# Plans and features — design

Status: design agreed with the product owner on 2026-11-26; not yet built.

## Intent

Tenants pay for a plan, and the plan decides which features they can use and how far (for example, a 5 MB attachment cap on Free, 15 MB on Basic, 50 MB plus video and audio on Pro). The same entitlement data drives the UI on web (Inertia), and later mobile and desktop (API), and is enforced on the server for every request. It is cached so feature checks don't query the database on every request, without letting a stale copy grant access.

## Deployment modes

Plans exist only to bill tenants of the hosted service. Everything in this design hangs off the existing checks in `IsTenantManagementAvailableAction`:

| Where the request runs                                                           | Plans, subscriptions, admin screen                                                                      | Feature checks                                                                                             |
| -------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- |
| Managed deployment (`mitarbo.managed_deployment` true), central host (`APP_URL`) | The admin screen lives here, behind the same gate as tenant management plus `plans.manage`              | Never gated: the platform owner's staff have every feature, unlimited                                      |
| Managed deployment, a tenant's domain                                            | Not reachable (404, like tenant management)                                                             | Gated by the tenant's subscription and plan                                                                |
| Self-hosted install (`mitarbo.managed_deployment` false)                         | Don't exist: no tenants, plans, subscriptions or billing routes. One organisation with its own database | The central feature set minus platform features; each product feature is locked until activated with a key |

The entitlements service decides the mode first: on the central host it returns every feature enabled and unlimited without reading plans or the cache.

## Goals and non-goals

Goals

- Plans and their features are database data that central staff manage through an admin screen, without a deploy.
- Features can be on/off or carry a numeric limit; an empty limit means unlimited.
- One feature payload drives the UI on every client and is enforced on the server (routes and individual actions).
- Clients resync automatically when their copy is out of date.
- Lapsed payments put a tenant into read-only mode without losing data, so they can export and leave.
- Plans already carry everything Stripe needs, so connecting a payment provider later adds integration, not reshaping.

Non-goals for now

- Taking payments (Stripe or another provider), payment history and invoices.
- The mobile/desktop API and its token authentication.
- Per-tenant feature overrides (custom plans cover deals instead).
- Usage-based quotas other than per-user storage, and storage tracking until the files feature exists.

Failure outcomes to avoid

- A tenant keeps paid access for minutes after a downgrade or lapse because a cache is stale.
- A billing change breaks a company's live setup (for example, their custom domain stops resolving) or looks like data loss.
- Editing one customer's custom plan changes another customer's.
- A price change silently changes what existing subscribers pay.

## Decisions (made by the product owner)

1. A `Feature` enum lists the feature keys; a `features` table mirrors it. Plans and plan–feature mappings are database data.
2. Every plan stores its complete feature list explicitly; "start from" copies another plan's features when creating one. No plan inheritance.
3. Features are on/off or limited. A feature that takes a limit has one number per plan in `plan_feature.limit`; its unit (MB, count) is defined by the enum through a `LimitUnit` enum. An empty limit means unlimited. No row means the plan doesn't include the feature.
4. Storage is limited per user (for example, 5 GB) and counts the files a user keeps on the platform, not message attachments. Message attachments have only a per-message size limit.
5. Tenants have plans through a `subscriptions` table (history, status, trial, grace). A payment provider is added later.
6. Caching: the resolved features live in Redis. Model observers clear the affected tenants' entries after commit; the next request rebuilds them; a TTL of about 10 minutes is the safety net. Every response carries a features version so web and devices resync; a blocked request returns `feature_unavailable` with feedback and the client resyncs.
7. Losing a feature or going over a limit leaves existing things readable and deletable; creating and updating (POST, PUT, PATCH) for that feature are blocked. A live custom domain keeps resolving but can't be added to or edited.
8. When a payment fails and the 7-day grace period expires, the tenant keeps its plan but becomes read-only: POST, PUT and PATCH are blocked except for authentication, billing, data export, users' own security settings, and the Free plan's features. Reads and deletes stay allowed.
9. A tenant with no current subscription gets the default plan (Free).
10. The mobile/desktop API comes later. Now: the features service, version stamp and `feature_unavailable` error in a shape the API will reuse, and the web through Inertia.
11. A central admin screen to manage plans, features, limits, custom plans and subscriptions is built now. Rollout starts with the Free features; premium features follow.
12. Managing plans requires the `plans.manage` permission (spatie, central guard) on top of the existing tenant-management gate: a managed deployment, on the central host. The seeder grants it to the central demo user.
13. Plans and billing apply only to tenants of a managed deployment. The central host is never gated by features, so the platform owner doesn't pay for its own features.
14. A self-hosted install has no tenants: one organisation with its own database. It gets the central feature set minus platform features (tenant management, plans and billing). The `Feature` enum marks which features are platform-only.
15. On a self-hosted install, product features are locked until activated with a key. Activation is online: the install verifies its key against a licence API on the managed deployment and pulls its plan and features from there.

