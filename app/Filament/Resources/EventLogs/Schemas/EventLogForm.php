<?php

namespace App\Filament\Resources\EventLogs\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'title'),
                TextInput::make('level')
                    ->required()
                    ->default('info'),
                TextInput::make('message')
                    ->required(),
                Textarea::make('context')
                    ->columnSpanFull(),
                DateTimePicker::make('logged_at')
                    ->required(),
            ]);
    }
}
