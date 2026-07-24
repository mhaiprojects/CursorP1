<?php

namespace App\Jobs;

use App\Models\Task;
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

        $task->update([
            'status' => $result->ok ? Task::STATUS_COMPLETED : Task::STATUS_FAILED,
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

        if ($task && ! $task->isTerminal()) {
            $task->update([
                'status' => Task::STATUS_FAILED,
                'finished_at' => now(),
                'error' => $exception?->getMessage() ?? 'The task failed to process.',
            ]);
        }
    }
}
