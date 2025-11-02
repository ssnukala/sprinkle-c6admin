# Schema Model Reference Update

**Date**: November 2, 2025  
**Issue**: Update schema files to use model names instead of class references  
**Related**: [sprinkle-crud6 PR #154](https://github.com/ssnukala/sprinkle-crud6/pull/154)

## Problem Statement

The sprinkle-crud6 PR #154 changed the relationship handling to pass model **instances** instead of model **class names** to Eloquent's relationship methods. This change was necessary to ensure that CRUD6Model instances maintain their configured table names and schema settings when used in relationships.

In c6admin schema files, the `belongs_to_many_through` relationships were referencing UserFrosting model classes directly:
```json
"through": "UserFrosting\\Sprinkle\\Account\\Database\\Models\\Role"
```

This approach had two issues:
1. It created a dependency on UserFrosting model classes that may not exist
2. It didn't align with the new CRUD6 pattern of using model instances

## Solution

Updated all `belongs_to_many_through` relationship `"through"` fields to use model **names** instead of class references:

```json
"through": "roles"
```

This allows CRUD6 to:
1. Resolve the model name to a configured CRUD6Model instance
2. Pass that instance (not a class name) to Eloquent's relationship methods
3. Preserve the model's table name and schema configuration

## Files Changed

### app/schema/crud6/users.json
**Line 29**: Changed permissions relationship
```diff
- "through": "UserFrosting\\Sprinkle\\Account\\Database\\Models\\Role",
+ "through": "roles",
```

### app/schema/crud6/permissions.json
**Line 29**: Changed users relationship
```diff
- "through": "UserFrosting\\Sprinkle\\Account\\Database\\Models\\Role",
+ "through": "roles",
```

## Technical Details

### Before (Class Reference)
```json
{
    "name": "permissions",
    "type": "belongs_to_many_through",
    "through": "UserFrosting\\Sprinkle\\Account\\Database\\Models\\Role",
    "first_pivot_table": "role_user",
    "first_foreign_key": "user_id",
    "first_related_key": "role_id",
    "second_pivot_table": "permission_role",
    "second_foreign_key": "role_id",
    "second_related_key": "permission_id"
}
```

### After (Model Name)
```json
{
    "name": "permissions",
    "type": "belongs_to_many_through",
    "through": "roles",
    "first_pivot_table": "role_user",
    "first_foreign_key": "user_id",
    "first_related_key": "role_id",
    "second_pivot_table": "permission_role",
    "second_foreign_key": "role_id",
    "second_related_key": "permission_id"
}
```

## Impact

### Benefits
- ✅ Aligns with sprinkle-crud6 PR #154 changes
- ✅ Removes dependency on UserFrosting model classes
- ✅ Allows CRUD6 to resolve model names to configured instances
- ✅ Ensures proper table name configuration in relationships
- ✅ More consistent with CRUD6's dynamic model pattern

### Breaking Changes
- ❌ None - This is a schema-level change that maintains API compatibility

### Testing
All JSON schemas validated successfully:
```bash
$ for file in app/schema/crud6/*.json; do php -r "if(json_decode(file_get_contents('$file'))) { echo '$file valid'; } else { echo '$file invalid'; } echo PHP_EOL;"; done
app/schema/crud6/activities.json valid
app/schema/crud6/groups.json valid
app/schema/crud6/permissions.json valid
app/schema/crud6/roles.json valid
app/schema/crud6/users.json valid
```

## How CRUD6 Processes This

### Old Flow (Class Reference)
1. Schema has: `"through": "UserFrosting\\Sprinkle\\Account\\Database\\Models\\Role"`
2. CRUD6 passes class name string to Eloquent
3. Eloquent creates new instance: `new Role()`
4. Instance may not exist or be properly configured
5. ❌ Potential errors or missing functionality

### New Flow (Model Name)
1. Schema has: `"through": "roles"`
2. CRUD6 resolves "roles" to a configured CRUD6Model instance
3. CRUD6 passes instance (not class name) to Eloquent
4. Instance has proper table name and schema configuration
5. ✅ Relationships work correctly with dynamic models

## Related Documentation

- [sprinkle-crud6 PR #154](https://github.com/ssnukala/sprinkle-crud6/pull/154) - Fix CRUD6Model table name not set in dynamic relationships
- [CRUD6 Relationship Documentation](https://github.com/ssnukala/sprinkle-crud6#relationships) - How CRUD6 handles relationships

## Migration Notes

If you have custom schemas using `belongs_to_many_through` relationships:

1. Locate all `"through"` fields in your schema JSON files
2. Change from class reference to model name:
   - Before: `"through": "UserFrosting\\Sprinkle\\Account\\Database\\Models\\Role"`
   - After: `"through": "roles"`
3. Validate JSON syntax: `php -r "json_decode(file_get_contents('your-schema.json'));"`
4. Test relationship endpoints to ensure they work correctly

## Validation Commands

### Validate JSON Syntax
```bash
cd /home/runner/work/sprinkle-c6admin/sprinkle-c6admin
php -r "echo json_decode(file_get_contents('app/schema/crud6/users.json')) ? 'users.json valid' : 'users.json invalid';"
php -r "echo json_decode(file_get_contents('app/schema/crud6/permissions.json')) ? 'permissions.json valid' : 'permissions.json invalid';"
```

### Check Git Diff
```bash
git diff app/schema/crud6/
```

## Conclusion

This update ensures c6admin schema files are compatible with the latest sprinkle-crud6 changes, removing dependency on specific UserFrosting model classes and allowing CRUD6's dynamic model system to work correctly with complex relationships.
