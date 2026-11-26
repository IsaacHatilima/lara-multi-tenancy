# Plans (Shipped)

Created: 26.11.2026, Modified: 26.11.2026

## Expected
A managed deployment stores its plans, features and tenant subscriptions in the central database, and ships with three public plans: Free (the default), Basic and Pro, none of which includes any features yet. Run `php artisan features:sync` on every deploy to keep the features list in step with the code. It only adds features and reports any that the code no longer knows.

Each tenant's features are worked out from the plan of its current subscription, or from the default Free plan when it has none or its subscription was canceled or ended. The result includes each feature's limit, the plans that would unlock a missing feature, and whether the tenant is read-only. It is cached for up to ten minutes, and the cache is cleared as soon as a subscription, plan or plan feature changes.

On a tenant's domain, actions the company's plan doesn't include are refused: users can still open and delete what they have, but can't create or change it. When a company's payment is overdue past its grace period, its account becomes read-only, and every user sees a notice explaining why. Signing in, passwords, two-factor authentication, passkeys and closing an account keep working, and saving anything else is refused with a message. Signed-in users' browsers receive the tenant's features with every page. Staff on the central host and self-hosted installs are never limited.

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
- **Read-only and past due.** A read-only subscription keeps its plan's features and marks the tenant read-only. A past-due subscription keeps its plan's features and is not read-only, so it can still write. The checks answer the same either way. What a read-only tenant can't do is described under "Read-only accounts".
- **Every feature is listed.** The result has one entry for every feature row in the database, including keys the code doesn't declare. Each entry says whether the feature is enabled, its limit (empty means unlimited), its unit (megabytes or items, empty for undeclared keys), and the codes of the public, active, hosted plans that include it, in sort order, so an upgrade prompt can name them. Custom plans are never listed.
- **Checks.** A feature is enabled only when the plan includes it. A limit check passes when the amount is within the limit, always passes for an unlimited feature, and never passes for a feature the plan doesn't include or a key that doesn't exist. Amounts are in the feature's own unit; converting bytes is left to the first feature that needs it.
- **Version.** Every result carries a short eight-character version computed from its content: the plan, the subscription state, the read-only flag and every entry. Equal results have the same version and any difference changes it, so a browser can tell that its features changed.

### Caching and clearing
- Each tenant's result is kept for ten minutes in its own entry in a dedicated cache store that copies the application's cache store and is never scoped by tenancy. An entry built while serving a tenant is therefore the same entry that staff actions on the central host clear. Within one request or queued job the result is also remembered, so it is read once.
- Creating, changing or deleting a subscription clears that tenant's entry. Changing or deleting a plan, or adding, changing or removing one of its features (directly or through the plan's features), clears every tenant currently on that plan. When the hosted default plan is involved, or a plan becomes or stops being the default, tenants without a current subscription are cleared too. Clearing waits for the surrounding transaction to commit, and a rolled-back change clears nothing. The next lookup rebuilds the entry.

### Refusing actions a plan doesn't include
A route can be marked with the feature it needs. On a tenant's domain, creating or changing through that route (POST, PUT or PATCH) is refused when the tenant's plan doesn't include the feature, and also when no feature with that key exists. Opening the page, and deleting, are never refused by this rule. The rule checks only that the plan includes the feature, not its limit, so a limit of 1 doesn't stop a second item. On the central host of a managed deployment and on self-hosted installs nothing is refused. No product route is marked yet, because the code declares no features.

A refusal looks like this to each kind of caller:
- **JSON callers** get a 403 with exactly `code` (`feature_unavailable`), `feature` (the key, or null for a read-only refusal) and `message`.
- **Pages in the browser** are sent back to the page they came from, with an error named `feature_unavailable` on the form and an error toast showing the message. A refused PUT or PATCH is answered with a 303 redirect so the browser follows it as a plain page load. The page it returns to already carries the current features.
- **Any other request**, such as a plain form post, gets a plain 403 with the same message.

