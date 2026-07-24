# Branch Report — `cursor/setup-laravel-filament-crud-4bac`

A full reference of the work and features on this branch. Structured as a top-level
feature summary followed by technical details intended for humans and other AI agents.

---

## 1. Top-level feature summary

This branch turns an empty repository into a **Laravel 13 + Filament 5** application that
integrates with the **Cursor CLI** to run and schedule automated agent tasks, with a
front-end console and chat.

### Product capabilities

1. **Admin panel & auth** — Filament admin at `/admin`; login `admin@example.com` / `password` (seeded).
2. **Events demo (CRUD + queue + scheduler)** — `Event` / `EventLog` resources; `events:dispatch-due` queues `ProcessEvent` jobs that write logs. (Original scaffold demo.)
3. **Cursor CLI integration** — a driver-based service that runs prompts through the real `cursor-agent` binary, with an offline `fake` driver for dev/CI.
4. **Task management** — create, schedule, run, cancel and delete tasks that execute a Cursor prompt. Filament `TaskResource` + a custom console.
5. **Scheduling** — one-off (`scheduled_at`) and **recurring** (cron expression) tasks, dispatched every minute via the Laravel scheduler.
6. **Task queue** — `ProcessTask` job runs each task through the agent with retries/backoff/timeout and failure handling.
7. **Task run history** — every execution is recorded as a `TaskRun` (audit trail).
8. **Cursor Console front-end** (`/console`) — authenticated, per-user page to manage/schedule tasks and chat with the agent (Alpine.js + polling).
9. **Dashboard widget** — Filament stats overview (tasks / runs / messages).
10. **Dev/ops** — SQLite everywhere, DDEV config, installer + verification scripts, GitHub Actions CI.

### Ways to run it

- Native: `composer dev` (server + queue + logs + Vite), then `/admin` and `/console`.
- One-command demo: `./scripts/install.sh`.
- Containerised: `ddev start` (SQLite; queue + scheduler run as daemons).
- Verify features: `./scripts/verify.sh`.

---

## 2. Technical details

### 2.1 Stack & conventions

- PHP 8.3, Laravel 13, Filament 5, Livewire 4 (Filament dep), Alpine.js 3 (console), Tailwind 4 (Vite).
- Storage: SQLite (`database/database.sqlite`, git-ignored). `database` driver for queue/cache/session.
- Tests: PHPUnit (in-memory SQLite, `sync` queue). Lint: Pint. All test envs force `CURSOR_DRIVER=fake` (`phpunit.xml`).

### 2.2 Cursor integration (`app/Services/Cursor`)

- `Contracts\CursorAgent::run(string $prompt, array $options): CursorResult` — the single abstraction.
- `CursorResult` — immutable value object (`ok`, `output`, `exitCode`, `error`, `raw`).
- `CliCursorAgent` — runs `cursor-agent --print --output-format json [--mode ask|plan] [--model X] [--force] <prompt>` via Symfony `Process`; parses JSON, treats `Error:`/empty output as failure. API key via `CURSOR_API_KEY` env.
- `FakeCursorAgent` — deterministic responses; records `calls`; helpers `pushResponse()`, `failNext()`, `throwNext()` for tests.
- `CursorServiceProvider` binds the contract as a singleton based on `config('cursor.driver')` (`config/cursor.php`).
- `config/cursor.php` keys: `driver` (`fake`|`cli`), `cli.binary`, `cli.api_key`, `cli.model`, `cli.workspace`, `cli.timeout`, `cli.default_mode`, `cli.force`.
- CLI helper: `php artisan cursor:run "<prompt>" [--mode=] [--model=] [--force]` (`App\Console\Commands\CursorRun`).

### 2.3 Data model

- `tasks`: `user_id?`, `name`, `description?`, `prompt`, `mode?`, `force`, `status`, `scheduled_at?`, `cron_expression?`, `last_run_at?`, `started_at?`, `finished_at?`, `exit_code?`, `output?`, `error?`.
  - Statuses: `pending → queued → running → completed | failed | cancelled`.
  - `Task` scopes: `due()` (one-off pending, no cron, scheduled ≤ now or null), `recurring()` (has cron + pending). Helpers: `isRecurring()`, `isTerminal()`, `canBeCancelled()`, `isCronDue()`. Relations: `user()`, `runs()` (ordered by `id` desc).
- `task_runs`: `task_id`, `status`, `output?`, `exit_code?`, `error?`, `started_at?`, `finished_at?` (one row per execution).
- `chat_messages`: `user_id?`, `conversation`, `role` (`user|assistant|system`), `content`, `failed`, `meta?`.
- `events` / `event_logs`: original demo (unchanged).

### 2.4 Jobs, commands & scheduling

- `ProcessTask` (`ShouldQueue`, `$tries=3`, `$timeout=300`, `backoff()`):
  - Creates a `TaskRun` (running) + sets task running.
  - Calls the agent. A thrown exception propagates (queue retries); a `!ok` result is a deterministic failure recorded immediately.
  - Updates the run + task. **Recurring** tasks return to `pending`; one-off tasks settle `completed`/`failed`.
  - `failed(Throwable)` marks the running run + task `failed` after retries are exhausted.
