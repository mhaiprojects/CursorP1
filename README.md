# CursorP1

A Laravel 13 + Filament 5 admin application scaffolded for quick CRUD development. It demonstrates:

- **User login** — Filament admin panel authentication at `/admin`.
- **Scheduled events** — an `Event` CRUD resource with a `scheduled_at` time.
- **Event log** — an `EventLog` CRUD resource recording what happened to each event.
- **Task queue** — a queued `ProcessEvent` job that processes due events.
- **Scheduler** — the `events:dispatch-due` command runs every minute (via the Laravel scheduler) to queue jobs for due events.

## Requirements

- PHP 8.3+ with the usual Laravel extensions (`mbstring`, `xml`, `curl`, `zip`, `sqlite3`, `bcmath`, `intl`, ...)
- Composer 2
- Node.js 20+ / npm (for Vite assets)

## Setup

```bash
composer install
npm install
cp .env.example .env      # if .env is missing
php artisan key:generate  # if APP_KEY is missing
php artisan migrate
php artisan make:filament-user   # create an admin login
```

The app uses SQLite (`database/database.sqlite`) and the `database` queue driver by default — no external services required.

## Running (development)

Run everything (server + queue worker + logs + Vite) with one command:

```bash
composer dev
```

Or run pieces individually:

```bash
php artisan serve                      # web server -> http://localhost:8000
php artisan queue:work                 # process queued jobs
php artisan schedule:work              # run the scheduler loop
npm run dev                            # Vite dev server (frontend assets)
```

Then log in at `http://localhost:8000/admin`.

## The scheduled-event flow

1. Create an `Event` in the admin panel with a `scheduled_at` in the past/now and status `pending`.
2. The scheduler runs `events:dispatch-due` every minute (or run it manually), which queues a `ProcessEvent` job and writes a "Queued" `EventLog`.
3. The queue worker runs the job, marks the event `completed`, and writes a "Processed" `EventLog`.

## Testing & linting

```bash
php artisan test        # PHPUnit tests
./vendor/bin/pint       # code style (add --test to check only)
```
