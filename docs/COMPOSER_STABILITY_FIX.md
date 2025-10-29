# Composer Stability Fix

## Issue
The GitHub Actions integration test was failing during the `composer install` step with the following error:

```
Problem 1
  - Root composer.json requires ssnukala/sprinkle-c6admin @dev -> satisfiable by ssnukala/sprinkle-c6admin[dev-main].
  - ssnukala/sprinkle-c6admin dev-main requires ssnukala/sprinkle-crud6 dev-main -> found ssnukala/sprinkle-crud6[dev-main] but it does not match your minimum-stability.
```

## Root Cause
When `sprinkle-c6admin` is installed as a dependency in a UserFrosting 6 project that has `minimum-stability: beta` (as done in the integration test workflow), Composer won't allow `dev-main` dependencies unless they're explicitly marked to override the stability requirement.

The workflow sets:
- `minimum-stability: beta` in the UserFrosting project
- `prefer-stable: true` in the UserFrosting project

However, `sprinkle-c6admin`'s composer.json required `ssnukala/sprinkle-crud6: "dev-main"` without a stability flag, which is incompatible with `minimum-stability: beta`.

## Solution
Changed the dependency constraint from:
```json
"ssnukala/sprinkle-crud6": "dev-main"
```

To:
```json
"ssnukala/sprinkle-crud6": "^0.1@dev"
```

### Why This Works
- The `@dev` stability flag explicitly tells Composer that this package is allowed to be a dev version, overriding the parent project's `minimum-stability: beta` setting
- The `^0.1` constraint allows any version >= 0.1 and < 1.0, which includes `dev-main` when it's aliased as `0.x-dev` (which is the default for the main branch)
- This is compatible with both:
  - Projects with `minimum-stability: dev` (like sprinkle-c6admin itself)
  - Projects with `minimum-stability: beta` (like the integration test scenario)

## Testing
1. ✅ Validated composer.json syntax with `composer validate`
2. ✅ Verified JSON is valid with Python json.tool
3. ✅ Tested autoload generation with `composer dump-autoload`
4. ✅ Verified no PHP syntax errors in source files

## References
- [Composer: Package Versions - Stability](https://getcomposer.org/doc/articles/versions.md#stability)
- [Composer: Minimum Stability](https://getcomposer.org/doc/04-schema.md#minimum-stability)
- GitHub Actions Run: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/18895873050/job/53933717916
