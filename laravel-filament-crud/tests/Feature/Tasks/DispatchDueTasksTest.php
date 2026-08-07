<?php

namespace Tests\Feature\Tasks;

use App\Jobs\ProcessTask;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DispatchDueTasksTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_only_due_pending_tasks(): void
    {
        Queue::fake();

        $dueScheduled = Task::create([
            'name' => 'Due',
            'prompt' => 'run me',
            'status' => Task::STATUS_PENDING,
            'scheduled_at' => now()->subMinute(),
        ]);

        $unscheduled = Task::create([
            'name' => 'Unscheduled',
            'prompt' => 'run me too',
            'status' => Task::STATUS_PENDING,
        ]);

        $future = Task::create([
            'name' => 'Future',
            'prompt' => 'later',
            'status' => Task::STATUS_PENDING,
            'scheduled_at' => now()->addDay(),
        ]);

        $completed = Task::create([
            'name' => 'Done',
            'prompt' => 'already ran',
            'status' => Task::STATUS_COMPLETED,
            'scheduled_at' => now()->subDay(),
        ]);

        $this->artisan('tasks:dispatch-due')
            ->expectsOutputToContain('Dispatched 2 task(s).')
            ->assertSuccessful();

        Queue::assertPushed(ProcessTask::class, 2);
        Queue::assertPushed(fn (ProcessTask $job) => $job->taskId === $dueScheduled->id);
        Queue::assertPushed(fn (ProcessTask $job) => $job->taskId === $unscheduled->id);
        Queue::assertNotPushed(fn (ProcessTask $job) => $job->taskId === $future->id);
        Queue::assertNotPushed(fn (ProcessTask $job) => $job->taskId === $completed->id);

        $this->assertSame(Task::STATUS_QUEUED, $dueScheduled->fresh()->status);
        $this->assertSame(Task::STATUS_PENDING, $future->fresh()->status);
    }

    public function test_it_reports_when_there_is_nothing_to_dispatch(): void
    {
        Queue::fake();

        $this->artisan('tasks:dispatch-due')
            ->expectsOutputToContain('No due tasks to dispatch.')
            ->assertSuccessful();

        Queue::assertNothingPushed();
    }
}
