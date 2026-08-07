<?php

namespace App\Filament\Resources\EventLogs;

use App\Filament\Resources\EventLogs\Pages\CreateEventLog;
use App\Filament\Resources\EventLogs\Pages\EditEventLog;
use App\Filament\Resources\EventLogs\Pages\ListEventLogs;
use App\Filament\Resources\EventLogs\Schemas\EventLogForm;
use App\Filament\Resources\EventLogs\Tables\EventLogsTable;
use App\Models\EventLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventLogResource extends Resource
{
    protected static ?string $model = EventLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return EventLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventLogs::route('/'),
            'create' => CreateEventLog::route('/create'),
            'edit' => EditEventLog::route('/{record}/edit'),
        ];
    }
}
