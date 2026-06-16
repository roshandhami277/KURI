<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DailyTaskController extends Controller
{
    public function index(Request $request): View
    {
        $selectedDate = Carbon::parse($request->query('date', today()->toDateString()));

        return view('tasks.index', [
            'selectedDate' => $selectedDate,
            'tasks' => $request->user()
                ->dailyTasks()
                ->whereDate('task_date', $selectedDate)
                ->latest()
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'task_date' => ['required', 'date'],
        ]);

        // Creating through the logged-in user automatically fills user_id.
        $request->user()->dailyTasks()->create($validated);

        return back()->with('success', 'Task added.');
    }

    public function update(Request $request, DailyTask $task): RedirectResponse
    {
        $this->checkTaskOwner($request, $task);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'task_date' => ['required', 'date'],
        ]);

        $task->update($validated);

        return redirect()
            ->route('tasks', ['date' => $validated['task_date']])
            ->with('success', 'Task updated.');
    }

    public function toggle(Request $request, DailyTask $task): RedirectResponse
    {
        $this->checkTaskOwner($request, $task);

        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);

        return back();
    }

    public function destroy(Request $request, DailyTask $task): RedirectResponse
    {
        $this->checkTaskOwner($request, $task);

        $task->delete();

        return back()->with('success', 'Task deleted.');
    }

    private function checkTaskOwner(Request $request, DailyTask $task): void
    {
        abort_unless($task->user_id === $request->user()->id, 403);
    }
}
