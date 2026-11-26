<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== spatie/laravel-activitylog/core rules ===

## Activitylog

- `spatie/laravel-activitylog` logs model events and manual activities to a database table, with subjects, causers, tracked attribute changes, and custom properties.
- Always activate the `activitylog-development` skill when working with activity logging or any code that uses the `LogsActivity`, `CausesActivity`, or `HasActivity` traits, `LogOptions`, the `Activity` model, or the `activity()` helper.
- If the skill is unavailable, read `vendor/spatie/laravel-activitylog/resources/boost/skills/activitylog-development/SKILL.md` before making changes.

</laravel-boost-guidelines>

<!-- skills:start -->
## Skills

Agent skills live in `.ai/skills/`, one folder per skill with a `SKILL.md`. Before work that a skill covers, read its `SKILL.md` in full and follow it; these instructions win where they differ. `.ai/skills/catalog.md` indexes them and maps a skill id such as `laravel/actions` to its folder, `laravel-actions`; a skill that names another one by its bare name means the same group.

| Skill | Use when |
|---|---|
| `brand-identity` | creating or applying a product's brand - composing its colour system from the brand-colours library (for example "a brand around green, red and gray"), writing brand guidelines, theming an app or the vite-dashboard starter, typography, logo and app icons, BRAND.md - or when reviewing UI for off-brand colours, raw hex values, contrast, or logo misuse. |
| `frontend-app-shell` | building or reviewing the frame around a web app's screens - choosing or changing the dashboard or auth shell, adding a page with its route, navigation item and breadcrumbs, the user menu and settings, page layout and density, or page-level loading, empty and error states - especially in projects built on the vite-dashboard starter. |
| `frontend-data-display` | designing, building, or reviewing how records and metrics are shown (data tables, lists, activity or audit logs, timelines, KPI tiles, summary charts), especially when a table looks generic, numbers don't line up, status is plain text, history is buried in a table, or every section sits in the same card. |
| `frontend-overlays` | adding, choosing, or reviewing a dialog, modal, drawer, sheet, popover, dropdown menu, tooltip, toast, or confirmation, especially when it's unclear what opens it or whether it needs a URL, or when overlays stack, lose focus, close by accident, drop unsaved work, blur the page, or break on mobile. |
| `knowledge-learning` | retrieving project lessons before planning, building or reviewing related work, or recording and revising reusable engineering lessons from explicitly verified fixes or dismissed findings. Keeps an agent-facing knowledge index grouped by technical area; does not turn unresolved review opinions into rules. |
| `laravel-actions` | creating, refactoring, or reviewing a Laravel action or operation service that makes a business decision or coordinates reads and writes through repositories. |
| `laravel-api-responses` | adding or changing a Laravel JSON API endpoint, its response envelope or pagination output, or how API errors such as 401, 403, 404, 422, 429, and 500 are rendered. |
| `laravel-architecture` | placing new Laravel behaviour, naming a new class, planning a feature or refactor, or reviewing where queries, validation, authorization, and responses belong. |
| `laravel-controllers` | adding or changing Laravel controllers or routes, wiring a FormRequest and action to an endpoint, or choosing between an API response, Inertia page, and redirect. |
| `laravel-data` | creating or changing Laravel input Data classes, output Resources, or the frontend-facing representation of a model, including partial updates. |
| `laravel-eloquent-strictness` | enabling Eloquent strict mode, or when fixing a lazy-loading, missing-attribute, or discarded-attribute violation or an N+1 query. |
| `laravel-enums` | adding or changing a PHP enum used by a Laravel model, or its stored values, labels, validation, or select options. |
| `laravel-frontend` | adding or changing React or Inertia pages, page props, Wayfinder routes, forms, or shared UI in a Laravel application's resources/js. |
| `laravel-insights` | adding, configuring, or running PHP Insights code-quality checks in a Laravel application, or when Insights reports a finding. |
| `laravel-jobs` | adding or reviewing Laravel queued jobs, listeners, or background work such as exports, reports, webhooks, imports, and notifications, or when a request is slow because it does work the user doesn't need to wait for. |
| `laravel-migrations` | adding or changing a Laravel migration, table, column, index, foreign key, or schema backfill. |
| `laravel-models` | creating or changing an Eloquent model, or its casts, relationships, scopes, fillable columns, or PHPDoc properties. |
| `laravel-phpstan` | adding, configuring, or running PHPStan or Larastan static analysis in a Laravel application, or when PHPStan reports an error. |
| `laravel-repositories` | adding or changing Laravel database queries, writes, relation loads, pagination, locks or transaction boundaries, or moving persistence out of an action or service. |
| `laravel-requests` | adding or changing a Laravel FormRequest, validation rules, request authorization, or query parameters such as filters, sorting, and pagination. |
| `laravel-security` | adding or reviewing Laravel authorization, policies, authentication, API tokens, CORS, CSRF exceptions, file uploads, raw queries, HTML output, rate limits, or production configuration, or before deploying a Laravel application. |
| `laravel-tests` | writing, choosing, or running tests for a Laravel application, including Pest unit, feature, action, HTTP, and browser tests, or when deciding which tests a change needs. |
| `planning-brainstorming` | a person wants to brainstorm an idea, feature or design, asks to be grilled or interviewed about it, or needs to work through intent, approaches and unresolved decisions before implementation. Combines brainstorming with rounds of probing questions and recommended answers. Skip fully specified changes that are already authorized. |
| `quality-deslop` | finishing or reviewing a branch for AI-generated slop - comments that narrate the change or don't match the file's style, defensive checks and try/catch on trusted paths, casts to any to silence the compiler, needless nesting, leftover debug output, and anything else that doesn't look like the surrounding code. |
| `quality-review` | reviewing a diff, branch, or pull request and deciding what blocks the merge, especially when findings mix real bugs with pre-existing problems, style preferences, or claims about code outside the diff. |
| `quality-security-review` | reviewing a diff, branch, or pull request for security problems - authorization and tenant isolation, injection, sessions and tokens, redirects, client-side and webview issues, secrets and sensitive data, LLM and agent tools, queues and webhooks, CI and dependencies, resource exhaustion - and deciding which ones block the merge. |
| `quality-simplicity` | writing, refactoring, or reviewing code that should be easy for the next developer to read, especially code written under time pressure, code that grew defensive checks or options nobody asked for, or code with nested conditionals, pass-through methods, or comments that no longer match. |
| `quality-thermo-nuclear-review` | reviewing or finishing a change with an extremely strict maintainability bar - abstraction quality, files pushed past 1,000 lines, ad-hoc branching and spaghetti growth, wrong-layer logic, casts and optionality that hide the real contract, and missed "code judo" restructurings that would make the change dramatically simpler. |
| `typescript-best-practices` | reading, writing, or reviewing any .ts or .tsx file - modelling state with discriminated unions, branding ids, parsing external data at the boundary with the project's schema library, removing as-casts and any, exhaustive switches, and deciding where validation belongs. |

