# AGENTS.md

This is a Laravel 13 + Filament 5 application. See `README.md` for a full feature overview and the standard commands.

## Cursor Cloud specific instructions

### Services / how to run

- This is a single web app (Laravel + Filament admin panel). No external services are required: it uses SQLite (`database/database.sqlite`) and the `database` queue and cache drivers.
- Run the full dev stack (server + queue worker + logs + Vite) with `composer dev`. It uses `--kill-others`, so if one process dies they all stop.
- To run pieces individually for debugging: `php artisan serve --host=0.0.0.0 --port=8000`, `php artisan queue:work`, `php artisan schedule:work`, `npm run dev`.
- The Filament admin panel lives at `/admin`. Create a login with `php artisan make:filament-user`. A dev user `admin@example.com` / `password` is created during setup, but the SQLite DB is git-ignored, so recreate the user on a fresh checkout with `php artisan migrate` + `php artisan make:filament-user`.

### Non-obvious gotchas

- The scheduled-event demo flow requires BOTH the scheduler and a queue worker running: `events:dispatch-due` only *queues* `ProcessEvent` jobs; a `queue:work`/`queue:listen` worker must be running for events to actually reach `completed`. `php artisan serve` alone does NOT process the queue.
- The Filament admin panel does not depend on Vite — its assets are pre-published to `public/`. `npm run dev`/`npm run build` is only needed for the default Laravel welcome page / custom frontend.
- `database/database.sqlite` is git-ignored. On a fresh checkout it may not exist; create it with `touch database/database.sqlite` (or it is auto-created) then `php artisan migrate`.
- Queued jobs are serialized to the DB. After editing a Job class while a worker is running, restart the worker (`queue:work` does not hot-reload code; `queue:listen` does).
