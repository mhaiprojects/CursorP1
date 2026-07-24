<?php

namespace App\Filament\Resources\Tasks\Tables;

use App\Jobs\ProcessTask;
use App\Models\Task;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('mode')
                    ->searchable(),
                IconColumn::make('force')
                    ->boolean(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        Task::STATUS_COMPLETED => 'success',
                        Task::STATUS_FAILED => 'danger',
                        Task::STATUS_RUNNING => 'info',
                        Task::STATUS_QUEUED => 'warning',
                        Task::STATUS_CANCELLED => 'gray',
                        default => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('cron_expression')
                    ->label('Cron')
                    ->placeholder('—')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('runs_count')
                    ->label('Runs')
                    ->counts('runs')
                    ->sortable(),
                TextColumn::make('scheduled_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('finished_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('exit_code')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('run')
                    ->label('Run now')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->visible(fn (Task $record): bool => ! in_array($record->status, [Task::STATUS_QUEUED, Task::STATUS_RUNNING], true))
                    ->action(function (Task $record): void {
                        $record->update(['status' => Task::STATUS_QUEUED]);
                        ProcessTask::dispatch($record->id);
                        Notification::make()->title('Task queued for the Cursor agent')->success()->send();
                    }),
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->visible(fn (Task $record): bool => $record->canBeCancelled())
                    ->action(function (Task $record): void {
                        $record->update(['status' => Task::STATUS_CANCELLED, 'finished_at' => now()]);
                        Notification::make()->title('Task cancelled')->warning()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
