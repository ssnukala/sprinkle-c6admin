# Fix for Composer Dependency Resolution Issue

## Problem
The integration-test workflow was failing with this error:
```
Error: Your requirements could not be resolved to an installable set of packages.

Problem 1
  - Root composer.json requires ssnukala/sprinkle-c6admin @dev -> satisfiable by ssnukala/sprinkle-c6admin[dev-main].
  - ssnukala/sprinkle-c6admin dev-main requires ssnukala/sprinkle-crud6 dev-main -> found ssnukala/sprinkle-crud6[dev-main] but it does not match your minimum-stability.
```

## Root Cause
When the UserFrosting project (which sets `minimum-stability: beta`) tries to install sprinkle-c6admin, it encounters a conflict:
- sprinkle-c6admin requires `ssnukala/sprinkle-crud6: "dev-main"`
- The `dev-main` branch is treated as a dev package (less stable than beta)
- This violates the parent project's minimum-stability constraint

## Solution
Changed the dependency specification from:
```json
"ssnukala/sprinkle-crud6": "dev-main"
```

To:
```json
"ssnukala/sprinkle-crud6": "dev-main as 6.0.x-dev"
```

The `as 6.0.x-dev` clause creates a version alias that tells Composer to treat the `dev-main` branch as version `6.0.x-dev`. This allows:
1. The dependency to be installed even when `minimum-stability: beta` is set
2. Composer to recognize it as compatible with the `^6.0` version constraint
3. Proper dependency resolution in the integration test workflow

## Verification
- Composer validate passes: ✅
- JSON syntax is valid: ✅
- Change is minimal and surgical: ✅
