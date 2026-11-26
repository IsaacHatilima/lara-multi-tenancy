#!/usr/bin/env bash
# The project's checks. Exit 0 only when the branch is fit to review.
#
# The gate runs this script from the main checkout with the job's worktree as
# the working directory, and sets FACTORY_BASE to the base ref (origin/main).
# It mirrors CI (`composer setup` then `composer ci:check`) plus PHP Insights,
# and never rewrites files: no --fix, no formatter writes.
set -euo pipefail

export COMPOSER_NO_INTERACTION=1

# A fresh worktree has no dependencies, .env or generated Wayfinder files.
composer install --no-interaction --prefer-dist --no-progress --quiet
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64:' .env || php artisan key:generate --no-interaction --quiet
npm ci --prefer-offline --no-audit --no-fund --loglevel=error

# The build also generates the Wayfinder routes and actions the type check reads.
npm run build

# Frontend: lint and format check, then types.
npm run check
npm run types:check

# Backend: style, static analysis, code quality, tests (SQLite in memory, per phpunit.xml).
php artisan config:clear --no-interaction --quiet
vendor/bin/pint --parallel --test
vendor/bin/phpstan analyse --no-progress --memory-limit=1G
php artisan insights --no-interaction
php artisan test --compact
