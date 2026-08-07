<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Every minute, queue processing jobs for any events that are now due.
Schedule::command('events:dispatch-due')->everyMinute();

// Every minute, queue Cursor-agent jobs for any tasks that are now due.
Schedule::command('tasks:dispatch-due')->everyMinute();
