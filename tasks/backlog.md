# Task Backlog

Add tasks here as markdown checkboxes. Run the router to import into `queue.json`:

```bash
python scripts/task_router.py tasks/backlog.md
```

## Local candidates (summaries, formatting, extraction)

- [ ] Summarize README
- [ ] Format delegation plan headings
- [ ] Extract action items from delegation plan
- [ ] Proofread tasks/backlog.md for grammar
- [ ] Draft commit message for delegation scripts

## Cloud candidates (code, repo, tests)

- [ ] Add unit tests for task_router.py
- [ ] Add GitHub Action to validate queue.json schema
- [ ] Wire worker-local.py into a systemd/launchd service
- [ ] Create PR template for cloud-agent tasks

## Blocked / waiting

- [ ] Integrate queue status with Cursor Automations
  > Depends on automation webhook setup
