<?php

namespace Tests\Feature\Tasks;

use App\Jobs\ProcessTask;
use App\Models\Task;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessTaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_runs_the_prompt_and_marks_the_task_completed(): void
    {
        app(CursorAgent::class)->pushResponse('Task done: created the file.');

        $task = Task::create([
            'name' => 'Create file',
            'prompt' => 'Create a hello.txt file',
            'status' => Task::STATUS_PENDING,
        ]);

        (new ProcessTask($task->id))->handle(app(CursorAgent::class));

        $task->refresh();
        $this->assertSame(Task::STATUS_COMPLETED, $task->status);
        $this->assertSame('Task done: created the file.', $task->output);
        $this->assertNotNull($task->started_at);
        $this->assertNotNull($task->finished_at);
        $this->assertNull($task->error);
    }

    public function test_it_marks_the_task_failed_when_the_agent_fails(): void
    {
        app(CursorAgent::class)->failNext();

        $task = Task::create([
            'name' => 'Broken task',
            'prompt' => 'do the impossible',
            'status' => Task::STATUS_PENDING,
        ]);

        (new ProcessTask($task->id))->handle(app(CursorAgent::class));

        $task->refresh();
        $this->assertSame(Task::STATUS_FAILED, $task->status);
        $this->assertNotNull($task->error);
    }

    public function test_it_passes_task_prompt_to_the_agent(): void
    {
        $agent = app(CursorAgent::class);

        $task = Task::create([
            'name' => 'Prompted',
            'prompt' => 'summarise the README',
            'status' => Task::STATUS_PENDING,
        ]);

        (new ProcessTask($task->id))->handle($agent);

        $this->assertSame('summarise the README', $agent->lastPrompt());
    }
}
