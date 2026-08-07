<?php

namespace App\Filament\Resources\EventLogs\Pages;

use App\Filament\Resources\EventLogs\EventLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventLogs extends ListRecords
{
    protected static string $resource = EventLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