16. A new self-hosted install gets a 7-day free trial. After the trial, or whenever the key can't be verified (licence API unreachable, key expired or revoked), it gets `license_grace_period_days` (14), then becomes read-only: sign-in, reads, deletes, data export and entering a key keep working; nothing is deleted.

17. Self-hosted buyers either buy the standard self-hosted package (the shipped product features) or arrange a custom plan by contact. Plans carry `deployment`: `hosted` | `self_hosted`; there is one standard self-hosted plan plus per-customer custom ones. A central `licences` table links a key (stored hashed) to an organisation, its plan, a status and an expiry; licences are issued from the same admin screen, and the licence API returns that plan's features.

18. A self-hosted licence gets a feature only if its plan includes it. Adding a feature to the standard self-hosted plan reaches every active licence on it; custom plans get it only if it is added to them.

My call: self-hosted ships as an image, so feature code arrives with the image and entitlements arrive from the licence API. An install running an older image ignores feature keys its `Feature` enum doesn't know, and picks them up after updating the image.

My call: the self-hosted server makes that call, never the browser, so the key stays server-side. It caches the result in its own Redis and serves the same payload, version stamp and `feature_unavailable` errors, so the frontend works identically on both deployment modes. 13. No per-tenant overrides. Plans are fixed public plans plus custom plans that customers arrange by contact. 14. Each custom plan belongs to exactly one tenant. Any plan, including another tenant's custom plan, can be copied as the starting point for a new one. 15. Plans carry their price (minor units), currency and billing interval now. The app will be the source of truth and push to Stripe later. 16. Changing a plan's price, currency or interval retires the plan (`inactive`) and creates a new `active` plan; existing subscribers stay on the old one until moved. Feature, limit, name and description changes edit the plan in place, tracked by the activity log and `updated_by`. 17. Unavailable features are hidden. Upgrade prompts appear in context, inside related features the tenant has (for example, "Free includes 1 user — upgrade to Basic or Pro to add team members"), naming the plans that unlock them. 18. Within a tenant, the `admin` role manages the plan and billing; the tenant's creator becomes admin. Others see "Ask your admin to upgrade". 19. Plans may offer trials (`trial_days`, default 0). A trial that ends unpaid falls back to Free, not read-only. 20. The grace period is 7 days, configurable (`billing.grace_days`).

## Approaches considered

- **Laravel Pennant.** Built for feature flags and gradual rollouts; stores one resolved value per scope. Plan-based entitlements would need every stored value purged on every plan or subscription change, and limits fit awkwardly. Rejected.
- **A small entitlements service of our own (chosen).** Resolves a tenant's features from subscription → plan → `plan_feature`, caches the result per tenant, and exposes checks to middleware, requests, actions and Inertia. Fits on/off plus limits, the read-only mode and the clear-on-change cache directly.

## Data model

All plan and billing tables are central (`database/migrations/central/`). Entity tables use UUID keys, per the project's migration rules.

`plans`

