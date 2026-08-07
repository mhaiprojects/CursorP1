<?php

namespace App\Filament\Widgets;

use App\Models\ChatMessage;
use App\Models\Task;
use App\Models\TaskRun;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CursorStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $stats = self::computeStats();

        return [
            Stat::make('Tasks', $stats['tasks'])
                ->description($stats['pending'].' pending / '.$stats['completed'].' completed')
                ->color('primary'),
            Stat::make('Task runs', $stats['runs'])
                ->description($stats['failed'].' failed tasks')
                ->color($stats['failed'] > 0 ? 'danger' : 'success'),
            Stat::make('Chat messages', $stats['messages'])
                ->description('across all conversations')
                ->color('info'),
        ];
    }

    /**
     * Computed separately so the numbers can be asserted in tests.
     *
     * @return array<string, int>
     */
    public static function computeStats(): array
    {
        return [
            'tasks' => Task::count(),
            'pending' => Task::where('status', Task::STATUS_PENDING)->count(),
            'completed' => Task::where('status', Task::STATUS_COMPLETED)->count(),
            'failed' => Task::where('status', Task::STATUS_FAILED)->count(),
            'runs' => TaskRun::count(),
            'messages' => ChatMessage::count(),
        ];
    }
}
