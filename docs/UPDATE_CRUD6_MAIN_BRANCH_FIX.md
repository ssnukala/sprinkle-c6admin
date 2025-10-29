# Fix: Update sprinkle-crud6 Dependency to Use dev-main Branch

## Issue
The composer.json dependency constraint `"ssnukala/sprinkle-crud6": "^0.1@dev"` was not loading the main branch as intended. Instead, it was searching for tagged versions starting with 0.1.x.

## Root Cause
In Composer, the constraint syntax has specific meanings:
- `^0.1@dev` - This searches for tagged versions matching 0.1.x with dev stability
- `dev-main` - This directly references the main branch

The original constraint would look for tags like 0.1.0, 0.1.1, etc., rather than pulling from the main branch.

## Solution
Changed the dependency constraint in composer.json from:
```json
"ssnukala/sprinkle-crud6": "^0.1@dev"
```

To:
```json
"ssnukala/sprinkle-crud6": "dev-main"
```

## Verification
The sprinkle-crud6 repository has:
- Main branch at commit: a9c8e0b31fa5d41cd0420102d1a0643496227e2f
- Multiple tagged versions from 0.1.0 to 0.5.9.1

With `dev-main`, Composer will now:
1. Clone the repository
2. Checkout the main branch
3. Pull the latest commits from main

## Testing
Added a new test file `app/tests/ComposerConfigTest.php` that verifies:
- The composer.json file is valid JSON
- The require section exists
- The sprinkle-crud6 dependency is present
- The dependency uses exactly "dev-main" as the version constraint

## Impact
- **Breaking Change**: No - this only affects which version of sprinkle-crud6 is installed
- **Behavior Change**: Yes - will now pull latest main branch instead of looking for 0.1.x tags
- **Benefits**: Always gets the latest development version from main branch

## Files Modified
- `composer.json` - Updated dependency constraint
- `app/tests/ComposerConfigTest.php` - Added test to prevent regression

## Related Documentation
- [Composer Documentation: Versions and Constraints](https://getcomposer.org/doc/articles/versions.md)
- [Composer: Branch Alias](https://getcomposer.org/doc/articles/aliases.md)
