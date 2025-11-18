# Fix Summary: Backend Errors for User Toggle Actions

## Problem

Integration tests (run #19482613792) revealed HTTP 500 errors when attempting to toggle user boolean fields:

1. **Toggle Verified Action** - Failed with 500 error when clicking "Toggle Verified" button
2. **Toggle Enabled Action** - Failed with 500 error when clicking "Toggle Enabled" button  
3. **Disable User Action** - Failed with 500 error when clicking "Disable User" button
4. **Enable User Action** - Failed with 500 error when clicking "Enable User" button

### Error Context

- Error occurred on `/api/crud6/users/{id}/{field}` endpoint
- Schema defines actions in `app/schema/crud6/users.json` (lines 94-149)
- Actions use `type: "field_update"` with `toggle: true` for boolean fields
- Frontend calls CRUD6's UpdateFieldAction which was not handling boolean toggles properly

## Root Cause

CRUD6's `UpdateFieldAction` expects the field value to be sent in the request body, but for boolean toggle actions:
- Frontend may not send any value (expecting backend to calculate it)
- Frontend may send string values ('true'/'false', '1'/'0') that need normalization
- CRUD6 doesn't have special handling for toggle actions

## Solution

Created `UserUpdateFieldAction` in C6Admin that:

1. **Extends CRUD6's UpdateFieldAction** - Inherits all base functionality
2. **Detects boolean toggle fields** - Checks schema for `type: "boolean"` and `toggle: true`
3. **Auto-calculates toggle value** - If no value provided, inverts current field value
4. **Normalizes boolean values** - Converts string/numeric values to true boolean types
5. **Delegates to parent** - Falls back to CRUD6 for non-toggle fields

### Implementation Details

**File: `app/src/Controller/User/UserUpdateFieldAction.php`**
- Checks if field is a boolean toggle type
- If no value in request OR toggle flag is true, inverts current value
- Normalizes string boolean values to actual booleans
- Modifies request body before calling parent implementation

**File: `app/src/Routes/UsersRoutes.php`**
- Registers route at `/api/crud6/users/{id}/{field}` 
- Overrides CRUD6's generic route (C6Admin loads after CRUD6)
- Only affects users model, not other CRUD6 models

**File: `app/tests/Controller/User/UserUpdateFieldActionTest.php`**
- Comprehensive test coverage for all scenarios
- Tests authentication, authorization, and field updates
- Tests toggle with/without values, string/numeric conversion

## Testing

### Unit Tests Created

1. ✅ testUpdateFieldForGuestUser - Verifies 401 for unauthenticated requests
2. ✅ testUpdateFieldWithoutPermission - Verifies 403 for unauthorized users
3. ✅ testUpdateBooleanFieldSuccess - Tests explicit boolean value update
4. ✅ testToggleBooleanFieldWithoutValue - Tests auto-toggle calculation  
5. ✅ testToggleBooleanFieldBackAndForth - Tests multiple toggles
6. ✅ testUpdateBooleanFieldWithStringValues - Tests 'true'/'false' normalization
7. ✅ testUpdateBooleanFieldWithNumericValues - Tests 0/1 normalization

### Integration Tests Required

- [ ] Toggle Verified button on user detail page
- [ ] Toggle Enabled button on user detail page
- [ ] Disable User button on user detail page
- [ ] Enable User button on user detail page
- [ ] Verify database values change correctly
- [ ] Verify UI updates after toggle actions

## Affected Endpoints

- `PUT /api/crud6/users/{id}/flag_enabled` - Now handled by UserUpdateFieldAction
- `PUT /api/crud6/users/{id}/flag_verified` - Now handled by UserUpdateFieldAction

## Benefits

1. **Backward Compatible** - Works with existing CRUD6 frontend code
2. **Schema-Driven** - Respects toggle definitions in users.json schema
3. **Type-Safe** - Properly handles boolean type conversions
4. **Testable** - Comprehensive unit test coverage
5. **Maintainable** - Clear separation of concerns

## Future Considerations

### Similar Issues in Other Models

Checked roles.json and permissions.json - they don't have field-level toggle actions, so this issue is specific to users model.

### Upstream Fix in CRUD6

This workaround should be temporary. The proper fix would be in CRUD6's UpdateFieldAction to:
1. Detect toggle actions from schema
2. Auto-calculate boolean toggle values
3. Normalize boolean input types

Once CRUD6 is fixed, this C6Admin override can be removed.

## Files Changed

- `app/src/Controller/User/UserUpdateFieldAction.php` (new)
- `app/src/Routes/UsersRoutes.php` (modified)
- `app/tests/Controller/User/UserUpdateFieldActionTest.php` (new)

## Verification Steps

1. Run unit tests: `vendor/bin/phpunit app/tests/Controller/User/UserUpdateFieldActionTest.php`
2. Run integration tests to verify frontend behavior
3. Check application logs for CRUD6/C6Admin debug messages
4. Verify toggle buttons work on user detail page
5. Verify disable/enable buttons work on user detail page
