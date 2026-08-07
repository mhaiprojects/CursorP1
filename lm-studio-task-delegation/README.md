# CursorP1

Hybrid AI task pipeline: **Cursor Cloud Agents** for code and repo work, **LM Studio** for local micro-tasks (summaries, formatting, extraction).

## Quick start

1. Read the full plan: [docs/lm-studio-cursor-delegation-plan.md](docs/lm-studio-cursor-delegation-plan.md)
2. Start LM Studio local server on port `1234`
3. Configure `config/lm-studio.env` with your model name
4. Add tasks to `tasks/backlog.md`, then import:

   ```bash
   python scripts/task_router.py tasks/backlog.md
   ```

5. Run the local worker:

   ```bash
   python scripts/worker-local.py --watch
   ```

6. Handle `tier: cloud` tasks via Cursor Agent / Cloud Agents

## Structure

| Path | Purpose |
|------|---------|
| `docs/README.md` | Documentation index |
| `docs/lm-studio-cursor-delegation-plan.md` | Architecture and setup guide |
| `docs/discussions-report.md` | Full discussion and implementation report |
| `tasks/queue.json` | Master task queue |
| `tasks/backlog.md` | Human-friendly task intake |
| `scripts/worker-local.py` | LM Studio task runner |
| `scripts/task_router.py` | Auto-classify local vs cloud |
| `config/lm-studio.env` | Local API configuration |
