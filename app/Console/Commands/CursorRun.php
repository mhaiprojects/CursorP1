<?php

namespace App\Console\Commands;

use App\Services\Cursor\Contracts\CursorAgent;
use Illuminate\Console\Command;

class CursorRun extends Command
{
    protected $signature = 'cursor:run
        {prompt : The prompt to send to the Cursor agent}
        {--mode= : Execution mode: ask|plan (defaults to config)}
        {--model= : Model override}
        {--force : Allow the agent to run write/shell tools}';

    protected $description = 'Send a prompt to the Cursor CLI agent and print the response';

    public function handle(CursorAgent $agent): int
    {
        $options = array_filter([
            'mode' => $this->option('mode'),
            'model' => $this->option('model'),
            'force' => $this->option('force') ?: null,
        ], fn ($value) => $value !== null);

        $this->info('Running Cursor agent...');

        $result = $agent->run($this->argument('prompt'), $options);

        if (! $result->ok) {
            $this->error('Cursor agent failed: '.$result->error);

            return self::FAILURE;
        }

        $this->line($result->output);

        return self::SUCCESS;
    }
}
