<?php

namespace Tests\Feature\Tasks;

use App\Jobs\ProcessTask;
use App\Models\Task;
use App\Models\TaskRun;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskRunHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_execution_records_a_task_run(): void
    {
        app(CursorAgent::class)->pushResponse('run 1')->pushResponse('run 2');

        $task = Task::create([
            'name' => 'History',
            'prompt' => 'do it',
            'status' => Task::STATUS_PENDING,
        ]);

        (new ProcessTask($task->id))->handle(app(CursorAgent::class));
        // Reset for a second manual run (non-recurring settles as completed).
        $task->refresh()->update(['status' => Task::STATUS_PENDING]);
        (new ProcessTask($task->id))->handle(app(CursorAgent::class));

        $this->assertSame(2, $task->runs()->count());
        $this->assertSame(TaskRun::STATUS_COMPLETED, $task->runs()->first()->status);
        $this->assertSame('run 2', $task->runs()->first()->output);
    }

    public function test_a_failed_run_is_recorded_as_failed(): void
    {
        app(CursorAgent::class)->failNext();

        $task = Task::create([
            'name' => 'Failing',
            'prompt' => 'x',
            'status' => Task::STATUS_PENDING,
        ]);

        (new ProcessTask($task->id))->handle(app(CursorAgent::class));

        $this->assertSame(TaskRun::STATUS_FAILED, $task->runs()->first()->status);
    }
}
