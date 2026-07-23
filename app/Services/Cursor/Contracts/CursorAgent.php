<?php

namespace App\Services\Cursor\Contracts;

use App\Services\Cursor\CursorResult;

/**
 * Abstraction over the Cursor CLI so the rest of the app (tasks, chat, jobs)
 * never depends on how the agent is actually invoked.
 */
interface CursorAgent
{
    /**
     * Run a single prompt through the Cursor agent and return the result.
     *
     * Supported options:
     *   - mode: "ask" | "plan" | null (agent)
     *   - model: string|null
     *   - force: bool  (allow write/shell tools without prompting)
     *   - timeout: int (seconds)
     */
    public function run(string $prompt, array $options = []): CursorResult;
}
