#!/usr/bin/env bash
# bin/smoke.sh — pull latest sharewire onto the Tailscale test laptop and rebuild.
#
# Run from your Mac. Requires an active Tailscale SSH session
# (tailscale ssh handles the auth handshake; this is a plain SSH client call).
#
# Does NOT restart `php artisan serve` — the dev server picks up code changes
# per-request. Only .env changes require a manual restart on the laptop.
#
# Override host or path with env vars:
#   LAPTOP_HOST=tweenson@100.124.43.125 APP_PATH=/home/tweenson/sharewire bin/smoke.sh

set -euo pipefail

LAPTOP_HOST="${LAPTOP_HOST:-tweenson@100.124.43.125}"
APP_PATH="${APP_PATH:-/home/tweenson/sharewire}"

echo "Smoke deploy → ${LAPTOP_HOST}:${APP_PATH}"
echo

ssh "$LAPTOP_HOST" "
    set -euo pipefail
    cd $APP_PATH
    echo '--- git pull (ff-only) ---'
    git pull --ff-only
    echo '--- composer install ---'
    composer install --no-interaction --prefer-dist
    echo '--- npm ci ---'
    npm ci
    echo '--- npm run build ---'
    npm run build
    echo '--- php artisan migrate --force ---'
    php artisan migrate --force
    echo '--- done ---'
"

echo
echo "Smoke deploy complete. Hit http://100.124.43.125:8000/ to verify."
