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

## Project conventions

These override the installed skills where they differ.

**Repositories**

- Each persistence operation is its own small class with one `handle()`, named for what it does and without a `Repository` suffix (`app/Repositories/Users/CreateUser.php`, `UserEmailTaken.php`). The domain's entry file, such as `app/Repositories/Users/UserRepository.php`, composes those classes and exposes one method per operation (`create`, `emailTaken`) that delegates to `handle()`. Actions inject only the entry repository, so they need one import and one constructor parameter. Put a new query in a new operation class and add its method to the entry repository.
- Write models by assigning each property from the Data object, then `save()`. Don't use `fill()`, `forceFill()`, `create([...])` or `update([...])` with an attribute array.
- When create and update write the same columns, use one `save(Data $data, ?Model $model = null)` that starts with `$model ??= new Model();`. When they differ, as for users, write separate `create` and `update` methods.
- A query that leaves a record out takes that record's id under a name that says what it is, such as `?string $userId = null`. Don't pass the model or call the parameter `$except`.
- Return the saved model without `refresh()`. Ids, UUIDs and timestamps are set in PHP. Refresh only when the database fills in a value the caller reads, such as a column default or a trigger, and prefer a model `$attributes` default over that.
- Use plain Eloquent for simple lookups. Use `spatie/laravel-query-builder` for tables and lists with search, filters or sorts. Pass the validated FormRequest to `QueryBuilder::for($query, $request)` and allowlist every filter. This is the one case where a repository takes a request; never call `request()` in a repository.

**Models**

- Every model uses `LogsActivity` unless told otherwise. Its `getActivitylogOptions()` returns `LogOptions::defaults()->logFillable()->logOnlyDirty()->dontLogEmptyChanges()`, or an explicit `logOnly([...])` when the fillable list would include a secret such as a password or token.

**Keys**

- Users and other entity tables are keyed by UUID (`HasUuids`, `$table->uuid('id')->primary()`), and foreign keys to them use `foreignUuid()`.

===

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

=== inertiaui/notifications/core rules ===

## Inertia Notifications

- `inertiaui/notifications` provides toasts for Laravel + Inertia.js apps, flashed from PHP with `InertiaUI\Notifications\Toast` or shown from the browser with `toast()`, and a notification center that stores Laravel notifications sent through the `inbox` channel. Supports React and Vue.
- Always activate the `inertia-notifications-development` skill when code uses the package, such as `Toast`, `InboxMessage`, `HasInbox`, `NotificationCenter`, `Toaster`, or `NotificationPreferences`. Code uses the package when it:
    - uses anything in the `InertiaUI\Notifications` namespace, or imports from `@inertiaui/notifications-vue` or `@inertiaui/notifications-react`;
    - or uses an entry point that needs no import: the `->toast()` and `->withToasts()` response macros, `Route::inertiaNotifications()`, the `assertToast()` and `assertNoToasts()` test response macros, the `inbox` notification channel, `config/inertia-notifications.php`, the `inertia-notifications:*` Artisan commands, or `make:inbox-notification`.
- Flash toasts with `Toast::success('...')->flash()` or `back()->toast(...)`. Do not use `Inertia::flash('toast', ...)` or `session()->flash()` for toasts in apps that use this package.

=== spatie/laravel-activitylog/core rules ===

## Activitylog

- `spatie/laravel-activitylog` logs model events and manual activities to a database table, with subjects, causers, tracked attribute changes, and custom properties.
- Always activate the `activitylog-development` skill when working with activity logging or any code that uses the `LogsActivity`, `CausesActivity`, or `HasActivity` traits, `LogOptions`, the `Activity` model, or the `activity()` helper.
- If the skill is unavailable, read `vendor/spatie/laravel-activitylog/resources/boost/skills/activitylog-development/SKILL.md` before making changes.

</laravel-boost-guidelines>
