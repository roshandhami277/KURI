<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use Illuminate\Http\JsonResponse;
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
                ->oldest()
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'task_date' => ['required', 'date'],
        ]);

        $task = $request->user()->dailyTasks()->create($validated);

        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'toggle_url' => route('tasks.toggle', $task),
        ]);
    }

    public function toggle(Request $request, DailyTask $task): JsonResponse
    {
        abort_unless($task->user_id === $request->user()->id, 403);

        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);

        return response()->json([
            'completed' => $task->completed_at !== null,
        ]);
    }
}