- `tasks:dispatch-due` — queues one-off due tasks, and recurring tasks whose `isCronDue()` matches (stamps `last_run_at`). Registered `everyMinute()` in `routes/console.php`.
- `events:dispatch-due` — original event pipeline, also `everyMinute()`.

### 2.5 Front-end console (`/console`)

- `ConsoleController` (web group, `auth` middleware). All queries scoped by `auth()->id()`; ownership enforced via `authorizeTask()` (403). Chat conversation key = `user:{id}`.
- Endpoints: `GET /console`, `GET /console/state` (tasks with `runs_count` + messages + driver), and rate-limited (`throttle:cursor`) writes: `POST /console/tasks`, `POST /console/tasks/{task}/run`, `POST /console/tasks/{task}/cancel`, `DELETE /console/tasks/{task}`, `POST /console/chat`, `POST /console/chat/clear`.
- View `resources/views/console.blade.php`: Alpine component polling `/console/state` every 3s; task form (name/prompt/mode/schedule/cron), task list (status/cron/run-count/run/cancel/delete), chat panel. Assets via `@vite` (needs `npm run build` or `npm run dev`).

### 2.6 Filament

- `TaskResource` (`/admin/tasks`): generated CRUD + status badges, `Cron` + `Runs` columns, and **Run now** / **Cancel** row actions.
- `CursorStatsOverview` widget (auto-discovered) — cards for Tasks/Runs/Messages; `computeStats()` is unit-tested.

### 2.7 Security & reliability notes (important gotchas)

- **`bootstrap/app.php` sets `shouldRenderJsonWhen(fn ($r) => $r->is('api/*'))`** — so validation AND auth failures on non-`api/*` routes render as **redirects**, not JSON. The console therefore (a) validates explicitly and throws a `ValidationException` carrying a JSON 422 response (`ConsoleController::validateJson`), and (b) tests expect a redirect for unauthenticated JSON calls.
- Guests are redirected to `route('filament.admin.auth.login')` (`redirectGuestsTo`). The console uses the same `web` guard as Filament, so a Filament login authenticates the console too.
- `runs()` is ordered by `id` desc, not `created_at` (same-second ties are non-deterministic).
- `CURSOR_DRIVER=fake` is the default so nothing requires an API key. Real usage: `CURSOR_DRIVER=cli` + `CURSOR_API_KEY` (+ `cursor-agent` on PATH).

### 2.8 Tests (`php artisan test`) — 43 tests

- `Unit/Cursor/FakeCursorAgentTest` — fake agent behaviour.
- `Feature/Cursor/CursorServiceTest` — driver binding + `cursor:run`.
- `Feature/Tasks/ProcessTaskTest` — success/failure/exception/`failed()`.
- `Feature/Tasks/DispatchDueTasksTest` — one-off due dispatch.
- `Feature/Tasks/TaskRunHistoryTest` — per-run records.
- `Feature/Tasks/RecurringTaskTest` — cron due logic + recurring dispatch + return-to-pending.
- `Feature/Console/ConsoleTaskTest` — auth, per-user scoping, CRUD, validation, prompt limit.
- `Feature/Console/ConsoleChatTest` — auth, scoping, validation, clear.
- `Feature/Console/ConsoleCancelTest` — cancel rules + cron validation.
- `Feature/Console/ConsoleRateLimitTest` — 429 after the per-minute limit.
- `Feature/Filament/CursorStatsOverviewTest` — widget stat counts.

### 2.9 Dev/ops

- `scripts/install.sh` — one-command POC install (deps, `.env`, SQLite, seed, process due events). Flags `--no-reset`, `--no-process`.
- `scripts/verify.sh` — Pint + targeted suites + live smoke tests (`cursor:run`, schedule/queue wiring).
- `.ddev/config.yaml` — Laravel type, PHP 8.3, SQLite (db container omitted), `queue:work` + `schedule:work` daemons, post-start hooks.
- `.github/workflows/ci.yml` — PHP 8.3 + Node 22, install, build, Pint, migrate, test.
- `Database\Seeders\PocSeeder` — idempotent admin + sample events + sample tasks (owned by admin).

### 2.10 Key files (quick index)

- Cursor service: `app/Services/Cursor/*`, `app/Providers/CursorServiceProvider.php`, `config/cursor.php`.
- Tasks: `app/Models/{Task,TaskRun}.php`, `app/Jobs/ProcessTask.php`, `app/Console/Commands/{DispatchDueTasks,CursorRun}.php`.
- Console: `app/Http/Controllers/ConsoleController.php`, `routes/web.php`, `resources/views/console.blade.php`, `resources/js/app.js`.
- Filament: `app/Filament/Resources/Tasks/*`, `app/Filament/Widgets/CursorStatsOverview.php`.
- Config/bootstrap: `bootstrap/app.php`, `app/Providers/AppServiceProvider.php`, `routes/console.php`, `phpunit.xml`.
