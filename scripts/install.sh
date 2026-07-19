#!/usr/bin/env bash
#
# install.sh — one-shot installer that recreates the CursorP1 demo
# (Laravel 13 + Filament 5: login, scheduled events, event log, task queue)
# on a fresh environment.
#
# By default this provisions a proof-of-concept (POC) demo: a clean database,
# a default admin login, sample scheduled events, and it immediately processes
# the due events through the queue so the Event Log is populated.
#
# Usage:
#   ./scripts/install.sh              # full POC install (RESETS the database)
#   ./scripts/install.sh --no-reset   # install without wiping existing data
#   ./scripts/install.sh --no-process # skip processing due demo events
#   ./scripts/install.sh --help
#
# Prerequisites: PHP 8.3+, Composer 2, Node.js 20+/npm.

set -euo pipefail

RESET_DB=1
PROCESS_EVENTS=1

for arg in "$@"; do
    case "$arg" in
        --no-reset) RESET_DB=0 ;;
        --no-process) PROCESS_EVENTS=0 ;;
        -h|--help)
            grep '^#' "$0" | sed 's/^# \{0,1\}//'
            exit 0
            ;;
        *)
            echo "Unknown option: $arg" >&2
            echo "Run '$0 --help' for usage." >&2
            exit 1
            ;;
    esac
done

# Always run from the project root (parent of this script's directory).
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR/.."

info() { printf '\n\033[1;34m==>\033[0m %s\n' "$1"; }
die()  { printf '\n\033[1;31mError:\033[0m %s\n' "$1" >&2; exit 1; }

info "Checking prerequisites"
command -v php >/dev/null 2>&1 || die "PHP is not installed (need PHP 8.3+)."
command -v composer >/dev/null 2>&1 || die "Composer is not installed."
command -v npm >/dev/null 2>&1 || die "npm/Node.js is not installed."
php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);' \
    || die "PHP 8.3+ is required (found $(php -r 'echo PHP_VERSION;'))."
echo "PHP $(php -r 'echo PHP_VERSION;'), $(composer --version | head -1), npm $(npm --version)"

info "Installing PHP dependencies (composer install)"
composer install --no-interaction

info "Installing JavaScript dependencies (npm install)"
npm install

info "Preparing environment file (.env)"
if [ ! -f .env ]; then
    cp .env.example .env
    echo "Created .env from .env.example"
else
    echo ".env already exists — leaving it untouched"
fi

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
else
    echo "APP_KEY already set"
fi

info "Preparing SQLite database file"
DB_FILE="database/database.sqlite"
touch "$DB_FILE"
echo "Ensured $DB_FILE exists"

if [ "$RESET_DB" -eq 1 ]; then
    info "Resetting database and seeding POC demo data (migrate:fresh --seed)"
    php artisan migrate:fresh --seed --force
else
    info "Running migrations and seeding POC demo data (migrate + db:seed)"
    php artisan migrate --force
    php artisan db:seed --class=Database\\Seeders\\PocSeeder --force
fi

info "Building front-end assets (npm run build)"
npm run build

if [ "$PROCESS_EVENTS" -eq 1 ]; then
    info "Processing due demo events through the queue"
    php artisan events:dispatch-due
    php artisan queue:work --stop-when-empty --tries=1
fi

info "Done!"
cat <<'EOF'

The demo is ready. Start the app with:

    composer dev        # server + queue worker + logs + Vite

...or run the pieces individually:

    php artisan serve            # http://localhost:8000
    php artisan queue:work       # process queued jobs
    php artisan schedule:work    # run the scheduler loop

Then log in to the Filament admin at http://localhost:8000/admin

    Email:    admin@example.com
    Password: password

EOF
