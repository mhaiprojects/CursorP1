<?php

use App\Providers\AppServiceProvider;
use App\Providers\CursorServiceProvider;
use App\Providers\Filament\AdminPanelProvider;

return [
    AppServiceProvider::class,
    CursorServiceProvider::class,
    AdminPanelProvider::class,
];
