# CursorP1

Planning and documentation hub for all projects.

## Convention

**One folder per project.** Each project lives at the repository root as its own directory.

```
CursorP1/
├── README.md              ← this index
├── _template/             ← copy when starting a new project
└── <project-name>/        ← one folder per project
    ├── README.md          ← identity, status, links
    ├── overview.md        ← what it is and why it exists
    ├── plan.md            ← current plan and open questions
    └── decisions.md       ← decisions log (newest first)
```

## Projects

| Project | Status | Folder | Source branch |
|---------|--------|--------|---------------|
| LM Studio task delegation | Active | [lm-studio-task-delegation/](./lm-studio-task-delegation/) | `cursor/lm-studio-task-delegation-54dd` |
| Laravel Filament CRUD | Active | [laravel-filament-crud/](./laravel-filament-crud/) | `cursor/setup-laravel-filament-crud-4bac` |
| POE1 builds & tools | Active | [poe1/](./poe1/) | `cursor/poe1-f301` |

## How to add a project

1. Copy `_template/` to a new folder named after the project (lowercase, hyphenated), e.g. `my-app/`.
2. Fill in `README.md`, `overview.md`, and `plan.md`.
3. Add a row to the **Projects** table above.
4. Keep planning and docs here; keep application code in the project’s own repository when it outgrows this hub.
