<?php

namespace App\Services\Cursor;

use App\Services\Cursor\Contracts\CursorAgent;

/**
 * Deterministic, offline implementation of the Cursor agent.
 *
 * Used as the default driver so the app runs without an API key, and in the
 * test suite. It records every invocation so tests can assert on them, and it
 * can be primed with a queue of scripted responses.
 */
class FakeCursorAgent implements CursorAgent
{
    /** @var array<int, array{prompt: string, options: array}> */
    public array $calls = [];

    /** @var array<int, CursorResult> */
    protected array $scripted = [];

    protected bool $shouldFail = false;

    public function run(string $prompt, array $options = []): CursorResult
    {
        $this->calls[] = ['prompt' => $prompt, 'options' => $options];

        if (! empty($this->scripted)) {
            return array_shift($this->scripted);
        }

        if ($this->shouldFail) {
            return CursorResult::failure('Fake cursor agent failure', 1);
        }

        $mode = $options['mode'] ?? 'ask';

        $output = sprintf(
            "[fake-cursor-agent] (mode: %s) Received prompt:\n\"%s\"\n\nThis is a simulated Cursor CLI response.",
            $mode,
            $prompt,
        );

        return CursorResult::success($output, 0, ['fake' => true, 'prompt' => $prompt]);
    }

    /**
     * Queue a scripted response (string becomes a success result).
     */
    public function pushResponse(string|CursorResult $response): self
    {
        $this->scripted[] = $response instanceof CursorResult
            ? $response
            : CursorResult::success($response);

        return $this;
    }

    public function failNext(): self
    {
        $this->shouldFail = true;

        return $this;
    }

    public function callCount(): int
    {
        return count($this->calls);
    }

    public function lastPrompt(): ?string
    {
        return $this->calls[array_key_last($this->calls)]['prompt'] ?? null;
    }
}
