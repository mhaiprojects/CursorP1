<?php

namespace Tests\Feature\Cursor;

use App\Services\Cursor\CliCursorAgent;
use App\Services\Cursor\Contracts\CursorAgent;
use App\Services\Cursor\FakeCursorAgent;
use Tests\TestCase;

class CursorServiceTest extends TestCase
{
    public function test_it_binds_the_fake_driver_by_default_in_tests(): void
    {
        $this->assertInstanceOf(FakeCursorAgent::class, app(CursorAgent::class));
    }

    public function test_it_binds_the_cli_driver_when_configured(): void
    {
        config()->set('cursor.driver', 'cli');
        app()->forgetInstance(CursorAgent::class);

        $this->assertInstanceOf(CliCursorAgent::class, app(CursorAgent::class));
    }

    public function test_cursor_run_command_prints_the_agent_response(): void
    {
        app(CursorAgent::class)->pushResponse('Three benefits: A, B, C');

        $this->artisan('cursor:run', ['prompt' => 'benefits of tests'])
            ->expectsOutputToContain('Three benefits: A, B, C')
            ->assertSuccessful();
    }

    public function test_cursor_run_command_fails_when_agent_fails(): void
    {
        app(CursorAgent::class)->failNext();

        $this->artisan('cursor:run', ['prompt' => 'boom'])
            ->assertFailed();
    }
}