The software factory runs as `/factory` in Claude Code and `$factory` in Codex; its workflow is `.ai/skills/factory/AGENT.md`.

Installed by the skills repository's `./install`; edit outside this block.
<!-- skills:end -->

## IMPORTANT: Migrations are split between central and tenant databases

Every company (tenant) has its own database, and the central database holds the platform itself. Migrations live in three folders, and the folder decides which databases a migration runs on:

| Folder | Runs on | Use for |
|---|---|---|
| `database/migrations/` | central **and** every tenant | Features both sides have, such as users, roles and permissions, passkeys, activity log, and messaging. |
| `database/migrations/central/` | central only | Platform tables: `tenants`, `domains`, queued jobs, billing, and anything that refers to a tenant by `tenant_id`. |
| `database/migrations/tenant/` | tenants only | Features that exist only inside a company's own database. |

- `php artisan migrate` runs shared + central (`AppServiceProvider::configureMigrations()`). `php artisan tenants:migrate` runs shared + tenant (`migration_parameters` in `config/tenancy.php`). New tenants are migrated automatically when they are created. On deploy, run `php artisan migrate --force && php artisan tenants:migrate && php artisan features:sync`, central first. On a managed deployment, `php artisan db:seed --force` creates the default plans (Free, Basic and Pro); it is safe to rerun, because it skips plans that already exist.
- `make:migration` and `vendor:publish` write to `database/migrations/`, which means **shared**. For a one-sided migration pass the folder, for example `php artisan make:migration create_plans_table --path=database/migrations/central`, and move published package migrations by hand.
- A shared migration must work in both databases. It must not reference `tenants`, `tenant_id`, or any central-only or tenant-only table. When one side needs extra columns, add them in a separate migration in that side's folder.
- Migration filenames must be unique across all three folders. Each database records migrations by filename, so never rename or reuse one that has already run.
- Tests that build a tenant database must migrate both `database/migrations` and `database/migrations/tenant`, as `createAuthenticationTenant()` in `tests/Feature/TenantAuthenticationTest.php` does.

