# Discussions Report: LM Studio + Cursor Task Delegation

**Project:** CursorP1  
**Branch:** `cursor/lm-studio-task-delegation-54dd`  
**Pull Request:** https://github.com/mhaiprojects/CursorP1/pull/3  
**Cloud Agent Run:** https://cursor.com/agents/bc-09cda1bb-4af8-46d5-a547-6b17f22754dd  
**Owner:** Michael HAI (mhaiprojects@gmail.com)  
**Repository:** https://github.com/mhaiprojects/CursorP1  
**Report date:** 2026-07-22  
**Status:** Draft PR open; local worker tested in dry-run mode only

---

## 1. Executive Summary

This report documents the full discussion, research, design decisions, and implementation work for building a **hybrid AI task pipeline** that uses:

- **Cursor Cloud Agents** for complex, repository-aware work (code, tests, PRs)
- **LM Studio** (local OpenAI-compatible API) for small, text-only micro-tasks (summaries, formatting, extraction)

The goal is to keep AI running continuously on a long task backlog while minimizing cloud API cost by delegating trivial work to a locally hosted model.

---

## 2. Original User Request

### Request 1 (2026-07-21)

> Create a new branch. Plan to effectively use my local hosted AI with LM Studio and Cursor to keep AI running non-stop to work on a long list of tasks. Delegate some small tasks like formatting a document or summarising to local AI.

**Interpreted requirements:**

| Requirement | Interpretation |
|-------------|----------------|
| New branch | Feature branch off `main` |
| LM Studio integration | Local model server as OpenAI-compatible endpoint |
| Cursor integration | Route appropriate work through Cursor Agent / Cloud Agents |
| Non-stop processing | Continuous task queue with local worker polling |
| Task delegation | Classification rules: local vs cloud tier |
| Small tasks to local AI | Summaries, formatting, extraction, proofreading |

### Request 2 (2026-07-22)

> Commit code and discussions report with all details in `/docs` folder.

**Interpreted requirements:**

| Requirement | Interpretation |
|-------------|----------------|
| Commit code | Ensure all implementation is committed and pushed |
| Discussions report | Full record of conversation, decisions, and deliverables in `/docs` |
| All details | Include research, architecture, files, issues, and next steps |

---

## 3. Discussion Timeline

### Phase 1 — Scoping and Research

1. **Repository assessment**
   - Repo contained only `README.md` with title `# CursorP1`
   - No existing LM Studio, Cursor, or task-queue infrastructure
   - Greenfield implementation required

2. **External research conducted**
   - LM Studio OpenAI-compatible API (`http://localhost:1234/v1`)
   - Cursor custom model configuration (Override OpenAI Base URL)
   - Cursor architectural constraints for local models
   - ngrok / Cloudflare Tunnel requirements for HTTPS exposure

3. **Key finding — Cursor local model constraints**

   Cursor does **not** natively connect to raw `localhost` in most configurations. Per Cursor community forum and 2026 setup guides:

   - Requests are routed through Cursor's backend for prompt assembly
   - Custom endpoints require a **public HTTPS URL** (ngrok, Cloudflare Tunnel, or similar)
   - Base URL format: `https://YOUR-TUNNEL-DOMAIN/v1`
   - API key can be any non-empty placeholder (e.g. `lm-studio`)
   - Model name must match LM Studio's exact model ID
   - **Tab autocomplete** remains cloud-only; local models apply to Chat, Cmd+K, and some Agent flows
   - Toggle Override Base URL when switching between local and cloud models

### Phase 2 — Architecture Design

**Decision: Two-tier hybrid pipeline**

Instead of routing everything through Cursor (expensive for trivial tasks), split the system:

```
Task Backlog → Router → Local Worker (LM Studio)  [text micro-tasks]
                     → Cursor Cloud Agent         [code/repo tasks]
```

**Rationale:**

| Approach | Pros | Cons |
|----------|------|------|
| All via Cursor + local override | Single UI | Must toggle settings; cloud cost for agent orchestration |
| Direct LM Studio API for micro-tasks | Zero cloud cost; runs overnight | Separate worker process |
| **Hybrid (chosen)** | Best cost/quality split | Two systems to monitor |

### Phase 3 — Implementation

**Branch created:** `cursor/lm-studio-task-delegation-54dd`

**Deliverables:**

