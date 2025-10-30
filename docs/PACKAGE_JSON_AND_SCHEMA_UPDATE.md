# Package.json and Schema Field Visibility Update

## Problem Statement

1. **Package.json Dependency Issue**: The `package.json` was using a specific version (`^0.5.0`) for `@ssnukala/sprinkle-crud6`, but this version may not exist. The `composer.json` uses `"dev-main as 6.0.x-dev"` to pull from the main branch, and `package.json` should use a similar approach.

2. **Schema Field Visibility**: The schema JSON files were showing fields that don't match the official `userfrosting/sprinkle-admin` interface, including sensitive fields like password in the users table.

## Changes Made

### 1. Package.json Update

**File**: `package.json`

**Change**: Updated the `peerDependencies` section to use a GitHub URL instead of a version number:

```json
"@ssnukala/sprinkle-crud6": "github:ssnukala/sprinkle-crud6#main"
```

**Rationale**: This matches the approach used in `composer.json` and ensures that the package always pulls from the latest main branch, avoiding issues with non-existent version numbers.

### 2. Schema Field Visibility Updates

Updated the `listable` attribute in schema files to match the field visibility patterns from `userfrosting/sprinkle-admin`:

#### users.json
**Hidden fields** (changed `listable` to `false`):
- `id` - Not shown in sprinkle-admin tables
- `locale` - Not shown in sprinkle-admin tables  
- `group_id` - Not shown in sprinkle-admin tables
- `created_at` - Not shown in sprinkle-admin tables

**Visible fields** (kept `listable` as `true`):
- `user_name`, `first_name`, `last_name` - Combined in name column
- `email` - Shown below name
- `flag_verified`, `flag_enabled` - Combined in status column

**Already hidden** (already had `listable: false`):
- `password` - NEVER shown (security)
- `deleted_at` - Not shown in sprinkle-admin
- `updated_at` - Not shown in sprinkle-admin

#### groups.json
**Hidden fields** (changed `listable` to `false`):
- `id` - Not shown in sprinkle-admin tables
- `slug` - Not shown as separate column (used in links)
- `icon` - Shown inline with name, not as separate column
- `created_at` - Not shown in sprinkle-admin tables

**Visible fields** (kept `listable` as `true`):
- `name` - Main column
- `description` - Separate column

**Already hidden**:
- `updated_at` - Already had `listable: false`

#### roles.json
**Hidden fields** (changed `listable` to `false`):
- `id` - Not shown in sprinkle-admin tables
- `slug` - Not shown as separate column (used in links)
- `created_at` - Not shown in sprinkle-admin tables

**Visible fields** (kept `listable` as `true`):
- `name` - Main column
- `description` - Separate column

**Already hidden**:
- `updated_at` - Already had `listable: false`

#### permissions.json
**Hidden fields** (changed `listable` to `false`):
- `id` - Not shown in sprinkle-admin tables
- `created_at` - Not shown in sprinkle-admin tables

**Visible fields** (kept `listable` as `true`):
- `name` - Main column
- `slug` - Combined with conditions and description in properties column
- `conditions` - Part of properties column
- `description` - Part of properties column

**Already hidden**:
- `updated_at` - Already had `listable: false`

#### activities.json
**Hidden fields** (changed `listable` to `false`):
- `id` - Not shown in sprinkle-admin tables
- `ip_address` - Combined with description, not separate column
- `user_id` - Not shown (user object is shown instead)
- `type` - Not shown as separate column

**Visible fields** (kept `listable` as `true`):
- `occurred_at` - Main timestamp column
- `description` - Combined with ip_address

## Reference

Field visibility patterns were determined by examining the official `userfrosting/sprinkle-admin` (6.0 branch) table templates:
- `app/templates/tables/users.html.twig`
- `app/templates/tables/groups.html.twig`
- `app/templates/tables/roles.html.twig`
- `app/templates/tables/permissions.html.twig`
- `app/templates/tables/activities.html.twig`

## Validation

All JSON schemas were validated after changes:
```bash
for file in app/schema/crud6/*.json; do 
  php -r "if(json_decode(file_get_contents('$file'))) { 
    echo basename('$file') . ' valid'; 
  } else { 
    echo basename('$file') . ' INVALID'; 
  } echo PHP_EOL;"
done
```

Result: All schemas valid ✓

## Impact

These changes ensure that:
1. The integration tests will pass because NPM can install `@ssnukala/sprinkle-crud6` from the main branch
2. The C6Admin interface will show the same fields as the official sprinkle-admin interface
3. Sensitive fields like password will not be displayed in tables
4. The UI is cleaner and more focused, matching UserFrosting best practices

## Testing

To test these changes in a UserFrosting 6 application:
1. Install the package: `npm install github:ssnukala/sprinkle-c6admin#main`
2. Navigate to `/c6/admin/users` and verify only appropriate fields are shown
3. Navigate to `/c6/admin/groups`, `/c6/admin/roles`, `/c6/admin/permissions`, `/c6/admin/activities`
4. Compare with `/admin/users` (sprinkle-admin) to verify matching field visibility
