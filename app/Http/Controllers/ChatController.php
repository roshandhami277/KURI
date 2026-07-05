<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $courses = Course::orderBy('name')->get();
        $course = $this->selectedCourse($request, $user, $courses);
        $groups = $this->availableGroups($user);
        $selectedGroup = $groups->firstWhere('id', (int) $request->query('group'));

        if ($selectedGroup) {
            $messages = ChatMessage::with(['sender', 'sharedNote.subject'])
                ->where('chat_group_id', $selectedGroup->id)
                ->orderBy('created_at')
                ->get();

            $members = $selectedGroup->members()->orderBy('name')->get();

            if ($selectedGroup->teacher) {
                $members->prepend($selectedGroup->teacher);
            }

            $members = $this->addAdminsToMembers($members);
        } else {
            $messages = $course
                ? ChatMessage::with(['sender', 'sharedNote.subject'])
                    ->where('course_id', $course->id)
                    ->whereNull('chat_group_id')
                    ->orderBy('created_at')
                    ->get()
                : collect();

            $members = $course
                ? $this->courseMembersWithAdmins($course)
                : collect();
        }

        return view('chat.index', [
            'course' => $course,
            'courses' => $courses,
            'groups' => $groups,
            'selectedGroup' => $selectedGroup,
            'messages' => $messages,
            'members' => $members,
            'students' => User::where('role', 'student')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['nullable', 'required_without:attachment', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,webp,gif,pdf,doc,docx,ppt,pptx,xls,xlsx,txt'],
            'chat_group_id' => ['nullable', 'exists:chat_groups,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
        ], [
            'body.required_without' => 'Escreve uma mensagem ou escolhe um ficheiro.',
            'body.max' => 'A mensagem é demasiado grande.',
            'attachment.file' => 'O anexo tem de ser um ficheiro válido.',
            'attachment.max' => 'O ficheiro pode ter no máximo 5 MB.',
            'attachment.mimes' => 'Só podes enviar imagens, PDF, Word, PowerPoint, Excel ou texto.',
            'chat_group_id.exists' => 'Esse grupo já não existe.',
            'course_id.exists' => 'Esse curso já não existe.',
        ]);

        $messageData = [
            'sender_id' => $request->user()->id,
            'body' => $validated['body'] ?? null,
        ];

        if ($request->filled('chat_group_id')) {
            $group = ChatGroup::findOrFail($validated['chat_group_id']);

            abort_unless($this->canOpenGroup($request->user(), $group), 403);

            $messageData['chat_group_id'] = $group->id;
            $redirect = redirect()->route('chat', ['group' => $group->id]);
        } else {
            $course = $request->user()->isAdmin() && $request->filled('course_id')
                ? Course::find($validated['course_id'])
                : $request->user()->course;

            abort_unless($course, 403);

            $messageData['course_id'] = $course->id;
            $redirect = $request->user()->isAdmin()
                ? redirect()->route('chat', ['course' => $course->id])
                : redirect()->route('chat');
        }

        if ($request->hasFile('attachment')) {
            $messageData = array_merge($messageData, $this->storeAttachment($request));
        }

        ChatMessage::create($messageData);

        return $redirect;
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('chat_groups', 'name')->where('teacher_id', $request->user()->id),
            ],
        ], [
            'name.required' => 'Escreve o nome do grupo.',
            'name.max' => 'O nome do grupo é demasiado grande.',
            'name.unique' => 'Já tens um grupo com esse nome.',
        ]);

        $group = ChatGroup::create([
            'teacher_id' => $request->user()->id,
            'name' => $validated['name'],
        ]);

        return redirect()->route('chat', ['group' => $group->id]);
    }

    public function updateGroup(Request $request, ChatGroup $group): RedirectResponse
    {
        $this->makeSureUserOwnsGroup($request, $group);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('chat_groups', 'name')
                    ->where('teacher_id', $group->teacher_id)
                    ->ignore($group->id),
            ],
        ], [
            'name.required' => 'Escreve o nome do grupo.',
            'name.max' => 'O nome do grupo é demasiado grande.',
            'name.unique' => 'Já existe um grupo com esse nome.',
        ]);

        $group->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('chat', ['group' => $group->id]);
    }

    public function destroyGroup(Request $request, ChatGroup $group): RedirectResponse
    {
        $this->makeSureUserOwnsGroup($request, $group);

        foreach ($group->messages as $message) {
            if ($message->attachment_path) {
                File::delete(public_path($message->attachment_path));
            }
        }

        $group->delete();

        return redirect()->route('chat');
    }

    public function addGroupMember(Request $request, ChatGroup $group): RedirectResponse
    {
        $this->makeSureUserOwnsGroup($request, $group);

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ], [
            'email.required' => 'Escolhe o email de um aluno.',
            'email.email' => 'Escolhe um email válido.',
            'email.exists' => 'Não existe nenhum utilizador com esse email.',
        ]);

        $student = User::where('email', $validated['email'])->firstOrFail();

        if (! $student->isStudent()) {
            throw ValidationException::withMessages([
                'email' => 'Só podes adicionar alunos a este grupo.',
            ]);
        }

        $group->members()->syncWithoutDetaching($student->id);

        return redirect()->route('chat', ['group' => $group->id]);
    }

    public function update(Request $request, ChatMessage $message): RedirectResponse
    {
        $this->makeSureUserOwnsMessage($request, $message);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ], [
            'body.required' => 'A mensagem não pode ficar vazia.',
            'body.max' => 'A mensagem é demasiado grande.',
        ]);

        // Updating the body also changes updated_at, so the view can show "edited at".
        $message->update([
            'body' => $validated['body'],
        ]);

        return $this->messageRedirect($message);
    }

    public function destroy(Request $request, ChatMessage $message): RedirectResponse
    {
        $this->makeSureUserOwnsMessage($request, $message);

        if ($message->attachment_path) {
            File::delete(public_path($message->attachment_path));
        }

        $redirect = $this->messageRedirect($message);

        $message->delete();

        return $redirect;
    }

    private function availableGroups(User $user): Collection
    {
        if ($user->isAdmin()) {
            return ChatGroup::with('teacher')->orderBy('name')->get();
        }

        if ($user->isTeacher()) {
            return ChatGroup::with('teacher')
                ->where('teacher_id', $user->id)
                ->orderBy('created_at')
                ->get();
        }

        return $user->chatGroups()->with('teacher')->orderBy('name')->get();
    }

    private function selectedCourse(Request $request, User $user, Collection $courses): ?Course
    {
        if (! $user->isAdmin()) {
            return $user->course;
        }

        if ($request->filled('course')) {
            return $courses->firstWhere('id', (int) $request->query('course'));
        }

        // Admins do not belong to one course, so the first course opens by default.
        return $courses->first();
    }

    private function courseMembersWithAdmins(Course $course): Collection
    {
        $members = User::where('course_id', $course->id)
            ->orderBy('name')
            ->get();

        return $this->addAdminsToMembers($members);
    }

    private function addAdminsToMembers(Collection $members): Collection
    {
        $admins = User::where('role', 'admin')
            ->orderBy('name')
            ->get();

        return $members
            ->merge($admins)
            ->unique('id')
            ->values();
    }

    private function canOpenGroup(User $user, ChatGroup $group): bool
    {
        if ($user->isAdmin() || $group->teacher_id === $user->id) {
            return true;
        }

        return $group->members()->where('users.id', $user->id)->exists();
    }

    private function makeSureUserOwnsGroup(Request $request, ChatGroup $group): void
    {
        abort_unless($request->user()->isAdmin() || $group->teacher_id === $request->user()->id, 403);
    }

    private function messageRedirect(ChatMessage $message): RedirectResponse
    {
        if ($message->chat_group_id) {
            return redirect()->route('chat', ['group' => $message->chat_group_id]);
        }

        return redirect()->route('chat');
    }

    private function makeSureUserOwnsMessage(Request $request, ChatMessage $message): void
    {
        // A user can only change messages written by them inside their own course group.
        abort_unless($message->sender_id === $request->user()->id, 403);

        if ($message->chat_group_id) {
            abort_unless($this->canOpenGroup($request->user(), $message->chatGroup), 403);
            return;
        }

        abort_unless($request->user()->isAdmin() || $message->course_id === $request->user()->course_id, 403);
    }

    private function storeAttachment(Request $request): array
    {
        $file = $request->file('attachment');
        $folder = public_path('chat_uploads');

        if (! File::exists($folder)) {
            File::makeDirectory($folder, 0755, true);
        }

        $fileName = uniqid('chat_', true).'.'.$file->getClientOriginalExtension();
        $file->move($folder, $fileName);

        return [
            'attachment_path' => 'chat_uploads/'.$fileName,
            'attachment_name' => $file->getClientOriginalName(),
            'attachment_type' => $file->getClientMimeType(),
        ];
    }
}