**If it is not clear whether a feature's migration belongs to central, tenants, or both, stop and ask the human before creating it.** Don't guess: a table in the wrong folder either leaks platform data into every tenant database or is missing where the feature needs it, and fixing it later needs a data migration on every tenant.

## Motion

Motion explains what changed: something opened, moved, was added or was saved. It is functional by default; the theme picker, sheet and dialog content, and charts may carry a little more character.

- **One switch.** When the device asks for reduced motion, nothing animates. `resources/css/app.css` zeroes every CSS animation and transition under `prefers-reduced-motion: reduce`, and `MotionProvider` (`resources/js/components/motion-provider.tsx`, mounted in `app.tsx`) sets the motion library to follow the same setting. Never add a per-component reduced-motion override.
- **Timing.** Use the tokens, never literal values: `--motion-fast` 120ms (hover, press, page fade), `--motion-base` 200ms (overlays, selection), `--motion-slow` 300ms (height and layout), with `--motion-ease-out` for entrances. The motion library reads the same values from `resources/js/lib/motion.ts` (`duration`, `slideSpring`, `resize`, `crossfade`); change both together.
- **CSS first.** Opening, closing, fading and hover states use CSS: Tailwind transitions, `tw-animate-css` and Base UI's open and close states. Use the motion library (`m` components from `motion/react`, inside `LazyMotion`) only for layout movement: a highlight sliding between items (`layoutId`), height animating to `auto` (`AnimatedHeight`), and items entering or leaving a list (`AnimatePresence`).
- **Pages.** The shell never animates. `PageEnter` fades the page content in only when the path changes, never on the first load, a save or a reload.
- **Charts.** Draw in once on first appear, in 400ms or less. Tween data updates in 300ms or less. Data that refreshes in the background (polling, live updates) changes without animation.
- Don't animate frequent, keyboard-driven actions such as the command palette, and don't stagger entrances on every page.

## IMPORTANT: Small commits

The owner reviews every commit on its own. Keep each one small and about one thing, so it can be read in a couple of minutes. This applies to every agent that commits, including the Factory builder, and is stricter than a generic "commit each coherent step".

- One concern per commit: one migration with its model, one enum, one repository, one action, one request, one controller with its route, one page or component. Commit each test with the code it covers.
- Aim for at most about 5 files and about 200 changed lines per commit. When a step is bigger, split it by layer or by behaviour, never into commits that break the build.
- Build a feature bottom-up as a sequence: schema, then models and enums, then repositories, then actions, then requests and controllers, then the frontend. Each commit leaves the checks passing.
- Keep generated output separate: regenerated Wayfinder routes and actions, formatter-only changes and lockfile updates go in their own commit, labelled as such.
- Use conventional one-line messages (`feat:`, `fix:`, `test:`, `docs:`, `chore:`) that say what the commit adds, for example `feat: add the plans table and Plan model`.
- Never bundle a whole feature, or unrelated fixes, into one commit.

## Software factory

