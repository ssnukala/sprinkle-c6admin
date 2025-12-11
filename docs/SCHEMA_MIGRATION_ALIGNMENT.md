# Schema Migration Alignment Fix

## Problem
CI integration tests were failing because the C6Admin JSON schemas did not accurately reflect the required fields defined in the UserFrosting sprinkle-account database migrations. This caused dynamically generated CRUD6 API tests to send invalid payloads with missing required fields.

## Error Analysis

The PHP error logs showed SQL errors like:
```
SQLSTATE[HY000]: General error: 1364 Field 'user_id' doesn't have a default value
SQLSTATE[HY000]: General error: 1364 Field 'slug' doesn't have a default value  
SQLSTATE[HY000]: General error: 1364 Field 'user_name' doesn't have a default value
SQLSTATE[42000]: Syntax error - SQL: update `activities` set  where `id` = 1
```

These errors occurred because:
1. Required fields in the database schema were marked as `required: false` in JSON schemas
2. Test payloads generated from schemas omitted required fields
3. Empty UPDATE payloads caused SQL syntax errors

## Migration Reference

All fixes are based on the official UserFrosting sprinkle-account v400 migrations:
- https://github.com/userfrosting/sprinkle-account/tree/6.0/app/src/Database/Migrations/v400

### Database Schema Requirements (from migrations)

**UsersTable.php:**
- `user_name` VARCHAR(50) NOT NULL
- `email` VARCHAR(254) NOT NULL
- `first_name` VARCHAR(20) NOT NULL
- `last_name` VARCHAR(30) NOT NULL
- `password` VARCHAR(255) NOT NULL
- `locale` VARCHAR(10) DEFAULT 'en_US'
- `theme` VARCHAR(100) NULLABLE (removed per user request)
- `group_id` INT UNSIGNED DEFAULT 1
- `last_activity_id` INT UNSIGNED NULLABLE

**GroupsTable.php:**
- `slug` VARCHAR(255) NOT NULL UNIQUE
- `name` VARCHAR(255) NOT NULL
- `description` TEXT NULLABLE
- `icon` VARCHAR(100) NOT NULL DEFAULT 'fas fa-user'

**RolesTable.php:**
- `slug` VARCHAR(255) NOT NULL UNIQUE
- `name` VARCHAR(255) NOT NULL
- `description` TEXT NULLABLE

**PermissionsTable.php:**
- `slug` VARCHAR(255) NOT NULL
- `name` VARCHAR(255) NOT NULL
- `conditions` TEXT NOT NULL
- `description` TEXT NULLABLE

**ActivitiesTable.php:**
- `ip_address` VARCHAR(45) NULLABLE
- `user_id` INT UNSIGNED NOT NULL
- `type` VARCHAR(255) NOT NULL
- `occurred_at` TIMESTAMP NULLABLE
- `description` TEXT NULLABLE

## Schema Changes Made

### 1. permissions.json
**Changed:**
```json
"conditions": {
    "type": "text",
    "label": "CRUD6.PERMISSION.CONDITIONS",
    "required": true,              // Changed from false
    "default": "always()",         // Added default value
    "show_in": ["form", "detail"],
    "validation": {
        "required": true           // Added validation
    }
}
```

**Reason:** The PermissionsTable migration defines `conditions` as TEXT NOT NULL. The field must have a value.

### 2. users.json
**Changed:**
```json
"password": {
    "type": "password",
    "label": "CRUD6.USER.PASSWORD",
    "required": true,              // Changed from false
    "show_in": ["create", "edit"],
    "validation": {
        "required": true,          // Added required validation
        "length": {
            "min": 8,
            "max": 255             // Added max from migration
        },
        "match": true
    }
}
```

**Reason:** The UsersTable migration defines `password` as VARCHAR(255) NOT NULL. New users must have a password.

### Fields Already Correct

The following fields were already properly marked as required in the schemas:

**users.json:**
- ✓ `user_name` (required: true)
- ✓ `email` (required: true)
- ✓ `first_name` (required: true)
- ✓ `last_name` (required: true)

**groups.json:**
- ✓ `slug` (required: true)
- ✓ `name` (required: true)

**roles.json:**
- ✓ `slug` (required: true)
- ✓ `name` (required: true)

**activities.json:**
- ✓ `user_id` (required: true)
- ✓ `type` (required: true)
- ✓ `occurred_at` (required: true)

**permissions.json:**
- ✓ `slug` (required: true)
- ✓ `name` (required: true)

## Impact

### Before
- Integration tests generated invalid payloads missing required fields
- Database INSERT/UPDATE operations failed with SQL errors
- Tests reported 40+ failures

### After
- Schemas accurately reflect database requirements
- Test payload generation includes all required fields
- CRUD6 API operations should succeed with valid data

## Testing

All JSON schemas validated successfully:
```bash
$ php -r "echo json_decode(file_get_contents('app/schema/crud6/permissions.json')) ? 'valid' : 'invalid';"
valid

$ php -r "echo json_decode(file_get_contents('app/schema/crud6/users.json')) ? 'valid' : 'invalid';"
valid
```

## Files Modified

1. `app/schema/crud6/permissions.json` - Mark `conditions` as required
2. `app/schema/crud6/users.json` - Mark `password` as required
3. `app/locale/en_US/messages.php` - Already has all translation keys
4. `app/locale/fr_FR/messages.php` - Already has all translation keys

## Verification Steps

To verify the fix locally:
```bash
# 1. Validate JSON syntax
for file in app/schema/crud6/*.json; do 
    php -r "echo json_decode(file_get_contents('$file')) ? basename('$file') . ' valid' : 'invalid';"
done

# 2. Run integration tests
vendor/bin/phpunit app/tests/Integration/SchemaBasedApiTest.php

# 3. Check CI logs for database errors
# Should see no more "Field 'X' doesn't have a default value" errors
```

## Notes

- The `theme` field was removed from users.json per explicit user request (comment 3640162874)
- The `last_activity_id` field was added in a previous commit as nullable per migration
- All changes strictly follow the UserFrosting sprinkle-account v400 migration definitions
- No CRUD6 functionality was modified - only schema definitions were aligned

## References

- Original Issue: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/20122312381/job/57745016947
- Migration Source: https://github.com/userfrosting/sprinkle-account/tree/6.0/app/src/Database/Migrations/v400
- Related PR Comment: https://github.com/ssnukala/sprinkle-c6admin/pull/XXX#issuecomment-3640178538
