<?php

namespace Tests\Feature\Console;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleCancelTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_pending_task_can_be_cancelled(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'name' => 'Cancel me',
            'prompt' => 'x',
            'status' => Task::STATUS_PENDING,
        ]);

        $this->actingAs($user)->postJson("/console/tasks/{$task->id}/cancel")->assertOk();

        $this->assertSame(Task::STATUS_CANCELLED, $task->fresh()->status);
    }

    public function test_a_completed_task_cannot_be_cancelled(): void
    {
        $user = User::factory()->create();
        $task = Task::create([
            'user_id' => $user->id,
            'name' => 'Done',
            'prompt' => 'x',
            'status' => Task::STATUS_COMPLETED,
        ]);

        $this->actingAs($user)->postJson("/console/tasks/{$task->id}/cancel")->assertStatus(422);
    }

    public function test_a_user_cannot_cancel_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $task = Task::create([
            'user_id' => $owner->id,
            'name' => 'Private',
            'prompt' => 'x',
            'status' => Task::STATUS_PENDING,
        ]);

        $this->actingAs($intruder)->postJson("/console/tasks/{$task->id}/cancel")->assertForbidden();
    }

    public function test_it_validates_a_bad_cron_expression(): void
    {
        $this->actingAs(User::factory()->create())
            ->postJson('/console/tasks', [
                'name' => 'Bad cron',
                'prompt' => 'x',
                'cron_expression' => 'not a cron',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['cron_expression']);
    }
}
