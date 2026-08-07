<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('prompt')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('mode'),
                Toggle::make('force')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                DateTimePicker::make('scheduled_at'),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('finished_at'),
                TextInput::make('exit_code')
                    ->numeric(),
                Textarea::make('output')
                    ->columnSpanFull(),
                Textarea::make('error')
                    ->columnSpanFull(),
            ]);
    }
}
