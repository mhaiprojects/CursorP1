<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Proof-of-concept seed data used to recreate the demo on a fresh environment.
 *
 * Creates a default admin login and a handful of sample scheduled events:
 * some are already due (they will be picked up by the scheduler + queue and
 * moved to "completed") and one is scheduled in the future (stays "pending").
 *
 * The seeder is idempotent, so it is safe to run repeatedly.
 */
class PocSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ],
        );

        $events = [
            [
                'title' => 'Nightly Report',
                'description' => 'Generate and email the nightly report.',
                'scheduled_at' => now()->subMinutes(5),
            ],
            [
                'title' => 'Weekly Backup',
                'description' => 'Back up the production database.',
                'scheduled_at' => now()->subMinutes(2),
            ],
            [
                'title' => 'Send Newsletter',
                'description' => 'Dispatch the weekly newsletter to subscribers.',
                'scheduled_at' => now()->addDay(),
            ],
        ];

        foreach ($events as $event) {
            Event::firstOrCreate(
                ['title' => $event['title']],
                [
                    'description' => $event['description'],
                    'scheduled_at' => $event['scheduled_at'],
                    'status' => 'pending',
                ],
            );
        }

        $tasks = [
            [
                'name' => 'Summarise open TODOs',
                'prompt' => 'Scan the codebase and summarise all TODO comments.',
                'mode' => 'ask',
                'scheduled_at' => null,
            ],
            [
                'name' => 'Draft release notes',
                'prompt' => 'Draft release notes from the latest git log.',
                'mode' => 'ask',
                'scheduled_at' => now()->addHour(),
            ],
        ];

        foreach ($tasks as $task) {
            Task::firstOrCreate(
                ['name' => $task['name']],
                [
                    'prompt' => $task['prompt'],
                    'mode' => $task['mode'],
                    'scheduled_at' => $task['scheduled_at'],
                    'status' => Task::STATUS_PENDING,
                ],
            );
        }
    }
}
