# CRUD6 Issue Report: Boolean Toggle Actions Failing with 500 Errors

## Issue Summary

**Component**: CRUD6 v0.6.1.2  
**Status**: ⚠️ CONFIRMED BUG - Boolean toggle actions fail despite v0.6.1.2 being installed  
**Severity**: High - Core CRUD functionality broken for boolean fields  
**Affects**: All boolean toggle actions in user management (and likely other models)

## Reproduction

### Environment
- **CRUD6 Version**: v0.6.1.2 (confirmed installed in test)
- **UserFrosting**: v6.0-beta.5
- **PHP**: 8.1.33
- **Test Environment**: GitHub Actions CI
- **Test Log**: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19507481257/job/55837623566

### Steps to Reproduce
1. Install C6Admin with CRUD6 v0.6.1.2
2. Navigate to user detail page: `/c6/admin/users/2`
3. Click "Toggle Enabled" button
4. **Expected**: Confirmation modal appears, value toggles
5. **Actual**: 500 Internal Server Error, no modal appears

### Failing Endpoints
```
PUT /api/crud6/users/2/flag_enabled  → 500 Error
PUT /api/crud6/users/2/flag_verified → 500 Error
```

## Evidence

### Browser Console Errors
```
[Browser ERROR]: Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

### Test Results
From `user_detail_button_test_results.json`:
```json
{
  "buttonText": "Toggle Enabled",
  "success": true,
  "message": "Button clicked but status may not have changed (button text: \"Toggle Enabled\")"
}
```

The test marks it as "success" because the button clicked, but:
1. Modal did not appear (screenshots show no modal)
2. HTTP request returned 500 error
3. Database value did not change

### Screenshots
All toggle button screenshots show the same page state without modals:
- `screenshot_button_Toggle_Enabled_before.png` - Page before click
- `screenshot_button_Toggle_Enabled_modal.png` - NO MODAL (should show confirmation)
- `screenshot_button_Toggle_Enabled_after.png` - Same as before (no change)

## Schema Configuration (C6Admin)

The C6Admin schema is correctly configured according to CRUD6 standards:

### Field Definition
```json
{
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

### Action Definition
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
    }
  ]
}
```

## Frontend Integration (C6Admin)

C6Admin correctly uses CRUD6's components:

### Route Configuration
```typescript
{
  path: ':id',
  name: 'c6admin.user',
  component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6DynamicPage),
  beforeEnter: (to) => {
    to.params.model = 'users'
  }
}
```

### API Call
C6Admin uses CRUD6's API pattern:
```typescript
// useUserUpdateApi.ts
PUT /api/crud6/users/{id}/{fieldName}
```

## Problem Analysis

Since C6Admin v0.6.1.2 was confirmed to be installed, the issue must be in CRUD6 itself:

### Possible Root Causes

#### 1. CRUD6DynamicPage Component Issue (Frontend)
**Location**: `@ssnukala/sprinkle-crud6/views/CRUD6DynamicPage.vue`

**Hypothesis**: The component may not be:
- Reading action definitions from schema correctly
- Detecting `toggle: true` flag
- Sending the correct API request format
- Handling the toggle button click properly

**What to Check**:
```vue
<!-- When user clicks toggle button, does it: -->
- Read the action from schema actions array?
- Extract field name from action.field?
- Detect toggle: true?
- Call the UpdateFieldAction endpoint?
- Send empty body {} or specific toggle request?
```

#### 2. UpdateFieldAction Endpoint Issue (Backend)
**Location**: `sprinkle-crud6/app/src/Controller/UpdateFieldAction.php`

**Hypothesis**: The endpoint may not be:
- Properly handling toggle requests
- Reading the action definition from schema
- Inverting boolean values correctly
- Validating permissions properly

**What to Check**:
```php
// When PUT /api/crud6/users/2/flag_enabled is called:
- Does it load the users.json schema?
- Does it find the toggle_enabled action?
- Does it detect toggle: true?
- Does it read current value from database?
- Does it invert the value?
- Does it save to database?
- Does it return 200 success?
```

#### 3. Schema Loading Issue
**Hypothesis**: Schema actions might not be loaded or parsed correctly

**What to Check**:
- Does SchemaService load `users.json` from C6Admin?
- Does it parse the `actions` array?
- Are action definitions available to frontend and backend?

#### 4. Permissions Issue
**Hypothesis**: Permission check might be failing

**What to Check**:
- Does user have `update_user_field` permission?
- Is permission check applied correctly?
- Is 500 error from permission denial?

## Expected Behavior

### Correct Flow for Toggle Action

1. **User clicks "Toggle Enabled" button**

2. **Frontend (CRUD6DynamicPage)**:
   ```javascript
   // Read action from schema
   action = schema.actions.find(a => a.key === 'toggle_enabled')
   
   // Verify action type and toggle flag
   if (action.type === 'field_update' && action.toggle === true) {
     // Make API call
     PUT /api/crud6/users/2/flag_enabled
     Body: { toggle: true } // or empty body if toggle is inferred
   }
   ```

3. **Backend (UpdateFieldAction)**:
   ```php
   // Load schema
   $schema = SchemaService::load('users');
   
   // Find action
   $action = array_find($schema['actions'], fn($a) => $a['field'] === 'flag_enabled');
   
   // Check if toggle
   if ($action['toggle'] === true) {
     // Read current value
     $currentValue = $user->flag_enabled;
     
     // Invert
     $newValue = !$currentValue;
     
     // Save
     $user->flag_enabled = $newValue;
     $user->save();
     
     // Return success
     return response()->json(['message' => 'User enabled status updated']);
   }
   ```

4. **Frontend receives success**:
   - Show success alert
   - Reload user data
   - Update button state
   - **Show confirmation modal if configured**

## Debug Recommendations

### 1. Enable CRUD6 Debug Logging

Add logging to UpdateFieldAction:
```php
Log::debug('UpdateFieldAction called', [
    'model' => $model,
    'id' => $id,
    'field' => $field,
    'request_data' => $request->all(),
    'schema_loaded' => $schema !== null,
    'action_found' => $action !== null,
    'toggle_flag' => $action['toggle'] ?? false
]);
```

### 2. Check Vue Component Logic

Add console logging to CRUD6DynamicPage:
```javascript
console.log('Toggle button clicked', {
  action: action,
  field: action.field,
  toggle: action.toggle,
  endpoint: `/api/crud6/${model}/${id}/${action.field}`
})
```

### 3. Verify Schema Loading

Test that schema actions are accessible:
```php
$schema = SchemaService::load('users');
dd($schema['actions']); // Should show toggle_enabled action
```

### 4. Test Endpoint Directly

Use curl or Postman:
```bash
# Test toggle endpoint
curl -X PUT http://localhost:8080/api/crud6/users/2/flag_enabled \
  -H "Content-Type: application/json" \
  -H "Cookie: uf_session=..." \
  -d '{}'

