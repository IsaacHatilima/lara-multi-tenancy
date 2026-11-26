#!/usr/bin/env bash
# Drop this project's factory Postgres databases whose job worktree no longer exists,
# with the per-tenant roles granted on it. Run from the main checkout:
#
#   bash factory/drop-databases.sh            # drop the leftovers
#   bash factory/drop-databases.sh --dry-run  # only list them
set -euo pipefail

cd "$(git rev-parse --show-toplevel)"
source factory/pg.sh

dry_run="${1:-}"
live=""
for worktree in .worktrees/*/; do
    [ -d "$worktree" ] && live="$live $(factory_db_name "${worktree%/}")"
done

prefix_pattern="$(printf '%s' "$FACTORY_DB_PREFIX" | sed 's/_/\\_/g')%"
for db in $(psql -d postgres -Atc "select datname from pg_database where datname like '${prefix_pattern}' order by datname"); do
    case " $live " in
        *" $db "*) continue ;;
    esac
    if [ "$dry_run" = "--dry-run" ]; then
        echo "would drop $db"
    else
        factory_db_drop "$db"
        echo "dropped $db"
    fi
done