- `id`, `code` (stable across versions, for example `basic`), `name`, `description`
- `status`: `active` | `inactive` (one active plan per `code`, enforced by a partial unique index)
- `deployment`: `hosted` | `self_hosted`
- `is_public`, `is_default` (exactly one hosted default, which must be public and active)
- `tenant_id` nullable: set for custom plans, which only that tenant may be assigned
- `price_minor` (integer), `currency` (ISO 4217), `interval`: `month` | `year`, `trial_days` (default 0)
- `sort_order`, `created_by`, `updated_by`, timestamps

`features`

- `id`, `key` (unique, a `Feature` enum value), timestamps. Synced from the enum by an artisan command run on deploy.

`plan_feature`

- `id`, `plan_id`, `feature_id`, `limit` nullable big integer, `updated_by`, timestamps
- unique (`plan_id`, `feature_id`)

`subscriptions`

- `id`, `tenant_id`, `plan_id`
- `status`: `trialing` | `active` | `past_due` (in grace) | `read_only` (grace expired) | `canceled` | `ended`
- `trial_ends_at`, `current_period_ends_at`, `grace_ends_at`, `canceled_at`, `ends_at`, timestamps
- at most one current subscription per tenant (partial unique index over the non-final statuses)

`licences` (managed deployment only)

- `id`, `key_hash` (the plain key is shown once when issued), `organisation_name`, `contact_email`, `plan_id` (a `self_hosted` plan)
- `status`: `active` | `expired` | `revoked`, `expires_at`, `last_verified_at`, `created_by`, `updated_by`, timestamps

Self-hosted install (its own database)

- the entered key (encrypted), the last verified licence response, `trial_ends_at`, `last_verified_at`. Kept in its database and cached in its Redis.

Tenant databases (when the files feature is built)

- per-user storage usage in bytes, updated on upload and delete.

Enums

- `Feature`: the feature keys, with `limitUnit(): ?LimitUnit` and the context where its upgrade prompt belongs. It starts with no product cases: each case is added when its feature is built, after the gate exists.
- `LimitUnit`: `Megabytes`, `Count`, with labels. Size limits are stored in MB and shown in GB where larger.
- `PlanStatus`, `BillingInterval`, `SubscriptionStatus`.

## Resolution and caching

- `GetTenantFeaturesAction` resolves the tenant's current subscription (or the default plan), reads its `plan_feature` rows through a repository, and builds the feature set: each feature's `enabled`, `limit`, `unit`, and for locked features `availableOn` (the cheapest public active plans that include it). It also sets `readOnly` and the subscription state.
- The set is cached in Redis under a central key, `tenant-features:{tenantId}`, independent of the tenancy cache prefix, with a TTL of about 10 minutes. Within a request it is memoised.
- `version` is a short hash of the feature set.
- Observers on `Plan`, `PlanFeature` and `Subscription` clear the affected entries after the transaction commits: one tenant for a subscription change, every tenant on that plan for a plan or mapping change. Entries rebuild lazily on each tenant's next request.
- A daily scheduled command moves trials that ended unpaid to `ended` (so Free applies) and `past_due` subscriptions past `grace_ends_at` to `read_only`, clearing the affected caches.

## Enforcement

- Route middleware `feature:{key}` refuses a whole area when the feature is off.
- A middleware on tenant routes enforces read-only mode: it refuses POST, PUT and PATCH unless the route is in the exemption list (`billing.read_only_exempt` config: authentication, billing, data export, security settings) or belongs to a feature on the default (Free) plan.
- Losing a feature (decision 7) uses the same rule per feature: its write routes are refused, its reads and deletes are not.
- Limits are checked where the request is validated or the action runs, through the same service: file size against the attachment limit, video and audio against their features, remaining seats against the team member limit, storage usage plus the new file against the per-user quota.
- A refusal is a 403 with `{ "code": "feature_unavailable", "feature": "<key>", "message": "…" }` for JSON. Inertia requests get the same as a validation-style error with a toast, and the next props carry the fresh feature set.
- Every response carries `X-Features-Version`.
- The central host is never gated by features (see Deployment modes); the feature middleware and limit checks pass straight through there.