# Expected: 200 OK
# Actual: 500 Error
```

## Temporary Workaround (Not Recommended)

While waiting for CRUD6 fix, C6Admin could implement custom controllers:

```php
// app/src/Controller/User/UserToggleEnabledAction.php
class UserToggleEnabledAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = User::findOrFail($args['id']);
        $user->flag_enabled = !$user->flag_enabled;
        $user->save();
        
        return $response->withJson(['message' => 'User enabled status updated']);
    }
}
```

**However**, this defeats the purpose of using CRUD6 and creates maintenance burden.

## Required CRUD6 Fixes

### Priority 1: Fix UpdateFieldAction Backend
- Ensure toggle actions are handled correctly
- Add proper error logging
- Return 200 success instead of 500 error
- Add unit tests for toggle actions

### Priority 2: Fix CRUD6DynamicPage Frontend
- Ensure action buttons trigger correct API calls
- Handle toggle actions properly
- Show confirmation modals
- Update UI after successful toggle

### Priority 3: Add Integration Tests
- Test toggle actions end-to-end
- Verify modals appear
- Verify database values change
- Test with different boolean field types

## Testing Checklist for CRUD6

After implementing fixes, verify:

- [ ] PUT /api/crud6/users/{id}/flag_enabled returns 200 (not 500)
- [ ] PUT /api/crud6/users/{id}/flag_verified returns 200 (not 500)
- [ ] Database value changes from true to false
- [ ] Database value changes from false to true  
- [ ] Confirmation modal appears before toggle
- [ ] Success message displays after toggle
- [ ] UI updates to reflect new state
- [ ] Action works with `toggle: true` in schema
- [ ] Action works with `value: true/false` in schema
- [ ] Permissions are enforced correctly

## References

- **C6Admin Integration Test**: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19507481257
- **Test Artifacts**: Screenshots and JSON results showing modal failures
- **Schema Definition**: `app/schema/crud6/users.json` in C6Admin repository
- **Documentation**: `docs/CRUD6_BOOLEAN_TOGGLE_REQUIREMENT.md` in C6Admin repository

## Contact

For questions about C6Admin integration with CRUD6:
- **Repository**: https://github.com/ssnukala/sprinkle-c6admin
- **Issue Tracker**: https://github.com/ssnukala/sprinkle-c6admin/issues

For CRUD6 bug reports:
- **Repository**: https://github.com/ssnukala/sprinkle-crud6
- **Issue Tracker**: https://github.com/ssnukala/sprinkle-crud6/issues
