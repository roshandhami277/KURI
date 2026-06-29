<?php

namespace App\Http\Controllers;

use App\Models\DailyTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DailyTaskController extends Controller
{
    public function index(Request $request): View
    {
        // Read the selected date from the URL, for example /tasks?date=2026-06-25.
        // If there is no date in the URL, use today's date.
        $selectedDate = Carbon::parse($request->query('date', today()->toDateString()));

        return view('tasks.index', [
            'selectedDate' => $selectedDate,
            // $request->user() is the logged-in user.
            // dailyTasks() uses the relationship in User.php.
            // whereDate() keeps only the tasks for the selected day.
            // get() finally runs the database query.
            'tasks' => $request->user()
                ->dailyTasks()
                ->whereDate('task_date', $selectedDate)
                ->oldest()
                ->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // Validate checks the data sent from JavaScript before saving it.
        // This protects the database from empty or too-long task text.
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'task_date' => ['required', 'date'],
        ]);

        // Create a row in daily_tasks.
        // Because we use $request->user()->dailyTasks(), Laravel automatically fills user_id.
        $task = $request->user()->dailyTasks()->create($validated);

        // Return JSON to JavaScript.
        // The page uses these URLs later to edit, tick, or delete this exact task.
        return response()->json([
            'id' => $task->id,
            'title' => $task->title,
            'update_url' => route('tasks.update', $task),
            'toggle_url' => route('tasks.toggle', $task),
            'delete_url' => route('tasks.destroy', $task),
        ]);
    }

    public function toggle(Request $request, DailyTask $task): JsonResponse
    {
        // Route model binding found the task by id.
        // This line checks the task belongs to the logged-in user before changing it.
        abort_unless($task->user_id === $request->user()->id, 403);

        // If completed_at already has a date, set it back to null.
        // If it is null, save the current time to mark the task as completed.
        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);

        return response()->json([
            'completed' => $task->completed_at !== null,
        ]);
    }

    public function update(Request $request, DailyTask $task): JsonResponse
    {
        // Do not let one student edit another student's task.
        abort_unless($task->user_id === $request->user()->id, 403);

        // Only the task title can be edited from this page.
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
        ]);

        // Save the new title in the daily_tasks table.
        $task->update($validated);

        return response()->json([
            'title' => $task->title,
        ]);
    }

    public function destroy(Request $request, DailyTask $task): Response
    {
        // Do not let one student delete another student's task.
        abort_unless($task->user_id === $request->user()->id, 403);

        // Remove the row from daily_tasks.
        $task->delete();

        // noContent means the delete worked, but we do not need to send page HTML back.
        return response()->noContent();
    }
}
