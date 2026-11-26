# Shared by factory/serve.sh and factory/drop-databases.sh. Source it; don't run it.
#
# Each job worktree gets its own throwaway Postgres database, factory_<project>_<worktree>,
# on the local server described by .env.example (never the owner's database).

FACTORY_PG_HOST="${FACTORY_PG_HOST:-127.0.0.1}"
FACTORY_PG_PORT="${FACTORY_PG_PORT:-5432}"
FACTORY_PG_USER="${FACTORY_PG_USER:-postgres}"
FACTORY_PG_PASSWORD="${FACTORY_PG_PASSWORD:-}"

export PGHOST="$FACTORY_PG_HOST" PGPORT="$FACTORY_PG_PORT" PGUSER="$FACTORY_PG_USER" PGPASSWORD="$FACTORY_PG_PASSWORD"

# Other projects' factories share the local server, so every name carries this
# project's prefix, and only that prefix is ever dropped.
factory_sanitize() {
    printf '%s' "$1" | tr 'A-Z-' 'a-z_' | tr -cd 'a-z0-9_'
}

# The main checkout's folder name, the same from the main checkout and from any worktree.
FACTORY_DB_PREFIX="factory_$(factory_sanitize "$(basename "$(dirname "$(git rev-parse --path-format=absolute --git-common-dir)")")")_"

# factory_<project>_<worktree folder>, at most 63 characters.
factory_db_name() {
    printf '%s%s' "$FACTORY_DB_PREFIX" "$(factory_sanitize "$(basename "$1")")" | cut -c1-63
}

factory_db_exists() {
    [ "$(psql -d postgres -Atc "select 1 from pg_database where datname = '$1'")" = "1" ]
}

# Drops the database and the per-tenant roles that tenancy granted on it.
factory_db_drop() {
    local db="$1" roles role
    factory_db_exists "$db" || return 0

    roles="$(psql -d postgres -Atc "select r.rolname from pg_shdepend d join pg_roles r on r.oid = d.refobjid where d.classid = 'pg_database'::regclass and d.objid = (select oid from pg_database where datname = '$db')")"

    while IFS= read -r role; do
        [ -n "$role" ] && psql -d "$db" -qc "DROP OWNED BY \"$role\""
    done <<< "$roles"

    dropdb --if-exists --force "$db"

    while IFS= read -r role; do
        [ -n "$role" ] && psql -d postgres -qc "DROP ROLE IF EXISTS \"$role\""
    done <<< "$roles"
}
