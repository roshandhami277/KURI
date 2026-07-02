<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $course = $user->course;

        $messages = $course
            ? ChatMessage::with('sender')
                ->where('course_id', $course->id)
                ->orderBy('created_at')
                ->get()
            : collect();

        $members = $course
            ? User::where('course_id', $course->id)->orderBy('name')->get()
            : collect();

        return view('chat.index', [
            'course' => $course,
            'messages' => $messages,
            'members' => $members,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $course = $request->user()->course;

        abort_unless($course, 403);

        $validated = $request->validate([
            'body' => ['nullable', 'required_without:attachment', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'max:5120'],
        ]);

        $messageData = [
            'course_id' => $course->id,
            'sender_id' => $request->user()->id,
            'body' => $validated['body'] ?? null,
        ];

        if ($request->hasFile('attachment')) {
            $messageData = array_merge($messageData, $this->storeAttachment($request));
        }

        ChatMessage::create($messageData);

        return redirect()->route('chat');
    }

    public function update(Request $request, ChatMessage $message): RedirectResponse
    {
        $this->makeSureUserOwnsMessage($request, $message);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        // Updating the body also changes updated_at, so the view can show "edited at".
        $message->update([
            'body' => $validated['body'],
        ]);

        return redirect()->route('chat');
    }

    public function destroy(Request $request, ChatMessage $message): RedirectResponse
    {
        $this->makeSureUserOwnsMessage($request, $message);

        if ($message->attachment_path) {
            File::delete(public_path($message->attachment_path));
        }

        $message->delete();

        return redirect()->route('chat');
    }

    private function makeSureUserOwnsMessage(Request $request, ChatMessage $message): void
    {
        // A user can only change messages written by them inside their own course group.
        abort_unless($message->sender_id === $request->user()->id, 403);
        abort_unless($message->course_id === $request->user()->course_id, 403);
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
