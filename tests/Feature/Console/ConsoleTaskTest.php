<?php

namespace Tests\Feature\Console;

use App\Models\Task;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_console_page_loads(): void
    {
        $this->get('/console')
            ->assertOk()
            ->assertSee('Cursor Console');
    }

    public function test_it_creates_a_pending_task(): void
    {
        $response = $this->postJson('/console/tasks', [
            'name' => 'Nightly summary',
            'prompt' => 'Summarise today\'s commits',
            'mode' => 'ask',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('tasks', [
            'name' => 'Nightly summary',
            'status' => Task::STATUS_PENDING,
        ]);
    }

    public function test_it_creates_and_runs_a_task_immediately(): void
    {
        // QUEUE_CONNECTION=sync in tests, so the job runs inline.
        app(CursorAgent::class)->pushResponse('Completed the summary.');

        $response = $this->postJson('/console/tasks', [
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
        app(CursorAgent::class)->pushResponse('done');
        $task = Task::create([
            'name' => 'Later',
            'prompt' => 'do it',
            'status' => Task::STATUS_PENDING,
        ]);

        $this->postJson("/console/tasks/{$task->id}/run")->assertOk();

        $this->assertSame(Task::STATUS_COMPLETED, $task->fresh()->status);
    }

    public function test_it_validates_task_creation(): void
    {
        $this->postJson('/console/tasks', ['name' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'prompt']);
    }

    public function test_it_deletes_a_task(): void
    {
        $task = Task::create([
            'name' => 'Delete me',
            'prompt' => 'x',
            'status' => Task::STATUS_PENDING,
        ]);

        $this->deleteJson("/console/tasks/{$task->id}")->assertOk();
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_state_endpoint_returns_tasks_and_driver(): void
    {
        Task::create(['name' => 'A', 'prompt' => 'x', 'status' => Task::STATUS_PENDING]);

        $this->getJson('/console/state')
            ->assertOk()
            ->assertJsonStructure(['tasks', 'messages', 'driver'])
            ->assertJsonPath('driver', 'fake');
    }
}
