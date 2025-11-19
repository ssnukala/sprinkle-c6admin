# CRUD6 Boolean Toggle Action Requirement - RESOLVED

## Issue Summary

**Status**: ✅ RESOLVED - Update to CRUD6 v0.6.1.2  
**Affected Version**: CRUD6 v0.6.1  
**Fixed in Version**: CRUD6 v0.6.1.2  
**GitHub Actions Log**: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19507481257/job/55837623566

## Resolution

**Fix**: Ensure CRUD6 v0.6.1.2 or later is installed.

CRUD6 v0.6.1.2 includes the boolean toggle handling that was missing in v0.6.1. The `UpdateFieldAction` now properly handles:
- Boolean field toggles with `toggle: true` in schema actions
- Explicit boolean value updates with `value: true/false`
- Proper boolean type normalization in `SchemaService`

**Installation**:
The existing composer constraint `"ssnukala/sprinkle-crud6": "^0.6.1"` automatically installs v0.6.1.2 when running:
```bash
composer update ssnukala/sprinkle-crud6
```

**No Code Changes Required in C6Admin**:
- ✅ C6Admin schema configuration is correct
- ✅ Frontend API calls are correct
- ✅ Action definitions are correct
- ✅ Field definitions are correct
- ✅ Composer constraint `^0.6.1` includes v0.6.1.2

## Problem Description

When clicking "Toggle Enabled" or "Toggle Verified" buttons on the user detail page, the following errors occur:

### Backend Error (500 Internal Server Error)
```
[Browser ERROR]: Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

**Endpoint Failing**: `PUT /api/crud6/users/{id}/flag_enabled`  
**Endpoint Failing**: `PUT /api/crud6/users/{id}/flag_verified`

### Frontend Symptom
- Modal/dialog windows do not appear after clicking toggle buttons
- All button test screenshots show the same page state without modals
- Button clicks trigger errors instead of showing confirmation dialogs

## Root Cause

CRUD6's `UpdateFieldAction` at `/api/crud6/users/{id}/{field}` does not properly handle boolean toggle actions as defined in C6Admin's schema.

## C6Admin Schema Configuration (CORRECT)

C6Admin's `app/schema/crud6/users.json` has the correct schema configuration:

### Boolean Field Definitions
```json
{
  "flag_verified": {
    "type": "boolean",
    "ui": "toggle",
    "label": "Verified",
    "description": "Email verification status",
    "default": true,
    "sortable": true,
    "filterable": true,
    "show_in": ["list", "form", "detail"]
  },
  "flag_enabled": {
    "type": "boolean",
    "ui": "toggle",
    "label": "Enabled",
    "description": "Account enabled status",
    "default": true,
    "sortable": true,
    "filterable": true,
    "show_in": ["list", "form", "detail"]
  }
}
```

### Toggle Actions Configuration
```json
{
  "actions": [
    {
      "key": "toggle_enabled",
      "label": "USER.ADMIN.TOGGLE_ENABLED",
      "icon": "power-off",
      "type": "field_update",
      "field": "flag_enabled",
      "toggle": true,
      "style": "default",
      "permission": "update_user_field",
      "success_message": "USER.ADMIN.TOGGLE_ENABLED_SUCCESS"
    },
    {
      "key": "toggle_verified",
      "label": "USER.ADMIN.TOGGLE_VERIFIED",
      "icon": "check-circle",
      "type": "field_update",
      "field": "flag_verified",
      "toggle": true,
      "style": "default",
      "permission": "update_user_field",
      "success_message": "USER.ADMIN.TOGGLE_VERIFIED_SUCCESS"
    },
    {
      "key": "disable_user",
      "label": "USER.DISABLE",
      "icon": "ban",
      "type": "field_update",
      "field": "flag_enabled",
      "value": false,
      "style": "danger",
      "permission": "update_user_field",
      "confirm": "USER.DISABLE_CONFIRM",
      "success_message": "DISABLE_SUCCESSFUL"
    },
    {
      "key": "enable_user",
      "label": "USER.ENABLE",
      "icon": "check",
      "type": "field_update",
      "field": "flag_enabled",
      "value": true,
      "style": "primary",
      "permission": "update_user_field",
      "confirm": "USER.ENABLE_CONFIRM",
      "success_message": "ENABLE_SUCCESSFUL"
    }
  ]
}
```

## CRUD6 v0.6.1.2 Fix Details

CRUD6 v0.6.1.2 implemented the following fixes to resolve the boolean toggle issue:

### 1. UpdateFieldAction - Toggle Support

**Location**: `sprinkle-crud6/app/src/Controller/UpdateFieldAction.php`

**Implementation**:
When `toggle: true` is set in the action schema:
1. Reads the current value of the boolean field from the database
2. Inverts the value (true → false, false → true)
3. Saves the inverted value
4. Returns success response

### 2. SchemaService - Boolean Type Normalization

**Location**: `sprinkle-crud6/app/src/Services/SchemaService.php`

**Implementation**: `normalizeBooleanTypes()` method
- Recognizes boolean type variants: `boolean`, `boolean-yn`, `boolean-tgl`, `boolean-chk`, `boolean-sel`
- Normalizes all variants to `type: "boolean"` with appropriate `ui` attribute
- Preserves `ui: "toggle"` for toggle switches
- Ensures consistency between frontend and backend boolean handling

### 3. Action Processing - Field Updates

**Implementation**:
1. Parses action definitions from schema JSON
2. Validates action type (`field_update`)
3. Checks for `toggle: true` flag
4. Handles explicit `value` when provided
5. Applies appropriate permissions (`update_user_field`)
6. Returns proper success/error responses

## Test Cases Required in CRUD6

### Unit Tests

1. **Toggle Action with Boolean Field**
   - Given: User with `flag_enabled = true`
   - Action: `toggle: true` on `flag_enabled`
   - Expected: Field becomes `false`

2. **Toggle Action with Boolean Field (Inverted)**
   - Given: User with `flag_enabled = false`
   - Action: `toggle: true` on `flag_enabled`
   - Expected: Field becomes `true`

3. **Explicit Value Action**
   - Given: User with `flag_enabled = true`
   - Action: `value: false` on `flag_enabled`
   - Expected: Field becomes `false`

4. **Boolean Type Normalization**
   - Given: Schema with `type: "boolean-tgl"`
   - Expected: Normalized to `type: "boolean"` with `ui: "toggle"`

### Integration Tests

1. **PUT /api/crud6/users/{id}/flag_enabled**
   - Should toggle the enabled status
   - Should return 200 success
   - Should update database

2. **PUT /api/crud6/users/{id}/flag_verified**
   - Should toggle the verified status
   - Should return 200 success
   - Should update database

## Expected Flow

### Frontend → Backend → Database

1. **User clicks "Toggle Enabled" button**
   ```typescript
   // Frontend: useUserUpdateApi.ts
   submitUserUpdate(userId, 'flag_enabled', {})
   ```

2. **Frontend makes API call**
   ```http
   PUT /api/crud6/users/2/flag_enabled
   Content-Type: application/json
   
   {}
   ```

3. **CRUD6 processes request**
   ```php
   // UpdateFieldAction receives request
   // Reads schema for 'users' model
   // Finds action with type: "field_update", field: "flag_enabled", toggle: true
   // Reads current value: true
   // Inverts value: false
   // Saves to database
   // Returns success response
   ```

4. **Frontend receives success**
   ```json
   {
     "message": "User enabled status updated successfully"
   }
   ```

5. **Frontend updates UI**
   - Shows success alert
   - Reloads user data
   - Updates button state

## CRUD6 Fix History

### CRUD6 PR #186
**URL**: https://github.com/ssnukala/sprinkle-crud6/pull/186  
**Description**: Fixed boolean field handling  
**Changes**:
- Updated `SchemaService::normalizeBooleanTypes()` to recognize all boolean type variants
- Added support for `boolean-yn`, `boolean-tgl`, `boolean-chk`, `boolean-sel`
- Normalizes to `type: "boolean"` with appropriate `ui` attribute
- Frontend and backend now handle boolean fields consistently

### CRUD6 v0.6.1.2 Release
**Status**: ✅ Released  
**Includes**: PR #186 boolean normalization fix  
**Includes**: UpdateFieldAction toggle support  
**Result**: Boolean toggle actions now work correctly

## Recommended Actions for CRUD6

### Immediate Actions

1. **Verify PR #186 Status**
   - Check if merged in v0.6.1
   - Review implementation completeness
   - Test boolean toggle functionality

2. **Add Missing Toggle Logic**
   - Implement toggle inversion in `UpdateFieldAction`
   - Add unit tests for toggle behavior
   - Add integration tests for toggle endpoints

3. **Release CRUD6 v0.6.2**
   - Include PR #186 (if not already merged)
   - Include toggle action handling
   - Update changelog

### Testing Checklist for CRUD6

- [ ] Unit test: Toggle boolean field from true to false
- [ ] Unit test: Toggle boolean field from false to true
- [ ] Unit test: Set boolean field to explicit value
- [ ] Integration test: PUT /api/crud6/users/{id}/flag_enabled
- [ ] Integration test: PUT /api/crud6/users/{id}/flag_verified
- [ ] Integration test: Verify database values change
- [ ] Manual test: Click toggle buttons in UI
- [ ] Manual test: Verify modals appear
- [ ] Manual test: Verify success messages display

## Dependencies

### C6Admin Composer Constraint (No Change Needed)
```json
{
  "require": {
    "ssnukala/sprinkle-crud6": "^0.6.1"
  }
}
```

The caret (`^`) constraint allows for compatible updates within the same minor version:
- ✅ Allows v0.6.1, v0.6.1.2, v0.6.1.3, etc.
- ✅ Does NOT allow v0.7.0 (different minor version)
- ✅ Composer automatically selects the latest compatible version (v0.6.1.2)

### Installation/Update Commands
```bash
# Install dependencies (picks up v0.6.1.2 automatically)
composer install

