<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsoleRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_write_endpoints_are_rate_limited(): void
    {
        $user = User::factory()->create();
        $agent = app(CursorAgent::class);

        // The 'cursor' limiter allows 30 requests/minute.
        $lastStatus = 201;
        for ($i = 0; $i < 31; $i++) {
            $agent->pushResponse('ok');
            $lastStatus = $this->actingAs($user)
                ->postJson('/console/chat', ['message' => "msg {$i}"])
                ->getStatusCode();
        }

        $this->assertSame(429, $lastStatus);
    }
}
