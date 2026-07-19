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

## Quick start (one command)

The fastest way to recreate the demo on a fresh machine is the installer script. It
installs dependencies, prepares `.env` + the SQLite database, seeds the
proof-of-concept (POC) demo data, and processes the due demo events through the queue:

```bash
./scripts/install.sh
```

Options:

| Flag | Effect |
|------|--------|
| _(none)_ | Full POC install — **resets** the database and seeds demo data |
| `--no-reset` | Install without wiping existing data (idempotent re-seed) |
| `--no-process` | Skip processing the due demo events |
| `--help` | Show usage |

When it finishes, start the app with `composer dev` and log in at
`http://localhost:8000/admin` using the seeded credentials below.

## Manual setup

If you prefer to set things up by hand:

```bash
composer install
npm install
cp .env.example .env      # if .env is missing
php artisan key:generate  # if APP_KEY is missing
php artisan migrate        # or: php artisan migrate:fresh --seed
php artisan db:seed        # seeds the POC demo data (admin user + sample events)
```

The app uses SQLite (`database/database.sqlite`) and the `database` queue driver by
default — no external services required.

### POC demo data & default login

`php artisan db:seed` (via `Database\Seeders\PocSeeder`) is idempotent and creates:

- A default admin login — **`admin@example.com` / `password`**
- Three sample events: two already due (processed to `completed`) and one scheduled
  for tomorrow (stays `pending`)

To create additional admin logins manually: `php artisan make:filament-user`.

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
