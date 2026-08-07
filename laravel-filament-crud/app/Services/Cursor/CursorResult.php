<?php

namespace App\Services\Cursor;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Immutable result of a single Cursor agent invocation.
 */
class CursorResult implements Arrayable
{
    public function __construct(
        public readonly bool $ok,
        public readonly string $output,
        public readonly ?int $exitCode = null,
        public readonly ?string $error = null,
        public readonly array $raw = [],
    ) {}

    public static function success(string $output, ?int $exitCode = 0, array $raw = []): self
    {
        return new self(true, $output, $exitCode, null, $raw);
    }

    public static function failure(string $error, ?int $exitCode = null, string $output = '', array $raw = []): self
    {
        return new self(false, $output, $exitCode, $error, $raw);
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok,
            'output' => $this->output,
            'exit_code' => $this->exitCode,
            'error' => $this->error,
        ];
    }
}
