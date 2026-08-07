<?php

namespace App\Filament\Resources\EventLogs\Pages;

use App\Filament\Resources\EventLogs\EventLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEventLog extends CreateRecord
{
    protected static string $resource = EventLogResource::class;
}
