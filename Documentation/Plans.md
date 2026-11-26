# Plans (Shipped)

Created: 26.11.2026, Modified: 26.11.2026

## Expected
A managed deployment stores its plans, features and tenant subscriptions in the central database, and ships with three public plans: Free (the default), Basic and Pro, none of which includes any features yet. Run `php artisan features:sync` on every deploy to keep the features list in step with the code. It only adds features and reports any that the code no longer knows. Nothing changes for tenants or staff yet: plans start deciding what a tenant can use in a later release.

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

### Limits
- Nothing reads this data at request time. A tenant's features are not resolved from its plan, no feature is enforced, and a tenant without a subscription is not yet treated as being on Free.
- There is no screen, route or API to create, edit or assign plans or subscriptions. They are managed only through the seeder and the database.
- There is no query for a tenant's current subscription.
- Plans have no features until later releases map them.
