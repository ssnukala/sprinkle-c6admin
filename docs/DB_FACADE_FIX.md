# Fix: DB Facade Class Not Found Error

## Problem

The `TestUsers.php` seeder was using Laravel's `DB` facade (`\DB::table('role_users')->count()`) which is not available in UserFrosting 6, causing a fatal error:

```
PHP Fatal error: Uncaught Error: Class "DB" not found in 
/home/runner/work/sprinkle-c6admin/sprinkle-c6admin/sprinkle-c6admin/app/src/Database/Seeds/TestUsers.php:164
```

## Root Cause

UserFrosting 6 does not include Laravel's facade system. The `DB` facade is a Laravel-specific feature that provides a convenient static interface to the database layer. UserFrosting 6 uses pure Eloquent ORM patterns instead.

## Solution

Replaced the DB facade call with a proper Eloquent query using the User model's `roles` relationship:

### Before (❌ Incorrect)
```php
$totalRoleUsers = \DB::table('role_users')->count();
```

### After (✅ Correct)
```php
// Count all role_users assignments by summing roles for all users
$totalRoleUsers = User::withCount('roles')->get()->sum('roles_count');
```

## How the Fix Works

The Eloquent query works as follows:

1. `User::withCount('roles')` - Adds a `roles_count` attribute to each user containing the count of their roles
2. `->get()` - Retrieves all users with the role count
3. `->sum('roles_count')` - Sums all the role counts across all users
4. Returns the total number of `role_users` assignments

### Example

Given this data:
- User 1 has 2 roles → `roles_count = 2`
- User 2 has 1 role → `roles_count = 1`  
- User 3 has 0 roles → `roles_count = 0`

The query returns: `2 + 1 + 0 = 3` total role_users assignments

This is functionally equivalent to counting all rows in the `role_users` pivot table.

## UserFrosting 6 Best Practices

When working with UserFrosting 6:

1. **Use Eloquent Models** instead of DB facade
2. **Use Relationships** for pivot table queries
3. **Use Query Builder** methods on models (e.g., `User::count()`)
4. **Avoid Laravel Facades** - they are not part of UserFrosting 6

## Related Code Patterns

For other pivot table operations in UserFrosting 6:

```php
// Counting related records via relationship
$roleCount = $user->roles()->count();

// Getting related records
$roles = $user->roles()->get();

// Attaching/detaching pivot records
$user->roles()->attach($roleId);
$user->roles()->detach($roleId);

// Checking if relationship exists
$hasRoles = $user->roles()->exists();
```

## Files Changed

- `app/src/Database/Seeds/TestUsers.php` - Line 164

## Testing

- ✅ PHP syntax validation passed
- ✅ No other DB facade usage found in codebase
- ✅ Query logic verified to be equivalent to original intent
- ✅ Autoloader regenerated successfully

## References

- [UserFrosting 6 Documentation](https://learn.userfrosting.com/)
- [Laravel Eloquent Relationships](https://laravel.com/docs/eloquent-relationships)
- [UserFrosting GitHub Issue #19385628996](https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19385628996/job/55472051705)
