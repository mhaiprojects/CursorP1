<?php

namespace App\Console\Commands;

use App\Jobs\ProcessTask;
use App\Models\Task;
use Illuminate\Console\Command;

class DispatchDueTasks extends Command
{
    protected $signature = 'tasks:dispatch-due';

    protected $description = 'Queue a Cursor-agent processing job for every due task (one-off + recurring)';

    public function handle(): int
    {
        $count = 0;

        // One-off tasks that are due.
        foreach (Task::query()->due()->get() as $task) {
            $task->update(['status' => Task::STATUS_QUEUED]);
            ProcessTask::dispatch($task->id);
            $count++;
        }

        // Recurring (cron) tasks whose schedule is due right now.
        foreach (Task::query()->recurring()->get() as $task) {
            if (! $task->isCronDue()) {
                continue;
            }

            $task->update([
                'status' => Task::STATUS_QUEUED,
                'last_run_at' => now(),
            ]);
            ProcessTask::dispatch($task->id);
            $count++;
        }

        if ($count === 0) {
            $this->info('No due tasks to dispatch.');

            return self::SUCCESS;
        }

        $this->info("Dispatched {$count} task(s).");

        return self::SUCCESS;
    }
}
