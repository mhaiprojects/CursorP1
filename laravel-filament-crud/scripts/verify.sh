#!/usr/bin/env bash
#
# verify.sh — verify the Cursor integration features are working.
#
# Runs the linter, the automated test suite, and a couple of live smoke tests
# against the Cursor task/queue/chat features (using the offline "fake" driver
# so no API key is required). Exits non-zero if anything fails.
#
# Usage:
#   ./scripts/verify.sh
#
# Set CURSOR_DRIVER=cli (and CURSOR_API_KEY) to smoke-test the real Cursor CLI.

set -uo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."

PASS=0
FAIL=0

step()  { printf '\n\033[1;34m==> %s\033[0m\n' "$1"; }
ok()    { printf '\033[1;32m  ✓ %s\033[0m\n' "$1"; PASS=$((PASS + 1)); }
bad()   { printf '\033[1;31m  ✗ %s\033[0m\n' "$1"; FAIL=$((FAIL + 1)); }

run() {
    local label="$1"; shift
    if "$@" >/tmp/verify_step.log 2>&1; then
        ok "$label"
    else
        bad "$label"
        sed 's/^/      /' /tmp/verify_step.log | tail -25
    fi
}

step "1/4 Lint (Pint)"
run "code style" ./vendor/bin/pint --test

step "2/4 Automated tests (Cursor + tasks + console + chat)"
run "unit + feature suite" php artisan test \
    tests/Unit/Cursor tests/Feature/Cursor tests/Feature/Tasks tests/Feature/Console

step "3/4 Smoke: cursor:run command"
if CURSOR_DRIVER="${CURSOR_DRIVER:-fake}" php artisan cursor:run "smoke test: reply with OK" \
        >/tmp/verify_cursor.log 2>&1; then
    ok "cursor:run produced a response"
    sed 's/^/      /' /tmp/verify_cursor.log
else
    bad "cursor:run failed"
    sed 's/^/      /' /tmp/verify_cursor.log
fi

step "4/4 Smoke: schedule + queue wiring"
run "tasks:dispatch-due command runs" php artisan tasks:dispatch-due
run "events:dispatch-due command runs" php artisan events:dispatch-due
run "scheduled tasks are registered" bash -c "php artisan schedule:list | grep -q 'tasks:dispatch-due'"

echo ""
printf '\033[1m----------------------------------------\033[0m\n'
printf 'Verification complete: \033[1;32m%d passed\033[0m, \033[1;31m%d failed\033[0m\n' "$PASS" "$FAIL"

if [ "$FAIL" -gt 0 ]; then
    exit 1
fi
