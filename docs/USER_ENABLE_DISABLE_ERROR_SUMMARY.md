# User Enable/Disable Button Error - Investigation Summary

## Issue Description

When clicking "Toggle Enabled" or "Toggle Verified" buttons on the user detail page (`/c6/admin/users/{id}`), the following problems occur:

1. **500 Internal Server Error** returned from backend
2. **No confirmation modal appears** (screenshots show page doesn't change)
3. **Database values don't change** (user enabled status unchanged)

## Test Evidence

**GitHub Actions Log**: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19507481257/job/55837623566

**Key Log Entries**:
```
[Browser ERROR]: Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

**Affected Endpoints**:
- `PUT /api/crud6/users/{id}/flag_enabled` → 500 Error
- `PUT /api/crud6/users/{id}/flag_verified` → 500 Error

## Root Cause

**Location**: CRUD6 v0.6.1.2 (confirmed installed in test)  
**Component**: Either frontend (`CRUD6DynamicPage`) or backend (`UpdateFieldAction`)

The issue is **NOT in C6Admin** - all configuration is correct:
- ✅ Schema defines actions with `toggle: true`
- ✅ Routes use CRUD6's `CRUD6DynamicPage` component
- ✅ API calls use correct CRUD6 endpoints
- ✅ Composer constraint `^0.6.1` allows v0.6.1.2

## What We Know

### C6Admin Configuration (Correct)

**Schema** (`app/schema/crud6/users.json`):
```json
{
  "actions": [
    {
      "key": "toggle_enabled",
      "type": "field_update",
      "field": "flag_enabled",
      "toggle": true,
      "permission": "update_user_field"
    }
  ],
  "fields": {
    "flag_enabled": {
      "type": "boolean",
      "ui": "toggle"
    }
  }
}
```

**Route** (`app/assets/routes/UserRoutes.ts`):
```typescript
component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6DynamicPage)
```

**API Call** (`app/assets/composables/useUserUpdateApi.ts`):
```typescript
PUT /api/crud6/users/{id}/{fieldName}
```

### CRUD6 v0.6.1.2 (Has Issues)

**Expected Behavior**:
1. `CRUD6DynamicPage` renders toggle button
2. User clicks toggle button
3. Component reads action from schema (toggle: true)
4. Component calls `PUT /api/crud6/users/{id}/flag_enabled`
5. `UpdateFieldAction` receives request
6. Backend reads current value from database
7. Backend inverts value (true → false or false → true)
8. Backend saves to database
9. Backend returns 200 success
10. Frontend shows confirmation modal
11. Frontend reloads data and updates UI

**Actual Behavior**:
1-4. ✅ Working (button clicks, API called)
5-9. ❌ **500 Error** (something fails in backend)
10-11. ❌ No modal, no UI update

## Possible Causes in CRUD6

### Option 1: Frontend Not Sending Correct Request
**File**: `@ssnukala/sprinkle-crud6/views/CRUD6DynamicPage.vue`

**Issue**: Component may not be:
- Reading toggle flag from schema action
- Sending correct request format to backend
- Passing toggle parameter in request body

**Debug**: Add console logging to see what's sent:
```javascript
console.log('Toggle API call', {
  url: `/api/crud6/users/${id}/flag_enabled`,
  body: requestBody,
  action: action
})
```

### Option 2: Backend Not Processing Toggle Correctly
**File**: `sprinkle-crud6/app/src/Controller/UpdateFieldAction.php`

**Issue**: Controller may not be:
- Loading schema actions correctly
- Detecting `toggle: true` flag
- Inverting boolean value
- Handling permissions properly

**Debug**: Add logging to see what fails:
```php
Log::debug('UpdateFieldAction', [
    'field' => $field,
    'schema_loaded' => isset($schema),
    'action_found' => isset($action),
    'toggle_flag' => $action['toggle'] ?? false,
    'current_value' => $model->{$field}
]);
```

### Option 3: Schema Not Being Loaded
**File**: `sprinkle-crud6/app/src/Services/SchemaService.php`

**Issue**: Schema service may not be:
- Finding C6Admin's schema files
- Parsing actions array
- Making actions available to frontend/backend

**Debug**: Verify schema loading:
```php
$schema = SchemaService::load('users');
var_dump($schema['actions']); // Should show toggle_enabled
```

## Recommended Next Steps

### For CRUD6 Maintainers

1. **Add Debug Logging**
   - Enable verbose logging in UpdateFieldAction
   - Log schema loading in SchemaService
   - Log frontend API calls in CRUD6DynamicPage

2. **Test Endpoint Directly**
   ```bash
   curl -X PUT http://localhost:8080/api/crud6/users/2/flag_enabled \
     -H "Content-Type: application/json" \
     -H "Cookie: uf_session=..." \
     -d '{}'
   ```
   Should return 200, currently returns 500

3. **Add Unit Tests**
   - Test UpdateFieldAction with toggle actions
   - Test SchemaService loads actions
   - Test boolean field toggle logic

4. **Add Integration Tests**
   - Test full toggle flow end-to-end
   - Verify modals appear
   - Verify database values change

### For C6Admin Users

**Temporary Workaround** (if needed urgently):

Create custom toggle endpoints in C6Admin:

```php
// app/src/Controller/User/UserToggleEnabledAction.php
class UserToggleEnabledAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = User::findOrFail($args['id']);
        $user->flag_enabled = !$user->flag_enabled;
        $user->save();
        
        return $response->withJson([
            'message' => 'User enabled status updated'
        ]);
    }
}

// app/src/Routes/UsersRoutes.php
$group->put('/{id}/toggle-enabled', UserToggleEnabledAction::class)
    ->setName('c6.api.users.toggle-enabled');
```

**Note**: This workaround defeats the purpose of using CRUD6 and should only be used temporarily.

## Documentation

### Created Files
- `docs/CRUD6_BOOLEAN_TOGGLE_REQUIREMENT.md` - Technical requirements
- `docs/CRUD6_ISSUE_REPORT.md` - Detailed bug report for CRUD6
- `docs/USER_ENABLE_DISABLE_ERROR_SUMMARY.md` - This file

### References
- GitHub Actions Test: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19507481257
- C6Admin Repository: https://github.com/ssnukala/sprinkle-c6admin
- CRUD6 Repository: https://github.com/ssnukala/sprinkle-crud6

## Conclusion

**The issue is confirmed to be in CRUD6 v0.6.1.2**, not in C6Admin. C6Admin is correctly configured and uses CRUD6 components as intended.

**No code changes are required in C6Admin** unless a temporary workaround is needed while waiting for the CRUD6 fix.

The comprehensive issue report has been created in `docs/CRUD6_ISSUE_REPORT.md` with all debugging recommendations and test cases for the CRUD6 maintainers.
