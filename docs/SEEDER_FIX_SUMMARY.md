# Seeder Inheritance Pattern Fix

## Issue
GitHub Actions integration test was failing with the error:
```
Error: ] Class is not a valid seed :                                            
         UserFrosting\Sprinkle\C6Admin\Database\Seeds\TestGroups
Error: Process completed with exit code 1.
```

**Source**: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19385415536/job/55471495522

## Root Cause
The seeder classes `TestGroups` and `TestUsers` in `app/src/Database/Seeds/` were incorrectly extending `Illuminate\Database\Seeder`, which is incompatible with UserFrosting 6's seeder system.

### Incorrect Pattern (Before)
```php
use Illuminate\Database\Seeder;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

class TestGroups extends Seeder implements SeedInterface
{
    public function run(): void { ... }
}
```

### Correct Pattern (After)
```php
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

class TestGroups implements SeedInterface
{
    public function run(): void { ... }
}
```

## UserFrosting 6 Seeder Pattern
UserFrosting 6 seeders should **only implement `SeedInterface`**, not extend `Illuminate\Database\Seeder`.

### Official Examples
1. **sprinkle-account** (`DefaultGroups.php`):
   ```php
   class DefaultGroups implements SeedInterface
   ```

2. **sprinkle-crud6** (`DefaultRoles.php`):
   ```php
   class DefaultRoles implements SeedInterface
   ```

Both official implementations:
- Only implement `SeedInterface`
- Do NOT extend `Illuminate\Database\Seeder`
- Provide a `run(): void` method

## Solution Implemented
Modified two seeder files to follow the correct UserFrosting 6 pattern:

### 1. TestGroups.php
**Changes**:
- Removed `extends Seeder` from class declaration
- Removed unused `use Illuminate\Database\Seeder;` import
- Kept `implements SeedInterface`

**Diff**:
```diff
-use Illuminate\Database\Seeder;
 use UserFrosting\Sprinkle\Account\Database\Models\Group;
 use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

-class TestGroups extends Seeder implements SeedInterface
+class TestGroups implements SeedInterface
```

### 2. TestUsers.php
**Changes**:
- Removed `extends Seeder` from class declaration
- Removed unused `use Illuminate\Database\Seeder;` import
- Kept `implements SeedInterface`

**Diff**:
```diff
-use Illuminate\Database\Seeder;
 use UserFrosting\Sprinkle\Account\Database\Models\Group;
 use UserFrosting\Sprinkle\Account\Database\Models\Role;
 use UserFrosting\Sprinkle\Account\Database\Models\User;
 use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

-class TestUsers extends Seeder implements SeedInterface
+class TestUsers implements SeedInterface
```

## Validation
- ✅ PHP syntax check passed: `php -l` on both files
- ✅ Pattern matches official UserFrosting 6 seeder implementations
- ✅ No security issues detected (CodeQL)
- ⏳ CI test will validate the fix in GitHub Actions

## Expected Outcome
The GitHub Actions workflow should now successfully run:
```bash
php bakery seed UserFrosting\\Sprinkle\\C6Admin\\Database\\Seeds\\TestGroups --force
php bakery seed UserFrosting\\Sprinkle\\C6Admin\\Database\\Seeds\\TestUsers --force
```

Both commands should execute without the "Class is not a valid seed" error.

## References
- UserFrosting 6 sprinkle-account: https://github.com/userfrosting/sprinkle-account/blob/6.0/app/src/Database/Seeds/DefaultGroups.php
- CRUD6 sprinkle: https://github.com/ssnukala/sprinkle-crud6/blob/main/app/src/Database/Seeds/DefaultRoles.php
- UserFrosting 6 SeedInterface: Part of `userfrosting/sprinkle-core`

## Commit
- **Branch**: `copilot/fix-seeder-error`
- **Commit**: `01b7b60` - "Fix seeder inheritance pattern - remove Illuminate\Database\Seeder"
- **Files Modified**: 
  - `app/src/Database/Seeds/TestGroups.php`
  - `app/src/Database/Seeds/TestUsers.php`

## Date
2025-11-15
