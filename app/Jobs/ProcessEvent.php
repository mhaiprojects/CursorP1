<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessEvent implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $eventId) {}

    public function handle(): void
    {
        $event = Event::find($this->eventId);

        if (! $event) {
            return;
        }

        $event->update(['status' => 'processing']);

        // Simulate work performed by the queued task.
        EventLog::create([
            'event_id' => $event->id,
            'level' => 'info',
            'message' => "Processed scheduled event: {$event->title}",
            'context' => [
                'scheduled_at' => optional($event->scheduled_at)->toIso8601String(),
                'worker' => gethostname(),
            ],
            'logged_at' => now(),
        ]);

        $event->update([
            'status' => 'completed',
            'processed_at' => now(),
        ]);

        Log::info("ProcessEvent completed for event #{$event->id}");
    }
}
