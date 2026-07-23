<?php

namespace App\Console\Commands;

use App\Jobs\ProcessTask;
use App\Models\Task;
use Illuminate\Console\Command;

class DispatchDueTasks extends Command
{
    protected $signature = 'tasks:dispatch-due';

    protected $description = 'Queue a Cursor-agent processing job for every due, pending task';

    public function handle(): int
    {
        $due = Task::query()->due()->get();

        if ($due->isEmpty()) {
            $this->info('No due tasks to dispatch.');

            return self::SUCCESS;
        }

        foreach ($due as $task) {
            $task->update(['status' => Task::STATUS_QUEUED]);
            ProcessTask::dispatch($task->id);
        }

        $this->info("Dispatched {$due->count()} task(s).");

        return self::SUCCESS;
    }
}