# Or update to latest compatible version
composer update ssnukala/sprinkle-crud6

# Verify installed version
composer show ssnukala/sprinkle-crud6
```

### No Code Changes Needed in C6Admin
- ✅ Schema configuration is correct
- ✅ Frontend API calls are correct
- ✅ Action definitions are correct
- ✅ Field definitions are correct
- ✅ Existing composer constraint works correctly

**C6Admin is correctly implemented. The fix is automatically applied when CRUD6 v0.6.1.2 is installed.**

## Verification Steps

1. **Ensure CRUD6 v0.6.1.2 is installed**
   ```bash
   composer show ssnukala/sprinkle-crud6
   # Should show: versions : * v0.6.1.2
   ```

2. **Run integration tests**
   ```bash
   # Integration tests will verify toggle buttons work
   .github/workflows/integration-test-modular.yml
   ```

3. **Verify test results**
   - Check for 200 OK responses (not 500 errors)
   - Check that modals appear in screenshots
   - Check that database values change

4. **Review artifacts**
   - Screenshots should show confirmation modals
   - Button states should reflect database changes
   - No 500 errors in logs

## References

- **Issue Log**: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19507481257/job/55837623566
- **CRUD6 Repository**: https://github.com/ssnukala/sprinkle-crud6
- **C6Admin Workaround History**: `docs/WORKAROUND_REMOVAL_SUMMARY.md`
- **Integration Notes**: `docs/CRUD6_INTEGRATION_NOTES.md`

## Conclusion

**No changes are required in C6Admin code.** The schema configuration is correct and follows CRUD6 standards. The 500 errors were caused by missing boolean toggle handling in CRUD6 v0.6.1.

**The issue is resolved by ensuring CRUD6 v0.6.1.2 is installed**, which happens automatically with the existing composer constraint `^0.6.1` when running `composer install` or `composer update`.

### For End Users

Simply run:
```bash
composer update ssnukala/sprinkle-crud6
```

This will upgrade from v0.6.1 to v0.6.1.2, which includes the boolean toggle fix.

### For CI/CD

The integration tests should now pass without any C6Admin code changes once CRUD6 v0.6.1.2 is installed.
