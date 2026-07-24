<?php

namespace Tests\Feature\Console;

use App\Models\Task;
use App\Models\User;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleTaskTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::factory()->create();
    }

    public function test_console_page_requires_authentication(): void
    {
        $this->get('/console')->assertRedirect();
    }

    public function test_console_page_loads_for_authenticated_user(): void
    {
        $this->actingAs($this->user())
            ->get('/console')
            ->assertOk()
            ->assertSee('Cursor Console');
    }

    public function test_it_creates_a_pending_task_for_the_user(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)->postJson('/console/tasks', [
            'name' => 'Nightly summary',
            'prompt' => 'Summarise today\'s commits',
            'mode' => 'ask',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('tasks', [
            'name' => 'Nightly summary',
            'status' => Task::STATUS_PENDING,
            'user_id' => $user->id,
        ]);
    }

    public function test_it_creates_and_runs_a_task_immediately(): void
    {
        app(CursorAgent::class)->pushResponse('Completed the summary.');

        $response = $this->actingAs($this->user())->postJson('/console/tasks', [
            'name' => 'Run now task',
            'prompt' => 'summarise',
            'run_now' => true,
        ]);

        $response->assertCreated();
        $task = Task::firstWhere('name', 'Run now task');
        $this->assertSame(Task::STATUS_COMPLETED, $task->status);
        $this->assertSame('Completed the summary.', $task->output);
    }

    public function test_it_runs_an_existing_task(): void
    {
        $user = $this->user();
        app(CursorAgent::class)->pushResponse('done');
        $task = Task::create([
            'user_id' => $user->id,
            'name' => 'Later',
            'prompt' => 'do it',
            'status' => Task::STATUS_PENDING,
        ]);

        $this->actingAs($user)->postJson("/console/tasks/{$task->id}/run")->assertOk();

        $this->assertSame(Task::STATUS_COMPLETED, $task->fresh()->status);
    }

    public function test_it_validates_task_creation(): void
    {
        $this->actingAs($this->user())
            ->postJson('/console/tasks', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'prompt']);
    }

    public function test_it_rejects_an_overly_long_prompt(): void
    {
        $this->actingAs($this->user())
            ->postJson('/console/tasks', [
                'name' => 'Too long',
                'prompt' => str_repeat('a', 8001),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['prompt']);
    }

    public function test_it_deletes_a_task(): void
    {
        $user = $this->user();
        $task = Task::create([
            'user_id' => $user->id,
            'name' => 'Delete me',
            'prompt' => 'x',
            'status' => Task::STATUS_PENDING,
        ]);

        $this->actingAs($user)->deleteJson("/console/tasks/{$task->id}")->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_state_endpoint_returns_only_the_users_tasks(): void
    {
        $user = $this->user();
        $other = $this->user();
        Task::create(['user_id' => $user->id, 'name' => 'Mine', 'prompt' => 'x', 'status' => Task::STATUS_PENDING]);
        Task::create(['user_id' => $other->id, 'name' => 'Theirs', 'prompt' => 'x', 'status' => Task::STATUS_PENDING]);

        $this->actingAs($user)->getJson('/console/state')
            ->assertOk()
            ->assertJsonStructure(['tasks', 'messages', 'driver'])
            ->assertJsonPath('driver', 'fake')
            ->assertJsonCount(1, 'tasks')
            ->assertJsonPath('tasks.0.name', 'Mine');
    }

    public function test_a_user_cannot_run_or_delete_another_users_task(): void
    {
        $owner = $this->user();
        $intruder = $this->user();
        $task = Task::create([
            'user_id' => $owner->id,
            'name' => 'Private',
            'prompt' => 'x',
            'status' => Task::STATUS_PENDING,
        ]);

        $this->actingAs($intruder)->postJson("/console/tasks/{$task->id}/run")->assertForbidden();
        $this->actingAs($intruder)->deleteJson("/console/tasks/{$task->id}")->assertForbidden();
        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }
}