The messages are "Your plan doesn't include this feature." and, for read-only accounts, "This account is read-only because its payment is overdue. You can still view and delete existing data."

### Read-only accounts
When a tenant is read-only, every POST, PUT and PATCH on its domain is refused with the read-only refusal above, whether or not the route has a name, including routes added later. Reading and deleting are never refused, so users can still view data, remove a passkey, turn off two-factor authentication and close their account.

These exceptions keep working while read-only:
- **Authentication and security settings.** Signing in and out, password reset email, password change, email verification resend, password confirmation, two-factor sign-in, passkey sign-in and confirmation, registration, changing the password, enabling and confirming two-factor authentication, regenerating recovery codes, and adding a passkey. They are listed by route name in `billing.read_only_exempt` in `config/billing.php`.
- **Features on the default plan.** A route marked with a feature that the hosted default plan includes still accepts writes, because the tenant keeps what the free plan gives. This check runs only for a read-only tenant, so other tenants add no default-plan query.
- **Live updates.** Channel authorisation keeps working.

Saving a profile (name and email) is not exempt: it is refused while read-only. A new authentication, billing or data-export route must add its name to `billing.read_only_exempt`, or it is refused for read-only tenants. A past-due tenant isn't read-only, and nothing in the product moves a tenant into or out of read-only yet: that status is set in the database.

`config/billing.php` also holds `grace_days` (7 by default, `BILLING_GRACE_DAYS`). Nothing reads it yet.

### What the browser receives
- **Features payload.** Every page a signed-in user opens carries a `features` payload: its version, whether the host is limited, the plan's name and code or nothing, the subscription's status and its trial and grace end dates (ISO 8601) or nothing, whether the tenant is read-only, and one entry per feature with whether it is enabled, its limit, its unit (megabytes, count or nothing) and the codes of the plans that unlock it. Users of the central host and of self-hosted installs get an unlimited payload with no plan or subscription. Guests, including on a tenant's sign-in page, get no `features` at all, so a company's plan and payment state can't be read from its public pages.
- **Version header.** Every response to a signed-in user, including refusals and redirects, carries an `X-Features-Version` header equal to the payload's version. Guests get none. Nothing in the browser reads the header yet. The browser gets fresh features with each page it loads.
- **`useFeature(key)`.** A hook returns one feature's entry. For a key the payload doesn't list, it reports the feature as enabled and unlimited when the host isn't limited, and as disabled when it is or when there is no payload.
- **Read-only notice.** While a tenant is read-only, every page in the app layout (dashboard, settings and tenants) shows a notice with the read-only message. It can't be dismissed. Nothing else on the page is disabled or hidden, so a refused save shows the error toast. Sign-in pages and the signed-in email verification and password confirmation pages don't show it.

### Limits
- No feature is declared yet, and no existing route is marked with one, so today only read-only mode refuses anything. The payload carries the plans that unlock a feature, but no screen shows an upgrade prompt, a plan name or a limit, apart from the read-only notice.
- Limits are never checked when a request is refused or allowed. A feature's limit is only reported.
- Nothing moves trials to ended or past-due subscriptions to read-only on a schedule, and there is no screen, route or API to create, edit or assign plans or subscriptions. They are managed only through the seeder and the database.
- Read-only mode applies only to tenants on a managed deployment. A self-hosted install is never read-only.
- The refusal rules cover the routes tenants can reach on their own domain. Developer-only tooling routes that run without a tenant aren't covered.
- Changing one plan doesn't clear tenants on other plans, so their lists of plans that unlock a feature can be up to ten minutes old. A change that bypasses the models, such as a bulk database update, clears nothing until an entry expires. Moving a subscription to a different tenant clears only the new tenant's entry.
- A rebuild racing a clear is bounded only by the ten-minute lifetime.
- Plans have no features until later releases map them, so every tenant's result currently has no enabled features unless a feature row and mapping are added by hand.
