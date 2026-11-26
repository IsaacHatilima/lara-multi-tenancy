# A list collapsed into a keyed map needs a `distinct` rule and a test that sends a repeat

ID: keyed-list-input-needs-distinct-rule-and-test
Category: Validation and Input
Tags: validation, form-request, distinct, mapWithKeys, duplicate-keys, pest, dataset, regression-test, plans, features
Verified: 2026-11-26
Status: verified-on-branch

## Use when

A FormRequest accepts a list of rows that each carry an identifier (for example `features.*.key` with a `limit`), and the code behind it turns that list into a map keyed by the identifier, such as `collect()->mapWithKeys()`, `keyBy()` or `pluck(..., key)`. It also applies when you add a validation rule the spec does not name and need to decide whether it needs its own test.

## Problem

Two rows with the same key and different values are accepted by the request. The mapping step keeps only the last row for that key, so the earlier value is dropped with no error. In the plan editor this meant a feature listed twice with limits 1 and 2 would silently save limit 2.

## Cause

Collapsing a list into a map is lossy for repeated keys, and the loss happens after validation. `UpdatePlanData::fromRequest` builds `featureLimits` with `mapWithKeys` over `features`. Only the `distinct` rule on `features.*.key` in `UpdatePlanRequest::rules()` stops a repeat from reaching it. The builder added that rule for a good reason (a repeat makes the submitted limit ambiguous). The spec's rule list and its acceptance criterion 15 did not name it, so no test covered it. A change that dropped the rule would have passed every test.

## Solution

Keep the rule and add a named case to the existing invalid-edit dataset in `tests/Feature/Http/Plans/PlanControllerTest.php`. The case sends `test-reports` twice with different limits and expects the error on `features.1.key`. The reviewer preferred the test over removing the rule because the rule's justification was sound and the test was the smaller change.

## Prevention

- When a request feeds a list into `mapWithKeys` or `keyBy`, put `distinct` on the identifying field. Do not rely on the later step to reject repeats.
- Treat every rule in `rules()` as needing a case, including rules you added beyond the spec. Search the tests for the rule name. Zero hits means it is unguarded.
- Add the case to the existing named dataset for invalid input, so the failing field path (`features.1.key`) is asserted.
- Point the expected error at the second occurrence, since `distinct` flags the later duplicate.
- Make the repeated rows carry different values. Then the case also shows what would have been lost.

## Evidence

- Repository and PR: `IsaacHatilima/lara-multi-tenancy`, PR #17, branch `factory/005-edit-plans-their-features`.
- Original finding: `code-r1-1` (code reviewer, round 1). The reviewer confirmed it fixed in round 2.
- Fix commit and reviewed commit: `e7d2fc2c54401d45ecc9f39ec8e77aed591271fc`, "test: refuse a feature listed twice in a plan update". It adds one line to the dataset. The diff from `e9e82c9` is that line and nothing else.
- Code paths: `app/Http/Requests/Plans/UpdatePlanRequest.php` (the `distinct` rule), `app/Data/Plans/UpdatePlanData.php` (the `mapWithKeys` call), `tests/Feature/Http/Plans/PlanControllerTest.php` (the `feature listed twice` case).
- Checks: the focused run with `--filter` passed (1 test, 3 assertions), and the round 2 gate recorded a pass at that commit. The build log records that `distinct` was removed for one run and the new case then failed, then the file was restored. The resolution record itself says it judged this from the rule and the case input and did not mutate the code. The reviewer's round 2 verdict rests on the passing run and on reading the case.

## Limits

- This is a test addition. No production behaviour changed.
- The mutation check is a builder's log entry, not something the reviewer reproduced.
- The test runs on SQLite. It verifies request validation only, not how a repeated key behaves further down in the repository or on Postgres.
- Not every list needs `distinct`. A list that may legitimately repeat an identifier, or one that is not collapsed by key, should not get it.
- The branch is unmerged, so this is not adopted behaviour.
