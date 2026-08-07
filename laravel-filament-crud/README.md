# CursorP1

A Laravel 13 + Filament 5 admin application scaffolded for quick CRUD development. It demonstrates:

- **User login** — Filament admin panel authentication at `/admin`.
- **Scheduled events** — an `Event` CRUD resource with a `scheduled_at` time.
- **Event log** — an `EventLog` CRUD resource recording what happened to each event.
- **Task queue** — a queued `ProcessEvent` job that processes due events.
- **Scheduler** — the `events:dispatch-due` command runs every minute (via the Laravel scheduler) to queue jobs for due events.

It also integrates with the **Cursor CLI** to run automated agent tasks:

- **Cursor integration** — a driver-based service (`App\Services\Cursor`) that shells out to the real `cursor-agent` CLI, with an offline `fake` driver for dev/CI.
- **Task management & scheduling** — a `Task` model + Filament resource; the `tasks:dispatch-due` scheduled command queues a `ProcessTask` job that runs each task's prompt through the Cursor agent.
- **Cursor Console** — a front-end page at `/console` to manage/schedule tasks and a chat window that talks to the Cursor agent.

See [Cursor CLI integration](#cursor-cli-integration) below.

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

## Cursor CLI integration

The app can drive the [Cursor CLI](https://cursor.com/cli) (`cursor-agent`) to action
queued tasks and power the chat window. It never calls the binary directly — everything
goes through the `App\Services\Cursor\Contracts\CursorAgent` contract, which has two drivers
(selected by `CURSOR_DRIVER`, see `config/cursor.php`):

| Driver | When to use | Requirements |
|--------|-------------|--------------|
| `fake` (default) | Local dev, CI, automated tests | None — returns deterministic simulated responses |
| `cli` | Real Cursor agent | `cursor-agent` binary on `PATH` + a `CURSOR_API_KEY` |

Enable the real CLI by installing the binary (`curl https://cursor.com/install | bash`)
and setting in `.env`:

```env
CURSOR_DRIVER=cli
CURSOR_API_KEY=your-cursor-api-key
# optional:
CURSOR_AGENT_BIN=/home/you/.local/bin/cursor-agent
CURSOR_MODEL=gpt-5
```

Try it from the CLI:

```bash
php artisan cursor:run "Explain what this project does"
```

### Task management, scheduling & the queue

- Create tasks in the **Cursor Console** (`/console`) or the Filament admin (`/admin/tasks`).
- A task has a `prompt`, an optional `scheduled_at`, and a status
  (`pending → queued → running → completed|failed`).
- The scheduler runs `tasks:dispatch-due` every minute; it queues a `ProcessTask` job for
  each due task. A running queue worker executes the job, which calls the Cursor agent and
  stores the output on the task. "Run now" queues a task immediately.

### The Cursor Console front-end

Visit `http://localhost:8000/console` for a single page with:

- a **Tasks** panel to create/schedule/run/cancel/delete Cursor tasks and watch their status/output/run-count, and
- a **Chat** window that sends messages to the Cursor agent and shows the replies.

The console **requires authentication** (guests are redirected to the Filament login) and is
scoped per-user — each user only sees and controls their own tasks and chat history.

### Production hardening & advanced features

- **Auth + per-user scoping** on the console and all its endpoints; ownership is enforced on run/cancel/delete.
- **Rate limiting** (`throttle:cursor`, 30/min/user) on the console write endpoints.
- **Reliable jobs** — `ProcessTask` has retries/backoff/timeout and a `failed()` handler that records the failure.
- **Task run history** — every execution is recorded as a `TaskRun` (audit trail); the count shows in the UI.
- **Recurring tasks** — set a cron expression (e.g. `*/5 * * * *`); `tasks:dispatch-due` re-queues them on schedule and they return to `pending` after each run.
- **Cancel** pending/queued tasks from the console or the Filament table.
- **Dashboard widget** — a Filament stats overview (tasks / runs / messages) on `/admin`.
- **CI** — `.github/workflows/ci.yml` runs Pint + the test suite on push/PR.

A full breakdown of everything on this branch is in [`docs/BRANCH_REPORT.md`](docs/BRANCH_REPORT.md).

## Running with DDEV

This project ships a [DDEV](https://ddev.com) config (`.ddev/config.yaml`) that uses SQLite
(the bundled database container is omitted) and runs the queue worker + scheduler as
`web_extra_daemons`:

```bash
ddev start          # boots the container and runs composer/npm install, migrate --seed, build
ddev launch /console
```

`ddev start` runs the setup hooks automatically. To use the real Cursor CLI inside DDEV, set
`CURSOR_DRIVER=cli` and add `CURSOR_API_KEY` to `web_environment` (or `ddev config global`).

## Testing & linting

```bash
php artisan test        # PHPUnit tests
./vendor/bin/pint       # code style (add --test to check only)
./scripts/verify.sh     # lint + tests + live smoke tests of the Cursor features
```

`scripts/verify.sh` is the repeatable check for the Cursor integration features — it runs
Pint, the relevant test suites, and live smoke tests of `cursor:run` and the
schedule/queue wiring (using the `fake` driver, so no API key is needed).