`/factory <description>` (`$factory <description>` in Codex) creates a local
job and builds it through intake → spec → build → gate → review → documentation
→ approval, one PR per job. Confirmed review fixes pass through a teacher that
records reusable lessons in `Knowledge/`; spec, build and review read relevant
lessons first. `/factory run` resumes the local queue. Optional
`/factory mitarbo <task-key>` reads one task through a connected Mitarbo tool.
State is in `factory/jobs/` (gitignored). Checks:
`factory/checks.sh`. Dashboard: `cd factory && python3 -m http.server`, then
open http://localhost:8000/dashboard.html.

Each job works in its own git worktree at `.worktrees/<job-id>` (gitignored).
A tool run from the repository root that doesn't read `.gitignore` sees those
copies too; exclude `.worktrees/` in its config.

- What the project is for: a multi-tenant Laravel SaaS starter (Mitarbo's base). A central platform manages tenant companies, each with its own database schema and domains; it runs hosted (managed deployment) or self-hosted.
- Security hot spots: tenant isolation (stancl/tenancy, central versus tenant routes and connections, the central-host gate in `IsTenantManagementAvailableAction`), Fortify authentication with 2FA and passkeys, spatie permissions, plan and feature enforcement and billing (see `docs/plans/2026-11-26-plans-and-features-design.md`; a read-only tenant is refused every POST, PUT and PATCH web route unless its name is in `billing.read_only_exempt` in `config/billing.php` or it carries `feature:{key}` for a default-plan feature, so a new authentication, billing or data-export route must add its name there), S3 uploads under per-tenant prefixes, and any query or HTML built from user input.
- Design system: tokens in `resources/css/app.css` (colour, density, motion); the Base UI kit in `resources/js/components/ui/`; the RailShell in `resources/js/components/shells/`; navigation in `resources/js/lib/navigation.ts`.
- Users: central platform staff on the central domain; tenant users (company admins and members) on tenant domains; one organisation's admins and members on a self-hosted install.
- A person must always see: central-database migrations, changes to tenant isolation, authentication or permissions, plan, feature or billing enforcement, CI or `.gitignore` changes, and new dependencies.

### Working in a job worktree

- **Setup.** `factory/checks.sh` installs everything a fresh worktree needs (Composer, npm, `.env`, the Wayfinder files generated by the build). To set up by hand: `composer install`, `npm ci`, `cp .env.example .env`, `php artisan key:generate`, `npm run build`.
- **Never touch the owner's database.** Flock serves every worktree at `https://<branch>.multi-tenant-laravel.test` and gives it a `.env` pointing at the owner's main Postgres database. Never run `migrate`, `db:seed`, `tinker` or any other database-writing command with that `.env`. Run the app through `factory/serve.sh`, and pass `--env=factory` to every other artisan command that touches the database: it uses the job's own throwaway Postgres database instead.
- **Dev server.** `bash factory/serve.sh <port>` writes `.env.factory`, recreates the job's own Postgres database `factory_<job-id>` on the local server (dropping the per-tenant roles tenancy created in the previous one), migrates and seeds it, and serves on that port; `--keep` serves the existing database, and `bash factory/serve.sh --drop` removes it. `bash factory/drop-databases.sh` (from the main checkout) drops the databases of jobs whose worktree is gone. Tests still run on SQLite in memory (`phpunit.xml`). Central app: `http://localhost:<port>`. Seeded tenants: `http://tenant1.localhost:<port>` and `http://tenant2.localhost:<port>`. Logins: `central@mail.test`, `tenant1@mail.test`, `tenant2@mail.test`, all with `Password1#`.
- **Validation.** `bash factory/checks.sh` runs the full gate: build, `npm run check`, `npm run types:check`, Pint, PHPStan, PHP Insights (100 in every category), and the Pest suite. While building, run the focused tests for what changed: `php artisan test --compact <paths>`.
- **Browser checks** use `agent-browser`, with a session named after the job and the agent's port, against the `factory/serve.sh` server. Don't use editor or environment preview tools: other jobs share their tabs.
