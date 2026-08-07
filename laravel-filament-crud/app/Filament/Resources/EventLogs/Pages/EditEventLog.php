<?php

namespace App\Filament\Resources\EventLogs\Pages;

use App\Filament\Resources\EventLogs\EventLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEventLog extends EditRecord
{
    protected static string $resource = EventLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
