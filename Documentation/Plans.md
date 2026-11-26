# Plans (Shipped)

Created: 26.11.2026, Modified: 27.11.2026

## Expected
Platform staff with the `plans.manage` permission can manage plans and features on the central host of a managed deployment. They can create, edit and delete plans and features, and on a plan's edit page choose which features the plan includes, each with an optional limit. Nothing checks these features yet: gating is built when the first real feature needs it.

## Actual
### What is stored
Three tables in the central database only:

- **Plans:** a name, a unique code and an optional description.
- **Features:** a unique key (lowercase letters, digits and underscores, starting with a letter, such as `messaging` or `project_management`), a name, an optional description and an optional limit unit (megabytes or count). The key is what code will check, so it can't be changed after the feature is created.
- **Plan features:** which features a plan includes, each with an optional limit. An empty limit means unlimited. A plan includes a feature at most once. Deleting a plan or a feature removes its mappings.

`php artisan db:seed --force` creates the Free, Basic and Pro plans, with no features, and the `plans.manage` permission on a managed deployment. Reseeding leaves existing plans as they are. A self-hosted install gets neither. The demo login `central@mail.test` holds the permission wherever demo data is seeded; on a real deployment an operator grants it by hand.

### The screens
"Plans" and "Features" sit in the Platform section of the navigation, at `/plans` and `/features`.

- **Who gets in.** On a tenant's domain or a self-hosted install every address answers "not found". On the central host a guest is sent to sign in, an unverified user to verify their email, and a user without `plans.manage` is refused with a 403. Every save checks the permission again.
- **Plans.** The list shows each plan's name, code and description by name. Creating a plan takes a name, code and description and opens its edit page. The edit page changes those fields and lists every feature with a checkbox; a ticked feature takes an optional limit (a whole number of zero or more), with its limit unit shown beside it. Saving stores the fields and the ticked features together, and removes every unticked one.
- **Features.** The list shows each feature's name, key and limit unit by name. Creating a feature takes a key, name, description and limit unit; editing changes everything but the key.
- **Deleting.** A plan or feature is deleted from its edit page, after a confirmation.
- Invalid input is refused with an error under its field, and nothing is saved.
