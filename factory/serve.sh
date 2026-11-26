#!/usr/bin/env bash
# Serve a job worktree on a port for browser checks, on its own SQLite database.
#
#   bash factory/serve.sh <port>            # fresh database, migrated and seeded, then serve
#   bash factory/serve.sh <port> --keep     # serve the existing database as is
#
# Flock gives every worktree a .env pointing at the owner's main dev database.
# This script never uses it: it writes .env.factory (SQLite, file cache) and
# runs every command with --env=factory, which makes Laravel load that file
# instead. Run any other artisan command against this app with --env=factory.
#
# Central app: http://localhost:<port>. Seeded tenants: http://tenant1.localhost:<port>
# and tenant2 (browsers resolve *.localhost to 127.0.0.1). Logins:
# central@mail.test, tenant1@mail.test, tenant2@mail.test, all with Password1#.
set -euo pipefail

port="${1:?usage: factory/serve.sh <port> [--keep]}"
keep="${2:-}"

# A worktree's .git is a file; the main checkout's is a directory.
if [ ! -f .git ]; then
    echo "factory/serve.sh runs only inside a job worktree, not the main checkout." >&2
    exit 1
fi

# Laravel keeps the first value of a repeated key, so drop the keys set below.
grep -vE '^(APP_ENV|APP_DEBUG|APP_URL|DB_CONNECTION|DB_DATABASE|CACHE_STORE|SESSION_DRIVER|QUEUE_CONNECTION|FILESYSTEM_DISK|BROADCAST_CONNECTION)=' \
    .env.example > .env.factory
cat >> .env.factory <<EOF

# Written by factory/serve.sh
APP_ENV=factory
APP_DEBUG=true
APP_URL=http://localhost:${port}
DB_CONNECTION=sqlite
DB_DATABASE=${PWD}/database/factory.sqlite
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
    rm -f database/factory.sqlite database/tenant_*.sqlite
    touch database/factory.sqlite
    php artisan migrate:fresh --seed --force --no-interaction --env=factory
fi

[ -f public/build/manifest.json ] || npm run build

exec php artisan serve --host=127.0.0.1 --port="${port}" --no-reload --env=factory
