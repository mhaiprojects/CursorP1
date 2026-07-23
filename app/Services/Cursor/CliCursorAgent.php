<?php

namespace App\Services\Cursor;

use App\Services\Cursor\Contracts\CursorAgent;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Drives the real `cursor-agent` CLI binary in non-interactive (--print) mode.
 */
class CliCursorAgent implements CursorAgent
{
    public function __construct(private readonly array $config) {}

    public function run(string $prompt, array $options = []): CursorResult
    {
        $mode = $options['mode'] ?? $this->config['default_mode'] ?? null;
        $model = $options['model'] ?? ($this->config['model'] ?? null);
        $force = $options['force'] ?? ($this->config['force'] ?? false);
        $timeout = (int) ($options['timeout'] ?? ($this->config['timeout'] ?? 120));

        $command = [
            $this->config['binary'] ?? 'cursor-agent',
            '--print',
            '--output-format', 'json',
        ];

        if ($mode === 'ask' || $mode === 'plan') {
            $command[] = '--mode';
            $command[] = $mode;
        }

        if (! empty($model)) {
            $command[] = '--model';
            $command[] = $model;
        }

        if ($force) {
            $command[] = '--force';
        }

        // Prompt goes last as a positional argument.
        $command[] = $prompt;

        $env = [];
        if (! empty($this->config['api_key'])) {
            $env['CURSOR_API_KEY'] = $this->config['api_key'];
        }

        $process = new Process(
            $command,
            $this->config['workspace'] ?? base_path(),
            $env ?: null,
            null,
            $timeout,
        );

        try {
            $process->run();
        } catch (ProcessTimedOutException $e) {
            return CursorResult::failure("Cursor agent timed out after {$timeout}s", null, $process->getOutput());
        }

        $stdout = trim($process->getOutput());
        $stderr = trim($process->getErrorOutput());
        $exitCode = $process->getExitCode();

        // The CLI prints authentication / usage errors to stdout as plain text
        // and may still exit 0, so detect those explicitly.
        if (str_starts_with($stdout, 'Error:') || $stdout === '') {
            $message = $stdout !== '' ? $stdout : ($stderr !== '' ? $stderr : 'Cursor agent returned no output');

            return CursorResult::failure($message, $exitCode, $stdout);
        }

        if (! $process->isSuccessful()) {
            return CursorResult::failure($stderr ?: $stdout ?: 'Cursor agent failed', $exitCode, $stdout);
        }

        [$text, $raw] = $this->parseJson($stdout);

        return CursorResult::success($text, $exitCode, $raw);
    }

    /**
     * Extract the assistant's text from the CLI's JSON output, tolerating a few
     * shapes (single result object, or a stream of events).
     *
     * @return array{0: string, 1: array}
     */
    private function parseJson(string $stdout): array
    {
        $decoded = json_decode($stdout, true);

        if (is_array($decoded)) {
            // Shape: {"type":"result","result":"..."}
            if (isset($decoded['result']) && is_string($decoded['result'])) {
                return [$decoded['result'], $decoded];
            }

            // Shape: {"text":"..."} or {"message":"..."}
            foreach (['text', 'message', 'content'] as $key) {
                if (isset($decoded[$key]) && is_string($decoded[$key])) {
                    return [$decoded[$key], $decoded];
                }
            }
        }

        // Fall back to raw stdout (already a plain string, e.g. text format).
        return [$stdout, is_array($decoded) ? $decoded : []];
    }
}