| Category | Files |
|----------|-------|
| Documentation | `docs/lm-studio-cursor-delegation-plan.md`, `docs/discussions-report.md` |
| Configuration | `config/lm-studio.env` |
| Scripts | `scripts/worker-local.py`, `scripts/task_router.py` |
| Task system | `tasks/queue.json`, `tasks/backlog.md`, `tasks/prompts/*.md` |
| Project docs | `README.md` (updated) |

**Commit:** `8022a4a` — "Add LM Studio + Cursor task delegation plan and local worker"

**Pull Request:** #3 (draft) — https://github.com/mhaiprojects/CursorP1/pull/3

### Phase 4 — Testing and Fixes

1. **Dry-run validation**
   - `python3 scripts/task_router.py tasks/backlog.md --dry-run` — parsed 10 backlog items
   - `python3 scripts/worker-local.py --dry-run` — identified 2 pending local tasks

2. **Bug found: keyword false positive**
   - Original router used substring matching
   - `"Proofread"` incorrectly matched cloud keyword `"pr"` (from "pull request")
   - **Fix:** Word-boundary matching via `contains_keyword()` and reorder: local keywords checked before cloud keywords
   - Removed bare `"pr"` from cloud keywords; added `"github action"` instead

3. **Not yet tested live**
   - LM Studio API calls (no LM Studio server in cloud agent environment)
   - ngrok / Cursor tunnel integration (requires user's local machine)
   - Cursor Cloud Agent execution of `tier: cloud` tasks

---

## 4. Architecture Decisions

### 4.1 Task Queue as Single Source of Truth

**Decision:** `tasks/queue.json` holds all tasks with explicit `tier`, `status`, and I/O paths.

**Status lifecycle:**

```
pending → in_progress → done
                     → failed
                     → blocked
```

**Why JSON over a database:** Minimal dependencies; works with git; easy for Cloud Agents to read/write.

### 4.2 Task Classification Rules

#### Local tier (LM Studio)

- Summarize documents
- Format markdown
- Extract action items
- Proofread prose
- Classify/tag tasks
- Draft commit messages from inline diffs
- Small structured output (< ~2000 tokens)
- No repository or terminal access needed

#### Cloud tier (Cursor Agent)

- Edit files in git repo
- Run tests, linters, builds
- Debug with codebase context
- Multi-file refactors
- Create branches, commits, PRs
- Install dependencies, configure CI

#### Decision tree

```
Does task require reading/writing files in a git repo?
  YES → cloud
  NO → Does it need running commands or tests?
    YES → cloud
    NO → Is output < 2000 tokens and well-scoped?
      YES → local
      NO → cloud
```

### 4.3 Local Worker Design

**Decision:** Standalone Python script calling LM Studio REST API directly.

- No Cursor involvement for local tier (avoids tunnel toggle overhead)
- Uses stdlib only (`urllib.request`) — no pip dependencies
- Supports `--watch` for continuous polling
- Writes outputs to `tasks/output/`
- Updates queue status in place

### 4.4 Prompt Templates

**Decision:** Reusable templates in `tasks/prompts/{type}.md` with `{content}` placeholder.

| Template | Task type |
|----------|-----------|
| `summarize.md` | Document summaries |
| `format-markdown.md` | Markdown cleanup |
| `extract-actions.md` | Action item extraction |

Tasks can override with inline `prompt` field in `queue.json`.

### 4.5 Parallelism Strategy

| Resource | Concurrency |
|----------|-------------|
| LM Studio | 1 job at a time (GPU-bound) |
| Cursor Cloud Agent | 1 agent per independent task/branch |
| Human reviewer | Batch-review local outputs + PRs |

**Overnight pattern:** Local worker processes text backlog while cloud agents handle code tasks during active hours.

---

## 5. Implementation Details

### 5.1 `scripts/worker-local.py`

**Purpose:** Process `tier: local` tasks from `tasks/queue.json`.

**Flow:**

1. Load config from `config/lm-studio.env`
2. Load queue from `tasks/queue.json`
3. For each `pending` + `local` task:
   - Set status `in_progress`
   - Load input (inline `content` or file `path`)
   - Resolve prompt (inline, template, or generic fallback)
   - POST to `{LM_STUDIO_BASE_URL}/chat/completions`
   - Write output to `tasks/output/` or task-specified path
   - Set status `done` or `failed`

**CLI options:**

```bash
python3 scripts/worker-local.py              # Process once
python3 scripts/worker-local.py --watch      # Poll continuously
python3 scripts/worker-local.py --watch --interval 30
python3 scripts/worker-local.py --dry-run    # Skip API calls
```

**Configuration (`config/lm-studio.env`):**

```env
LM_STUDIO_BASE_URL=http://localhost:1234/v1
LM_STUDIO_MODEL=qwen2.5-7b-instruct
LM_STUDIO_MAX_TOKENS=2048
LM_STUDIO_TEMPERATURE=0.3
```

### 5.2 `scripts/task_router.py`

**Purpose:** Parse markdown backlog and append classified tasks to queue.

**Backlog format:**

```markdown
- [ ] Summarize README
- [ ] Add unit tests for task_router.py
- [ ] Integrate with Cursor Automations
  > Depends on automation webhook setup
```

**Classification logic:**

1. Check local keywords first (word-boundary match)
2. Check cloud keywords
3. Check repo/code patterns (`repo`, `module`, `function`, `class`, `test suite`, `unit test`)
4. Default to `local` / `generic`

**CLI:**

```bash
python3 scripts/task_router.py tasks/backlog.md
python3 scripts/task_router.py tasks/backlog.md --dry-run
```

### 5.3 Sample Queue Tasks

| ID | Title | Tier | Status |
|----|-------|------|--------|
| `task-summarize-readme` | Summarize README | local | pending |
| `task-format-delegation-plan` | Format delegation plan headings | local | pending |
| `task-implement-router` | Add unit tests for task_router.py | cloud | pending |

---

## 6. LM Studio + Cursor Setup Reference

### 6.1 LM Studio

1. Install from https://lmstudio.ai/
2. Download model (recommended: Qwen2.5-7B-Instruct for speed, 14B+ for quality)
3. Local Server tab → enable CORS → Start Server on port 1234
4. Verify: `curl http://localhost:1234/v1/models`

### 6.2 HTTPS Tunnel (required for Cursor)

```bash
# Option A: ngrok
ngrok http 1234

# Option B: Cloudflare Tunnel
cloudflared tunnel --url http://localhost:1234
```

Cursor base URL: `https://YOUR-TUNNEL-DOMAIN/v1`

### 6.3 Cursor Settings

1. Settings → Models
2. OpenAI API Key: `lm-studio` (any non-empty string)
3. Enable **Override OpenAI Base URL**
4. Set URL to tunnel `/v1` endpoint
5. Add custom model matching LM Studio model ID
6. Deselect cloud models when using local exclusively

### 6.4 Limitations Acknowledged

| Limitation | Workaround |
|------------|------------|
| No native localhost support in Cursor | ngrok / Cloudflare Tunnel |
| Tab completion cloud-only | Use local for Chat/Cmd+K; accept cloud Tab |
| Prompts pass through Cursor servers | Use direct LM Studio API for sensitive local tasks |
| Override URL affects all OpenAI-routed models | Toggle setting when switching tiers |
| Single GPU = one local job at a time | Queue many small tasks; batch overnight |

---

## 7. File Inventory

```
cursorp1/
├── README.md                                    # Updated project overview
├── config/
│   └── lm-studio.env                            # LM Studio API configuration
├── docs/
│   ├── discussions-report.md                    # This report
│   └── lm-studio-cursor-delegation-plan.md      # Full architecture & setup guide
├── scripts/
│   ├── task_router.py                           # Backlog → queue classifier
│   └── worker-local.py                          # LM Studio task runner
└── tasks/
    ├── backlog.md                               # Human checkbox intake list
    ├── queue.json                               # Master task queue
    ├── output/                                  # Local task results (created at runtime)
    └── prompts/
        ├── extract-actions.md
        ├── format-markdown.md
        └── summarize.md
```

**Line counts (approximate):**

| File | Lines |
|------|-------|
| `docs/lm-studio-cursor-delegation-plan.md` | 409 |
| `docs/discussions-report.md` | (this file) |
| `scripts/worker-local.py` | 197 |
| `scripts/task_router.py` | 164 |
| Total new content in commit | 899 lines |

---

## 8. Recommended Workflow

### Daily operation

1. Add tasks to `tasks/backlog.md`
2. Import: `python3 scripts/task_router.py tasks/backlog.md`
3. Start local worker: `python3 scripts/worker-local.py --watch`
4. Launch Cursor Cloud Agents for `tier: cloud` tasks
5. Review `tasks/output/` and merge PRs
6. Mark blocked tasks when dependencies aren't met

### Example 24-hour run

| Time | Action |
|------|--------|
| T+0 | Add 30 tasks (10 cloud, 20 local) |
| T+5m | Start `worker-local.py --watch` |
| T+10m | Launch 2 Cloud Agents on independent tasks |
| T+1h | Review 8 local summaries; merge 1 PR |
| T+4h | Local worker finishes formatting backlog |
| Overnight | Local worker processes remaining text tasks |
| Next AM | Batch-review PRs + outputs |

---

## 9. Open Items and Next Steps

### Immediate (user action required)

- [ ] Install LM Studio and download a model
- [ ] Start LM Studio server on port 1234
- [ ] Set up ngrok or Cloudflare Tunnel
- [ ] Configure Cursor with tunnel URL and model ID
- [ ] Update `config/lm-studio.env` with actual model name
- [ ] Run first live local task: `python3 scripts/worker-local.py`
- [ ] Complete first cloud task via Cursor Agent

### Future enhancements (from backlog)

- [ ] Add unit tests for `task_router.py` (cloud tier)
- [ ] Add GitHub Action to validate `queue.json` schema
- [ ] Wire `worker-local.py` into systemd/launchd service
- [ ] Create PR template for cloud-agent tasks
- [ ] Integrate queue status with Cursor Automations

### Known gaps

| Gap | Notes |
|-----|-------|
| No cloud worker script | Cloud tasks handled manually via Cursor UI today |
| No queue schema validation | JSON is free-form; CI validation planned |
| Router classification is heuristic | May misclassify edge cases; manual `tier` override in queue.json |
| No retry logic in local worker | Failed tasks stay `failed`; re-set to `pending` manually |

---

## 10. Git and PR Reference

| Item | Value |
|------|-------|
| Base branch | `main` |
| Feature branch | `cursor/lm-studio-task-delegation-54dd` |
| Commit | `8022a4a` |
| Commit message | Add LM Studio + Cursor task delegation plan and local worker |
| PR | #3 (draft) |
| PR URL | https://github.com/mhaiprojects/CursorP1/pull/3 |
| PR title | Add LM Studio + Cursor hybrid task delegation plan |

### Commit contents

```
 README.md                                |  32 +++
 config/lm-studio.env                     |   4 +
 docs/lm-studio-cursor-delegation-plan.md | 409 +++++++++++++++++++++++++++++++
 scripts/task_router.py                   | 164 +++++++++++++
 scripts/worker-local.py                  | 197 +++++++++++++++
 tasks/backlog.md                         |  27 ++
 tasks/prompts/extract-actions.md         |   5 +
 tasks/prompts/format-markdown.md         |   8 +
 tasks/prompts/summarize.md               |   7 +
 tasks/queue.json                         |  46 ++++
 10 files changed, 899 insertions(+)
```

---

## 11. Troubleshooting Guide

| Problem | Cause | Fix |
|---------|-------|-----|
| Cursor can't reach LM Studio | Tunnel down or wrong URL | Verify ngrok running; URL ends with `/v1` |
| "Invalid model" in Cursor | Model ID mismatch | Use exact ID from `GET /v1/models` |
| Local worker timeout | Large input or slow GPU | Reduce input size; increase timeout |
| Task misclassified as cloud | Keyword heuristic | Override `tier` manually in `queue.json` |
| "Proofread" classified as cloud | Was substring `"pr"` bug | Fixed with word-boundary matching |
| LM Studio unreachable | Server not started | Start server in LM Studio GUI |
| Empty response from LM Studio | Model not loaded | Select model in Local Server tab |

---

## 12. References

- LM Studio: https://lmstudio.ai/
- LM Studio Developer Docs: https://lmstudio.ai/docs/developer
- Cursor Community Forum (local LLM): https://forum.cursor.com/t/how-can-i-use-a-local-llm-on-my-desktop-ai-computer/152419
- Cursor Cloud Agent run: https://cursor.com/agents/bc-09cda1bb-4af8-46d5-a547-6b17f22754dd
- Repository: https://github.com/mhaiprojects/CursorP1
- Pull Request #3: https://github.com/mhaiprojects/CursorP1/pull/3

---

## 13. Conclusion

The hybrid LM Studio + Cursor architecture provides a practical path to continuous AI-assisted work on long task backlogs. Local micro-tasks run cheaply overnight via a direct API worker, while complex repository work stays on Cursor Cloud Agents where tool access, testing, and PR workflows are available.

All code, configuration, prompt templates, and documentation are committed on branch `cursor/lm-studio-task-delegation-54dd` and available for review in draft PR #3.
