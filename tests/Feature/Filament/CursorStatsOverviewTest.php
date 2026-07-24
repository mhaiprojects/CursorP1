<?php

namespace Tests\Feature\Filament;

use App\Filament\Widgets\CursorStatsOverview;
use App\Models\ChatMessage;
use App\Models\Task;
use App\Models\TaskRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CursorStatsOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_computes_dashboard_stats(): void
    {
        $completed = Task::create(['name' => 'C', 'prompt' => 'x', 'status' => Task::STATUS_COMPLETED]);
        Task::create(['name' => 'P', 'prompt' => 'x', 'status' => Task::STATUS_PENDING]);
        Task::create(['name' => 'F', 'prompt' => 'x', 'status' => Task::STATUS_FAILED]);
        TaskRun::create(['task_id' => $completed->id, 'status' => TaskRun::STATUS_COMPLETED]);
        ChatMessage::create(['conversation' => 'default', 'role' => 'user', 'content' => 'hi']);

        $stats = CursorStatsOverview::computeStats();

        $this->assertSame(3, $stats['tasks']);
        $this->assertSame(1, $stats['pending']);
        $this->assertSame(1, $stats['completed']);
        $this->assertSame(1, $stats['failed']);
        $this->assertSame(1, $stats['runs']);
        $this->assertSame(1, $stats['messages']);
    }
}
