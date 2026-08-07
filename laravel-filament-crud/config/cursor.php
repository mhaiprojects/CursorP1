<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cursor Agent Driver
    |--------------------------------------------------------------------------
    |
    | Which implementation of App\Services\Cursor\Contracts\CursorAgent to use:
    |
    |   "cli"  - shells out to the real `cursor-agent` CLI binary. Requires the
    |            binary to be installed and a CURSOR_API_KEY to be configured.
    |   "fake" - returns deterministic canned responses. Great for local dev,
    |            CI and the automated test suite (no API key required).
    |
    */

    'driver' => env('CURSOR_DRIVER', 'fake'),

    /*
    |--------------------------------------------------------------------------
    | CLI driver settings
    |--------------------------------------------------------------------------
    */

    'cli' => [
        // Path (or command name on PATH) of the cursor-agent binary.
        'binary' => env('CURSOR_AGENT_BIN', 'cursor-agent'),

        // API key used for authentication (also read from CURSOR_API_KEY by the CLI).
        'api_key' => env('CURSOR_API_KEY'),

        // Optional model override, e.g. "gpt-5", "sonnet-4-thinking".
        'model' => env('CURSOR_MODEL'),

        // Directory the agent runs in.
        'workspace' => env('CURSOR_WORKSPACE', base_path()),

        // Max seconds a single invocation may run before being killed.
        'timeout' => (int) env('CURSOR_TIMEOUT', 120),

        // Execution mode used for chat-style requests: "ask" (read-only Q&A) is
        // the safe default. Tasks may override this per-request.
        'default_mode' => env('CURSOR_DEFAULT_MODE', 'ask'),

        // When true, pass --force so the agent may run write/shell tools without
        // prompting. Off by default for safety.
        'force' => (bool) env('CURSOR_FORCE', false),
    ],

];
