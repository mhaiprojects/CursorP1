<?php

namespace App\Jobs;

use App\Models\Task;
use App\Models\TaskRun;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

/**
 * Runs a Task's prompt through the Cursor agent and records the outcome.
 */
class ProcessTask implements ShouldQueue
{
    use Queueable;

    /** Retry transient failures a few times. */
    public int $tries = 3;

    /** Hard cap on a single attempt (seconds). */
    public int $timeout = 300;

    public function __construct(public int $taskId) {}

    /**
     * Wait progressively longer between retries.
     *
     * @return array<int, int>
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function handle(CursorAgent $agent): void
    {
        $task = Task::find($this->taskId);

        if (! $task || $task->isTerminal()) {
            return;
        }

        $run = $task->runs()->create([
            'status' => TaskRun::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        $task->update([
            'status' => Task::STATUS_RUNNING,
            'started_at' => now(),
            'error' => null,
        ]);

        // A thrown exception here propagates so the queue can retry it; a clean
        // "not ok" result is a deterministic failure and is recorded immediately.
        $result = $agent->run($task->prompt, array_filter([
            'mode' => $task->mode,
            'force' => $task->force,
        ], fn ($value) => $value !== null && $value !== false));

        $run->update([
            'status' => $result->ok ? TaskRun::STATUS_COMPLETED : TaskRun::STATUS_FAILED,
            'output' => $result->output,
            'exit_code' => $result->exitCode,
            'error' => $result->ok ? null : $result->error,
            'finished_at' => now(),
        ]);

        // Recurring tasks return to "pending" so they can run again on schedule;
        // one-off tasks settle into a terminal status.
        $task->update([
            'status' => $task->isRecurring()
                ? Task::STATUS_PENDING
                : ($result->ok ? Task::STATUS_COMPLETED : Task::STATUS_FAILED),
            'output' => $result->output,
            'exit_code' => $result->exitCode,
            'error' => $result->ok ? null : $result->error,
            'finished_at' => now(),
        ]);
    }

    /**
     * Called by the queue when the job ultimately fails (after exhausting retries).
     */
    public function failed(?Throwable $exception): void
    {
        $task = Task::find($this->taskId);

        if (! $task) {
            return;
        }

        $message = $exception?->getMessage() ?? 'The task failed to process.';

        $task->runs()
            ->where('status', TaskRun::STATUS_RUNNING)
            ->update([
                'status' => TaskRun::STATUS_FAILED,
                'error' => $message,
                'finished_at' => now(),
            ]);

        if (! $task->isTerminal()) {
            $task->update([
                'status' => Task::STATUS_FAILED,
                'finished_at' => now(),
                'error' => $message,
            ]);
        }
    }
}
