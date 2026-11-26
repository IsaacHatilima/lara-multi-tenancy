#!/usr/bin/env bash
# Serve a job worktree on a port for browser checks, on its own throwaway Postgres database.
#
#   bash factory/serve.sh <port>            # fresh database factory_<worktree>, migrated and seeded, then serve
#   bash factory/serve.sh <port> --keep     # serve the existing database as is
#   bash factory/serve.sh --drop            # drop this worktree's database and its tenant roles
#
# Flock gives every worktree a .env pointing at the owner's main dev database.
# This script never uses it: it writes .env.factory and runs every command with
# --env=factory, which makes Laravel load that file instead. Run any other
# artisan command against this app with --env=factory.
#
# Central app: http://localhost:<port>. Seeded tenants: http://tenant1.localhost:<port>
# and tenant2 (browsers resolve *.localhost to 127.0.0.1). Logins:
# central@mail.test, tenant1@mail.test, tenant2@mail.test, all with Password1#.
set -euo pipefail

# A worktree's .git is a file; the main checkout's is a directory.
if [ ! -f .git ]; then
    echo "factory/serve.sh runs only inside a job worktree, not the main checkout." >&2
    exit 1
fi

source "$(dirname "$0")/pg.sh"
db="$(factory_db_name "$PWD")"

if [ "${1:-}" = "--drop" ]; then
    factory_db_drop "$db"
    rm -f .env.factory
    echo "Dropped $db."
    exit 0
fi

port="${1:?usage: factory/serve.sh <port> [--keep] | --drop}"
keep="${2:-}"

# Laravel keeps the first value of a repeated key, so drop the keys set below.
grep -vE '^(APP_ENV|APP_DEBUG|APP_URL|DB_CONNECTION|DB_HOST|DB_PORT|DB_DATABASE|DB_USERNAME|DB_PASSWORD|CACHE_STORE|SESSION_DRIVER|QUEUE_CONNECTION|FILESYSTEM_DISK|BROADCAST_CONNECTION)=' \
    .env.example > .env.factory
cat >> .env.factory <<EOF

# Written by factory/serve.sh
APP_ENV=factory
APP_DEBUG=true
APP_URL=http://localhost:${port}
DB_CONNECTION=pgsql
DB_HOST=${FACTORY_PG_HOST}
DB_PORT=${FACTORY_PG_PORT}
DB_DATABASE=${db}
DB_USERNAME=${FACTORY_PG_USER}
DB_PASSWORD=${FACTORY_PG_PASSWORD}
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log
EOF
php artisan key:generate --env=factory --force --no-interaction --quiet

# The Vite dev server's hot file would point pages at another checkout's assets.
rm -f public/hot

if [ "$keep" != "--keep" ]; then
    factory_db_drop "$db"
    createdb "$db"
    php artisan migrate --seed --force --no-interaction --env=factory
fi

[ -f public/build/manifest.json ] || npm run build

exec php artisan serve --host=127.0.0.1 --port="${port}" --no-reload --env=factory
