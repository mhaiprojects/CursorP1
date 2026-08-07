<?php

namespace App\Console\Commands;

use App\Jobs\ProcessEvent;
use App\Models\Event;
use App\Models\EventLog;
use Illuminate\Console\Command;

class DispatchDueEvents extends Command
{
    protected $signature = 'events:dispatch-due';

    protected $description = 'Queue a processing job for every pending event whose scheduled time has passed';

    public function handle(): int
    {
        $due = Event::query()
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($due->isEmpty()) {
            $this->info('No due events to dispatch.');

            return self::SUCCESS;
        }

        foreach ($due as $event) {
            $event->update(['status' => 'queued']);

            EventLog::create([
                'event_id' => $event->id,
                'level' => 'info',
                'message' => "Queued scheduled event: {$event->title}",
                'logged_at' => now(),
            ]);

            ProcessEvent::dispatch($event->id);
        }

        $this->info("Dispatched {$due->count()} due event(s).");

        return self::SUCCESS;
    }
}
