# AGENTS.md

This is a Laravel 13 + Filament 5 application. See `README.md` for a full feature overview and the standard commands.

## Cursor Cloud specific instructions

### Services / how to run

- This is a single web app (Laravel + Filament admin panel). No external services are required: it uses SQLite (`database/database.sqlite`) and the `database` queue and cache drivers.
- Run the full dev stack (server + queue worker + logs + Vite) with `composer dev`. It uses `--kill-others`, so if one process dies they all stop.
- To run pieces individually for debugging: `php artisan serve --host=0.0.0.0 --port=8000`, `php artisan queue:work`, `php artisan schedule:work`, `npm run dev`.
- The Filament admin panel lives at `/admin`. Create a login with `php artisan make:filament-user`. A dev user `admin@example.com` / `password` is created during setup, but the SQLite DB is git-ignored, so recreate the user on a fresh checkout with `php artisan migrate` + `php artisan make:filament-user`.
- The custom **Cursor Console** front-end is at `/console` (public, no auth). It is a fetch-based Alpine.js page, so it needs built assets: run `npm run build` (or `npm run dev`) — unlike `/admin`, it does NOT work without Vite assets.

### Cursor CLI integration

- The app talks to the Cursor CLI through `App\Services\Cursor\Contracts\CursorAgent`, bound in `CursorServiceProvider` based on `config('cursor.driver')` (`CURSOR_DRIVER`). Default is `fake` (offline, deterministic) so everything runs and tests pass with NO API key. Tests force `CURSOR_DRIVER=fake` via `phpunit.xml`.
- For the real agent set `CURSOR_DRIVER=cli` + `CURSOR_API_KEY`; the `cursor-agent` binary must be installed and on `PATH` (or set `CURSOR_AGENT_BIN`). Without a key the CLI prints `Error: Authentication required` and the driver surfaces it as a failed result.
- Scheduled Cursor tasks need the SAME scheduler + queue worker as events: `tasks:dispatch-due` only queues `ProcessTask` jobs.

### Recreating the demo

- `./scripts/install.sh` recreates the whole POC demo from scratch (deps, `.env`, SQLite DB, seed, and it processes due events through the queue). Default run **resets the DB** (`migrate:fresh --seed`); pass `--no-reset` to keep existing data. It is safe to re-run.
- POC seed data lives in `Database\Seeders\PocSeeder` (wired into `DatabaseSeeder`) and is idempotent (`updateOrCreate`/`firstOrCreate`). It creates the `admin@example.com` / `password` login plus sample events.

### Non-obvious gotchas

- The scheduled-event demo flow requires BOTH the scheduler and a queue worker running: `events:dispatch-due` only *queues* `ProcessEvent` jobs; a `queue:work`/`queue:listen` worker must be running for events to actually reach `completed`. `php artisan serve` alone does NOT process the queue.
- The Filament admin panel does not depend on Vite — its assets are pre-published to `public/`. `npm run dev`/`npm run build` is only needed for the default Laravel welcome page / custom frontend.
- `database/database.sqlite` is git-ignored. On a fresh checkout it may not exist; create it with `touch database/database.sqlite` (or it is auto-created) then `php artisan migrate`.
- Queued jobs are serialized to the DB. After editing a Job class while a worker is running, restart the worker (`queue:work` does not hot-reload code; `queue:listen` does).
- `bootstrap/app.php` sets `shouldRenderJsonWhen(fn ($r) => $r->is('api/*'))`, so validation errors on non-`api/*` routes render as redirects, NOT JSON. The `/console` fetch endpoints therefore validate explicitly and throw a `ValidationException` with a JSON response (see `ConsoleController::validateJson`). Follow that pattern for new JSON endpoints outside `api/*`.
- `./scripts/verify.sh` is the repeatable check for the Cursor features (Pint + targeted test suites + live smoke tests). Run it after changing the Cursor/task/console code.
- DDEV (`.ddev/config.yaml`) omits the db container (SQLite) and runs `queue:work` + `schedule:work` as `web_extra_daemons`. Native `php artisan serve`/`composer dev` remains the primary in-VM dev flow; DDEV needs Docker + the `ddev` binary, which are not part of the update script.