## Payload

Shared with every Inertia page as `features`, and later returned by the API in the same shape:

```json
{
    "version": "a1f3c9",
    "plan": { "name": "Basic", "code": "basic" },
    "subscription": {
        "status": "active",
        "trialEndsAt": null,
        "graceEndsAt": null
    },
    "readOnly": false,
    "items": {
        "messaging": { "enabled": true },
        "attachments": { "enabled": true, "limit": 15, "unit": "megabytes" },
        "video_attachments": { "enabled": false, "availableOn": ["pro"] }
    }
}
```

`canManageBilling` is added for the current user from the tenant `admin` role. The frontend gets a `useFeature(key)` hook and a typed payload. Upgrade prompts read `availableOn` and show "Ask your admin to upgrade" to non-admins.

## Admin screen (managed deployment, central host, `plans.manage`)

- Plans list: public and custom plans (custom ones show their tenant), with status, price and interval.
- Create a plan, optionally starting from a copy of any plan; create a custom plan from a tenant's page.
- Edit a plan: name, description, features and limits in place; changing price, currency, interval or trial days creates a new active version and retires the old one.
- On a tenant's page: see the current subscription and history; assign a plan (public, or that tenant's custom plan), set status, trial or grace dates. Until a payment provider exists, staff set `past_due` by hand.

## Phasing

1. The gate: schema, enums, seeders (Free as the default plan; Basic and Pro defined, with no features yet), features service, cache and observers, middleware, Inertia payload, tenant `admin` role, read-only mode, the scheduled expiry command, and the admin screen. No product feature is gated yet, because none exists; tests exercise the gate with a test-only feature.
2. The Free features (messaging, MCP connection and the rest), each built behind the gate and added to the Free plan as it ships.
3. Premium features and their limits; per-user storage tracking when the files feature is built.
4. Self-hosted licensing: `self_hosted` plans and licences in the admin screen, the licence API on the managed deployment, and on self-hosted installs the key entry page, the 7-day trial, the 14-day grace and read-only mode. (Ordering of this phase relative to the payment provider is my call.)
5. Payment provider: push plans to Stripe (Product plus Price, a new Price on price changes), webhooks for payment results and subscription status, payment history.
6. Mobile/desktop API: token authentication, versioned routes, a features endpoint returning the same payload.

## Assumptions (my calls, not decisions)

- Plan changes made by staff take effect immediately; upgrade and downgrade timing rules (now versus end of period) come with the payment provider.
- Grace and read-only notices go to tenant admins by email and an in-app banner.
- The read-only exemption list is route-name based, in config.
- "Read" for a custom domain means it keeps resolving.

## Open questions

- The Free feature list and its limits are decided as each Free feature is built (phase 2), not up front.
- Prices, intervals and feature lists for Basic and Pro when premium features arrive.

## Success criteria

- A tenant on Free sees only Free features; switching them to Basic in the admin screen shows Basic features on their next page load, with no deploy and no cache wait.
- A request to a feature the tenant lacks, made with a stale client copy, is refused with `feature_unavailable`, and the client resyncs.
- A tenant whose grace has expired can sign in, read, delete, export, manage billing and use Free features, and nothing else.
- Changing a plan's price leaves existing subscribers on their old plan and price.
- Editing one tenant's custom plan doesn't change any other tenant.
- Staff on the central host of a managed deployment can use every feature without a subscription.
- A self-hosted install shows no tenant, plan or billing screens; after its trial it unlocks exactly its licence plan's features, keeps them through 14 days without verification, then goes read-only.

Verification: feature tests for resolution (default plan, trial, grace, read-only), cache clearing on each observer path, both middleware and each limit check, the payload shape, and the admin screen's permission; a browser check that the UI shows and hides features and upgrade prompts per plan.
