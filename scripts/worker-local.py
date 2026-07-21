#!/usr/bin/env python3
"""Process local-tier tasks from tasks/queue.json via LM Studio."""

from __future__ import annotations

import argparse
import json
import os
import sys
import time
import urllib.error
import urllib.request
from datetime import datetime, timezone
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
QUEUE_PATH = ROOT / "tasks" / "queue.json"
PROMPTS_DIR = ROOT / "tasks" / "prompts"
ENV_PATH = ROOT / "config" / "lm-studio.env"


def load_env(path: Path) -> dict[str, str]:
    env: dict[str, str] = {}
    if not path.exists():
        return env
    for line in path.read_text(encoding="utf-8").splitlines():
        line = line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, _, value = line.partition("=")
        env[key.strip()] = value.strip()
    return env


def load_queue() -> dict:
    if not QUEUE_PATH.exists():
        print(f"Queue not found: {QUEUE_PATH}", file=sys.stderr)
        sys.exit(1)
    return json.loads(QUEUE_PATH.read_text(encoding="utf-8"))


def save_queue(data: dict) -> None:
    QUEUE_PATH.write_text(json.dumps(data, indent=2) + "\n", encoding="utf-8")


def resolve_prompt(task: dict) -> str:
    if task.get("prompt"):
        return task["prompt"]

    task_type = task.get("type", "generic")
    template_path = PROMPTS_DIR / f"{task_type}.md"
    if template_path.exists():
        return template_path.read_text(encoding="utf-8")

    return "Complete the following task.\n\n---\n{content}"


def load_input_content(task: dict) -> str:
    input_spec = task.get("input", {})
    if "content" in input_spec:
        return input_spec["content"]

    if "path" in input_spec:
        input_path = ROOT / input_spec["path"]
        if not input_path.exists():
            raise FileNotFoundError(f"Input file not found: {input_path}")
        return input_path.read_text(encoding="utf-8")

    raise ValueError(f"Task {task.get('id')} has no input content or path")


def call_lm_studio(
    *,
    base_url: str,
    model: str,
    prompt: str,
    max_tokens: int,
    temperature: float,
) -> str:
    url = f"{base_url.rstrip('/')}/chat/completions"
    payload = {
        "model": model,
        "messages": [{"role": "user", "content": prompt}],
        "max_tokens": max_tokens,
        "temperature": temperature,
        "stream": False,
    }
    req = urllib.request.Request(
        url,
        data=json.dumps(payload).encode("utf-8"),
        headers={"Content-Type": "application/json"},
        method="POST",
    )
    try:
        with urllib.request.urlopen(req, timeout=300) as resp:
            body = json.loads(resp.read().decode("utf-8"))
    except urllib.error.URLError as exc:
        raise RuntimeError(
            f"LM Studio unreachable at {url}. Is the server running?"
        ) from exc

    choices = body.get("choices") or []
    if not choices:
        raise RuntimeError(f"Empty response from LM Studio: {body}")
    return choices[0]["message"]["content"]


def write_output(task: dict, content: str) -> Path:
    output_spec = task.get("output", {})
    if "path" not in output_spec:
        output_path = ROOT / "tasks" / "output" / f"{task['id']}.md"
    else:
        output_path = ROOT / output_spec["path"]

    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text(content, encoding="utf-8")
    return output_path


def process_one(task: dict, env: dict, dry_run: bool) -> bool:
    task_id = task.get("id", "<unknown>")
    print(f"Processing {task_id}: {task.get('title', '')}")

    if dry_run:
        print("  [dry-run] would call LM Studio")
        return True

    content = load_input_content(task)
    template = resolve_prompt(task)
    prompt = template.replace("{content}", content)

    result = call_lm_studio(
        base_url=env.get("LM_STUDIO_BASE_URL", "http://localhost:1234/v1"),
        model=env.get("LM_STUDIO_MODEL", "local-model"),
        prompt=prompt,
        max_tokens=int(env.get("LM_STUDIO_MAX_TOKENS", "2048")),
        temperature=float(env.get("LM_STUDIO_TEMPERATURE", "0.3")),
    )

    out_path = write_output(task, result)
    print(f"  Wrote {out_path.relative_to(ROOT)}")
    return True


def run_once(dry_run: bool) -> int:
    env = load_env(ENV_PATH)
    queue = load_queue()
    processed = 0

    for task in queue.get("tasks", []):
        if task.get("status") != "pending":
            continue
        if task.get("tier") != "local":
            continue

        task["status"] = "in_progress"
        task["started_at"] = datetime.now(timezone.utc).isoformat()
        if not dry_run:
            save_queue(queue)

        try:
            process_one(task, env, dry_run)
            task["status"] = "done"
            task["completed_at"] = datetime.now(timezone.utc).isoformat()
            processed += 1
        except Exception as exc:  # noqa: BLE001 — worker should continue on failure
            task["status"] = "failed"
            task["error"] = str(exc)
            print(f"  FAILED: {exc}", file=sys.stderr)

        if not dry_run:
            save_queue(queue)

    return processed


def main() -> None:
    parser = argparse.ArgumentParser(description="Run local-tier tasks via LM Studio")
    parser.add_argument("--watch", action="store_true", help="Poll queue continuously")
    parser.add_argument("--interval", type=int, default=30, help="Poll interval seconds")
    parser.add_argument("--dry-run", action="store_true", help="Skip LM Studio calls")
    args = parser.parse_args()

    if args.watch:
        print(f"Watching {QUEUE_PATH} every {args.interval}s (Ctrl+C to stop)")
        while True:
            count = run_once(args.dry_run)
            if count:
                print(f"Processed {count} task(s)")
            time.sleep(args.interval)
    else:
        count = run_once(args.dry_run)
        print(f"Done. Processed {count} task(s).")


if __name__ == "__main__":
    main()
