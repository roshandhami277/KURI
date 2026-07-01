<?php

namespace App\Http\Controllers;

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

        return redirect()->route('notes');
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id, 403);

        $note->update($this->validateNote($request));

        return redirect()->route('notes');
    }

    public function destroy(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->user_id === $request->user()->id, 403);

        $note->delete();

        return redirect()->route('notes');
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
}
