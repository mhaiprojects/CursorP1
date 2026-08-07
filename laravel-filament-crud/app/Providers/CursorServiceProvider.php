<?php

namespace App\Providers;

use App\Services\Cursor\CliCursorAgent;
use App\Services\Cursor\Contracts\CursorAgent;
use App\Services\Cursor\FakeCursorAgent;
use Illuminate\Support\ServiceProvider;

class CursorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CursorAgent::class, function ($app) {
            $config = $app['config']['cursor'];

            return match ($config['driver'] ?? 'fake') {
                'cli' => new CliCursorAgent($config['cli']),
                default => new FakeCursorAgent,
            };
        });
    }
}
