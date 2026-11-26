# Plans (Shipped)

Created: 26.11.2026, Modified: 26.11.2026

## Expected
A managed deployment stores its plans, features and tenant subscriptions in the central database, and ships with three public plans: Free (the default), Basic and Pro, none of which includes any features yet. Run `php artisan features:sync` on every deploy to keep the features list in step with the code. It only adds features and reports any that the code no longer knows.

Each tenant's features are worked out from the plan of its current subscription, or from the default Free plan when it has none or its subscription was canceled or ended. The result includes each feature's limit, the plans that would unlock a missing feature, and whether the tenant is read-only. It is cached for up to ten minutes, and the cache is cleared as soon as a subscription, plan or plan feature changes. Staff on the central host and self-hosted installs are never limited. Nothing enforces a tenant's features yet: that arrives in a later release.

## Actual
### What is stored
Four tables live in the central database only. They are never created in a tenant's own database, and the models that read them always use the central connection, even while a tenant is active.

- **Plans.** A plan has a code, name, optional description, status (active or inactive), deployment (hosted or self-hosted), billing interval (monthly or yearly), price in minor units of a three-letter currency, trial days, a sort order, and public and default flags. A plan can belong to one tenant, which makes it a custom plan. Public plans have no tenant.
- **Features.** A feature is a unique key. A plan includes a feature through a mapping that carries an optional limit. An empty limit means unlimited. A feature's limit is counted in megabytes or in items.
- **Subscriptions.** A subscription links a tenant to a plan and has a status: trialing, active, past due, read-only, canceled or ended. It also records its trial end, current period end, grace end, cancellation and end dates, all optional.

### Rules the database enforces
- A plan code can have only one active plan. Any number of inactive versions can share that code.
- Only one hosted plan can be the default. A self-hosted plan can be marked default beside it.
- A feature can be mapped to a plan only once.
- A tenant can have only one current subscription, meaning trialing, active, past due or read-only. Canceled and ended subscriptions are history, and a tenant can keep any number of them beside its current one.
- A plan or feature that is still referenced by a subscription or a mapping can't be deleted. Deleting a plan does remove its feature mappings.

These rules hold on both SQLite and Postgres.

### Default plans
`php artisan db:seed --force` creates three plans on a managed deployment, in every environment including production:

| Plan | Price | Default |
|---|---|---|
| Free | 0.00 EUR a month | Yes |
| Basic | 19.00 EUR a month | No |
| Pro | 49.00 EUR a month | No |

All three are active, public, hosted, monthly and have no trial. Their prices are placeholders. They include no features. Reseeding is safe: a plan whose code already exists, active or not, is left as it is, so renamed or repriced plans are not reset. On a self-hosted install (`mitarbo.managed_deployment` set to false) the seeder creates no plans, although the tables still exist. Seeding in production adds only these plans, never demo users or tenants.

### Keeping features in step with the code
`php artisan features:sync` adds a feature row for every feature the code declares and doesn't yet have in the database. It never deletes. A stored feature the code no longer declares keeps its row and its plan mappings, and the command prints its key so an operator can see it. Running it repeatedly is safe, including when two deploys run it at once. The code declares no features yet, so today the command adds nothing. The deploy sequence is `php artisan migrate --force && php artisan tenants:migrate && php artisan features:sync`.

### Subscription statuses
The subscription status set is now trialing, active, past due, read-only, canceled and ended. The stored spelling is `canceled`, and its label stays "Cancelled". The tenant details page shows read-only like past due, and canceled and ended like cancelled. Its appearance is unchanged. That page still shows sample billing figures, which only use the active and past-due statuses.

### How a tenant's features are worked out
- **Where the answer comes from.** The service first decides the mode. On the central host of a managed deployment, and on every host of a self-hosted install (`mitarbo.managed_deployment` set to false), nothing is limited: every feature is enabled and unlimited, no plan is reported, and the database and the cache are not read. On a tenant's own domain, whether a subdomain or a custom domain, the tenant always gets its own result and never the unlimited one. Asking on a tenant domain without saying which tenant is an error rather than a guess.
- **Which plan.** A tenant is on the plan of its current subscription (trialing, active, past due or read-only), whatever that plan's status. A retired plan version or a tenant's custom plan resolves to its own features, not to another plan with the same code. A tenant with no current subscription, including one whose subscriptions are all canceled or ended, gets the hosted default plan with no subscription. If no hosted default plan exists, the tenant gets no plan and every feature is off.
- **Stored status decides.** Only the stored subscription status counts. Trial and grace end dates are reported but never compared with the clock, so a trialing subscription whose trial date has passed stays on its plan until its status is changed.
- **Read-only and past due.** A read-only subscription keeps its plan's features and marks the tenant read-only. A past-due subscription keeps its plan's features and is not read-only. The checks answer the same either way. Blocking writes for read-only tenants is not built yet.
- **Every feature is listed.** The result has one entry for every feature row in the database, including keys the code doesn't declare. Each entry says whether the feature is enabled, its limit (empty means unlimited), its unit (megabytes or items, empty for undeclared keys), and the codes of the public, active, hosted plans that include it, in sort order, so an upgrade prompt can name them. Custom plans are never listed.
- **Checks.** A feature is enabled only when the plan includes it. A limit check passes when the amount is within the limit, always passes for an unlimited feature, and never passes for a feature the plan doesn't include or a key that doesn't exist. Amounts are in the feature's own unit; converting bytes is left to the first feature that needs it.
- **Version.** Every result carries a short eight-character version computed from its content: the plan, the subscription state, the read-only flag and every entry. Equal results have the same version and any difference changes it, so a later release can use it to tell a browser that its features changed.

### Caching and clearing
- Each tenant's result is kept for ten minutes in its own entry in a dedicated cache store that copies the application's cache store and is never scoped by tenancy. An entry built while serving a tenant is therefore the same entry that staff actions on the central host clear. Within one request or queued job the result is also remembered, so it is read once.
- Creating, changing or deleting a subscription clears that tenant's entry. Changing or deleting a plan, or adding, changing or removing one of its features (directly or through the plan's features), clears every tenant currently on that plan. When the hosted default plan is involved, or a plan becomes or stops being the default, tenants without a current subscription are cleared too. Clearing waits for the surrounding transaction to commit, and a rolled-back change clears nothing. The next lookup rebuilds the entry.

### Limits
- Nothing calls this service yet. No feature is enforced, there is no read-only blocking, no feature payload reaches the browser, and no screen shows a tenant's plan or limits.
- Changing one plan doesn't clear tenants on other plans, so their lists of plans that unlock a feature can be up to ten minutes old. A change that bypasses the models, such as a bulk database update, clears nothing until an entry expires. Moving a subscription to a different tenant clears only the new tenant's entry.
- A rebuild racing a clear is bounded only by the ten-minute lifetime.
- There is no screen, route or API to create, edit or assign plans or subscriptions. They are managed only through the seeder and the database.
- Plans have no features until later releases map them, so every tenant's result currently has no enabled features unless a feature row and mapping are added by hand.
