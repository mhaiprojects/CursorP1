#!/usr/bin/env python3
"""Classify backlog items as local or cloud tier and append to queue.json."""

from __future__ import annotations

import argparse
import json
import re
import sys
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
QUEUE_PATH = ROOT / "tasks" / "queue.json"

LOCAL_KEYWORDS = {
    "summarize",
    "summary",
    "format",
    "formatting",
    "proofread",
    "spell",
    "grammar",
    "extract",
    "action items",
    "tag",
    "classify",
    "rename in snippet",
    "draft commit message",
    "clean up markdown",
    "bullet points",
}

CLOUD_KEYWORDS = {
    "implement",
    "refactor",
    "fix bug",
    "debug",
    "add test",
    "install",
    "deploy",
    "pull request",
    "github action",
    "ci",
    "lint",
    "build",
    "migrate",
    "api endpoint",
    "database",
}


def contains_keyword(text: str, keyword: str) -> bool:
    if " " in keyword:
        return keyword in text
    return re.search(rf"\b{re.escape(keyword)}\b", text) is not None


def classify(title: str, body: str = "") -> tuple[str, str]:
    text = f"{title} {body}".lower()

    for keyword in LOCAL_KEYWORDS:
        if contains_keyword(text, keyword):
            task_type = "summarize" if "summar" in keyword else "format-markdown"
            if "action" in keyword or "extract" in keyword:
                task_type = "extract-actions"
            return "local", task_type

    for keyword in CLOUD_KEYWORDS:
        if contains_keyword(text, keyword):
            return "cloud", "code"

    if re.search(r"\b(repo|module|function|class|test suite|unit test)\b", text):
        return "cloud", "code"

    return "local", "generic"


def parse_backlog(path: Path) -> list[dict]:
    lines = path.read_text(encoding="utf-8").splitlines()
    items: list[dict] = []
    current: dict | None = None

    for line in lines:
        match = re.match(r"^-\s+\[( |x)\]\s+(.+)$", line.strip())
        if match:
            if current:
                items.append(current)
            done = match.group(1) == "x"
            title = match.group(2).strip()
            tier, task_type = classify(title)
            current = {
                "title": title,
                "tier": tier,
                "type": task_type,
                "status": "done" if done else "pending",
            }
        elif current and line.strip().startswith(">"):
            note = line.strip().lstrip(">").strip()
            current.setdefault("notes", []).append(note)

    if current:
        items.append(current)

    return items


def slugify(text: str) -> str:
    slug = re.sub(r"[^a-z0-9]+", "-", text.lower()).strip("-")
    return slug[:40] or "task"


def merge_into_queue(items: list[dict], dry_run: bool) -> int:
    queue = {"version": 1, "tasks": []}
    if QUEUE_PATH.exists():
        queue = json.loads(QUEUE_PATH.read_text(encoding="utf-8"))

    existing_titles = {t.get("title") for t in queue.get("tasks", [])}
    added = 0
    now = datetime.now(timezone.utc).isoformat()

    for item in items:
        if item["title"] in existing_titles:
            continue
        task_id = f"task-{slugify(item['title'])}"
        task = {
            "id": task_id,
            "title": item["title"],
            "type": item["type"],
            "tier": item["tier"],
            "status": item["status"],
            "created_at": now,
        }
        if item.get("notes"):
            task["prompt"] = "\n".join(item["notes"])
        queue.setdefault("tasks", []).append(task)
        added += 1
        print(f"  [{item['tier']:5}] {item['title']}")

    if not dry_run and added:
        QUEUE_PATH.parent.mkdir(parents=True, exist_ok=True)
        QUEUE_PATH.write_text(json.dumps(queue, indent=2) + "\n", encoding="utf-8")

    return added


def main() -> None:
    parser = argparse.ArgumentParser(description="Route backlog items to local/cloud tiers")
    parser.add_argument("backlog", type=Path, help="Path to markdown backlog file")
    parser.add_argument("--dry-run", action="store_true")
    args = parser.parse_args()

    if not args.backlog.exists():
        print(f"Backlog not found: {args.backlog}", file=sys.stderr)
        sys.exit(1)

    items = parse_backlog(args.backlog)
    print(f"Parsed {len(items)} item(s) from {args.backlog}")
    added = merge_into_queue(items, args.dry_run)
    print(f"Added {added} new task(s) to {QUEUE_PATH}")


if __name__ == "__main__":
    main()
