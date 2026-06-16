<?php

namespace Tests\Feature;

use App\Models\DailyTask;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_complete_edit_and_delete_daily_task(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tasks.store'), [
                'title' => 'Finish maths homework',
                'task_date' => '2026-06-16',
            ])
            ->assertRedirect();

        $task = DailyTask::first();

        $this->assertSame($user->id, $task->user_id);

        $this->actingAs($user)
            ->patch(route('tasks.toggle', $task))
            ->assertRedirect();

        $this->assertNotNull($task->fresh()->completed_at);

        $this->actingAs($user)
            ->put(route('tasks.update', $task), [
                'title' => 'Finish physics homework',
                'task_date' => '2026-06-17',
            ])
            ->assertRedirect(route('tasks', ['date' => '2026-06-17']));

        $this->assertSame('Finish physics homework', $task->fresh()->title);

        $this->actingAs($user)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect();

        $this->assertDatabaseMissing('daily_tasks', ['id' => $task->id]);
    }

    public function test_users_cannot_change_each_others_tasks(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $task = DailyTask::create([
            'user_id' => $owner->id,
            'title' => 'Private task',
            'task_date' => '2026-06-16',
        ]);

        $this->actingAs($otherUser)
            ->patch(route('tasks.toggle', $task))
            ->assertForbidden();
    }
}
