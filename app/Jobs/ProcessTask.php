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

    public function __construct(public int $taskId) {}

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

        try {
            $result = $agent->run($task->prompt, array_filter([
                'mode' => $task->mode,
                'force' => $task->force,
            ], fn ($value) => $value !== null && $value !== false));
        } catch (Throwable $e) {
            $task->update([
                'status' => Task::STATUS_FAILED,
                'finished_at' => now(),
                'error' => $e->getMessage(),
            ]);

            return;
        }

        $task->update([
            'status' => $result->ok ? Task::STATUS_COMPLETED : Task::STATUS_FAILED,
            'output' => $result->output,
            'exit_code' => $result->exitCode,
            'error' => $result->ok ? null : $result->error,
            'finished_at' => now(),
        ]);
    }
}
