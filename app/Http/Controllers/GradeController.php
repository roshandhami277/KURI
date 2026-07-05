<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // These are only the subjects that belong to the course the student chose during registration.
        $subjects = $user->course
            ? $user->course->subjects()->where('is_active', true)->orderBy('name')->get()
            : collect();

        $selectedSubjectId = (int) $request->query('subject_id', $subjects->first()?->id);

        if (! $subjects->contains('id', $selectedSubjectId)) {
            $selectedSubjectId = (int) $subjects->first()?->id;
        }

        $recentSubjectId = $request->query('recent_subject_id', 'all');
        if ($recentSubjectId !== 'all' && ! $subjects->contains('id', (int) $recentSubjectId)) {
            $recentSubjectId = 'all';
        }

        $allowedSubjectIds = $subjects->pluck('id')->all();

        $recentGrades = $user->grades()
            ->with('subject')
            // Even if a user tries to change the URL, only subjects from their course are shown.
            ->whereIn('subject_id', $allowedSubjectIds)
            ->latest('grade_date')
            ->latest()
            ->get();

        $graphPointsBySubject = [];

        foreach ($subjects as $subject) {
            $subjectGrades = $user->grades()
                ->where('subject_id', $subject->id)
                ->orderBy('grade_date')
                ->orderBy('created_at')
                ->get();

            $graphPointsBySubject[$subject->id] = $this->makeGraphPoints($subjectGrades);
        }

        return view('grades.index', [
            'subjects' => $subjects,
            'selectedSubjectId' => $selectedSubjectId,
            'recentSubjectId' => $recentSubjectId,
            'recentGrades' => $recentGrades,
            'average' => $recentGrades->avg('grade'),
            'graphPointsBySubject' => $graphPointsBySubject,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $this->validateGrade($request);

        // Create one grade row connected to the logged-in user.
        $user->grades()->create($validated);

        return redirect()->route('grades', [
            'subject_id' => $validated['subject_id'],
        ]);
    }

    public function update(Request $request, Grade $grade): RedirectResponse
    {
        abort_unless($grade->user_id === $request->user()->id, 403);

        $validated = $this->validateGrade($request);
        $grade->update($validated);

        return redirect()->route('grades', [
            'subject_id' => $validated['subject_id'],
            'recent_subject_id' => $validated['subject_id'],
        ]);
    }

    public function destroy(Request $request, Grade $grade): RedirectResponse
    {
        abort_unless($grade->user_id === $request->user()->id, 403);

        $subjectId = $grade->subject_id;
        $grade->delete();

        return redirect()->route('grades', [
            'subject_id' => $subjectId,
            'recent_subject_id' => $subjectId,
        ]);
    }

    private function validateGrade(Request $request): array
    {
        $allowedSubjectIds = $request->user()->course
            ? $request->user()->course->subjects()->pluck('subjects.id')->all()
            : [];

        return $request->validate([
            'subject_id' => ['required', Rule::in($allowedSubjectIds)],
            'title' => ['nullable', 'string', 'max:100'],
            'grade' => ['required', 'numeric', 'min:0', 'max:20'],
            'grade_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'subject_id.required' => 'Escolhe uma disciplina.',
            'subject_id.in' => 'Só podes adicionar notas a disciplinas do teu curso.',
            'grade.required' => 'Escreve a nota que recebeste.',
            'grade.numeric' => 'A nota tem de ser um número.',
            'grade.min' => 'A nota não pode ser menor que 0.',
            'grade.max' => 'A nota não pode ser maior que 20.',
            'grade_date.date' => 'Escolhe uma data válida.',
        ]);
    }

    private function makeGraphPoints(Collection $grades): array
    {
        if ($grades->isEmpty()) {
            return [];
        }

        $width = 820;
        $height = 330;
        $leftPadding = 42;
        $rightPadding = 24;
        $topPadding = 14;
        $bottomPadding = 32;
        $usableWidth = $width - $leftPadding - $rightPadding;
        $usableHeight = $height - $topPadding - $bottomPadding;
        $lastIndex = max(1, $grades->count() - 1);

        return $grades->values()->map(function (Grade $grade, int $index) use ($leftPadding, $topPadding, $usableWidth, $usableHeight, $lastIndex) {
            $x = $leftPadding + (($index / $lastIndex) * $usableWidth);
            $y = $topPadding + ($usableHeight - (($grade->grade / 20) * $usableHeight));

            return [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'label' => number_format((float) $grade->grade, 2),
                'date' => $grade->grade_date?->format('d M') ?? 'No date',
            ];
        })->all();
    }
}
