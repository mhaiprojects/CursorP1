<?php

namespace Tests\Unit\Cursor;

use App\Services\Cursor\CursorResult;
use App\Services\Cursor\FakeCursorAgent;
use PHPUnit\Framework\TestCase;

class FakeCursorAgentTest extends TestCase
{
    public function test_it_returns_a_deterministic_success_result_and_records_calls(): void
    {
        $agent = new FakeCursorAgent;

        $result = $agent->run('hello world', ['mode' => 'ask']);

        $this->assertInstanceOf(CursorResult::class, $result);
        $this->assertTrue($result->ok);
        $this->assertStringContainsString('hello world', $result->output);
        $this->assertSame(1, $agent->callCount());
        $this->assertSame('hello world', $agent->lastPrompt());
    }

    public function test_it_returns_scripted_responses_in_order(): void
    {
        $agent = (new FakeCursorAgent)
            ->pushResponse('first')
            ->pushResponse('second');

        $this->assertSame('first', $agent->run('a')->output);
        $this->assertSame('second', $agent->run('b')->output);
    }

    public function test_it_can_simulate_a_failure(): void
    {
        $agent = (new FakeCursorAgent)->failNext();

        $result = $agent->run('do something');

        $this->assertFalse($result->ok);
        $this->assertNotNull($result->error);
    }
}
