<?php

namespace Tests\Feature\Tasks;

use App\Jobs\ProcessTask;
use App\Models\Task;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RecurringTaskTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_is_cron_due_matches_the_schedule(): void
    {
        Carbon::setTestNow('2026-01-01 09:00:00');

        $due = Task::create(['name' => 'A', 'prompt' => 'x', 'status' => Task::STATUS_PENDING, 'cron_expression' => '0 9 * * *']);
        $notDue = Task::create(['name' => 'B', 'prompt' => 'x', 'status' => Task::STATUS_PENDING, 'cron_expression' => '0 10 * * *']);

        $this->assertTrue($due->isCronDue());
        $this->assertFalse($notDue->isCronDue());
    }

    public function test_dispatch_due_queues_recurring_tasks_and_stamps_last_run_at(): void
    {
        Carbon::setTestNow('2026-01-01 09:00:00');
        Queue::fake();

        $recurring = Task::create(['name' => 'Cron', 'prompt' => 'x', 'status' => Task::STATUS_PENDING, 'cron_expression' => '0 9 * * *']);
        $notYet = Task::create(['name' => 'Later', 'prompt' => 'x', 'status' => Task::STATUS_PENDING, 'cron_expression' => '0 10 * * *']);

        $this->artisan('tasks:dispatch-due')->assertSuccessful();

        Queue::assertPushed(fn (ProcessTask $job) => $job->taskId === $recurring->id);
        Queue::assertNotPushed(fn (ProcessTask $job) => $job->taskId === $notYet->id);

        $recurring->refresh();
        $this->assertSame(Task::STATUS_QUEUED, $recurring->status);
        $this->assertNotNull($recurring->last_run_at);
    }

    public function test_processing_a_recurring_task_returns_it_to_pending(): void
    {
        app(CursorAgent::class)->pushResponse('ran');

        $task = Task::create([
            'name' => 'Repeating',
            'prompt' => 'x',
            'status' => Task::STATUS_QUEUED,
            'cron_expression' => '* * * * *',
        ]);

        (new ProcessTask($task->id))->handle(app(CursorAgent::class));

        $task->refresh();
        $this->assertSame(Task::STATUS_PENDING, $task->status);
        $this->assertSame('ran', $task->output);
        $this->assertSame(1, $task->runs()->count());
    }
}
