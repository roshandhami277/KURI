<?php

namespace App\Http\Controllers;

use App\Models\ChatGroup;
use App\Models\ChatMessage;
use App\Models\Note;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // The subject dropdown only shows subjects from the student's course.
        $subjects = $user->course
            ? $user->course->subjects()->where('is_active', true)->orderBy('name')->get()
            : collect();

        $notes = $user->notes()
            ->with('subject')
            ->latest()
            ->get();

        return view('notes.index', [
            'notes' => $notes,
            'subjects' => $subjects,
            'shareTargets' => $this->shareTargets($user),
            'tags' => $notes->pluck('tag')->filter()->unique()->sort()->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $note = $request->user()->notes()->make($this->validateNote($request));
        $openedAt = $this->openedAt($request);

        if ($openedAt) {
            // For a new note, "created" means the moment the student clicked New.
            $note->created_at = $openedAt;
        }

        $note->save();

        return redirect()->route('notes')->with('success', 'Note saved.');
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id, 403);

        $note->update($this->validateNote($request));

        return redirect()->route('notes')->with('success', 'Note saved.');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id, 403);

        $note->delete();

        return redirect()->route('notes');
    }

    public function share(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'target_type' => ['required', Rule::in(['course', 'chat_group'])],
            'target_id' => ['required', 'integer'],
        ]);

        $messageData = [
            'sender_id' => $request->user()->id,
            'shared_note_id' => $note->id,
            'body' => 'Shared a note',
        ];

        if ($validated['target_type'] === 'course') {
            abort_unless((int) $request->user()->course_id === (int) $validated['target_id'], 403);

            $messageData['course_id'] = $request->user()->course_id;
        } else {
            $group = ChatGroup::findOrFail($validated['target_id']);

            abort_unless($this->canShareToChatGroup($request, $group), 403);

            $messageData['chat_group_id'] = $group->id;
        }

        ChatMessage::create($messageData);

        return redirect()->route('notes')->with('success', 'Note shared to group.');
    }

    public function copy(Request $request, Note $note): RedirectResponse
    {
        abort_unless($this->canPreviewSharedNote($request, $note), 403);

        $subjectId = $this->subjectAllowedForUser($request, $note)
            ? $note->subject_id
            : null;

        // This creates a new private note owned by the logged-in user.
        // After this, the user can edit it like any other note they wrote.
        $copiedNote = $request->user()->notes()->create([
            'subject_id' => $subjectId,
            'title' => $note->title,
            'tag' => $note->tag,
            'description' => $note->description,
            'body' => $note->body,
        ]);

        return redirect(route('notes').'#note-'.$copiedNote->id)->with('success', 'Shared note saved to your notes.');
    }

    private function validateNote(Request $request): array
    {
        $allowedSubjectIds = $request->user()->course
            ? $request->user()->course->subjects()->pluck('subjects.id')->all()
            : [];

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'subject_id' => ['nullable', Rule::in($allowedSubjectIds)],
            'tag' => ['nullable', Rule::in(['study', 'personal', 'important'])],
            'description' => ['nullable', 'string', 'max:180'],
            'body' => ['nullable', 'string'],
        ]);

        // A subject only makes sense for study notes.
        // If the tag is blank, personal, or important, we store no subject.
        if (($validated['tag'] ?? null) !== 'study') {
            $validated['subject_id'] = null;
        }

        return $validated;
    }

    private function openedAt(Request $request): ?Carbon
    {
        if (! $request->filled('opened_at')) {
            return null;
        }

        try {
            return Carbon::parse($request->input('opened_at'));
        } catch (\Throwable) {
            return null;
        }
    }

    private function shareTargets($user): array
    {
        $targets = [];

        if ($user->course) {
            $targets[] = [
                'type' => 'course',
                'id' => $user->course->id,
                'name' => $user->course->name,
                'subtitle' => 'Course group',
            ];
        }

        $chatGroups = $user->isTeacher()
            ? $user->ownedChatGroups()->orderBy('name')->get()
            : $user->chatGroups()->orderBy('name')->get();

        foreach ($chatGroups as $group) {
            $targets[] = [
                'type' => 'chat_group',
                'id' => $group->id,
                'name' => $group->name,
                'subtitle' => 'Teacher group',
            ];
        }

        return $targets;
    }

    private function canShareToChatGroup(Request $request, ChatGroup $group): bool
    {
        $user = $request->user();

        if ($user->isTeacher() && $group->teacher_id === $user->id) {
            return true;
        }

        return $group->members()->where('users.id', $user->id)->exists();
    }

    private function canPreviewSharedNote(Request $request, Note $note): bool
    {
        $user = $request->user();

        if ($note->user_id === $user->id) {
            return true;
        }

        $courseMessageIsVisible = ChatMessage::where('shared_note_id', $note->id)
            ->where('course_id', $user->course_id)
            ->whereNull('chat_group_id')
            ->exists();

        if ($courseMessageIsVisible) {
            return true;
        }

        $groupIds = $user->isTeacher()
            ? $user->ownedChatGroups()->pluck('chat_groups.id')
            : $user->chatGroups()->pluck('chat_groups.id');

        return ChatMessage::where('shared_note_id', $note->id)
            ->whereIn('chat_group_id', $groupIds)
            ->exists();
    }

    private function subjectAllowedForUser(Request $request, Note $note): bool
    {
        if (! $note->subject_id || ! $request->user()->course) {
            return false;
        }

        return $request->user()
            ->course
            ->subjects()
            ->where('subjects.id', $note->subject_id)
            ->exists();
    }
}
