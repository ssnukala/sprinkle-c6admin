# Alternative Status Codes Fix Summary

## Issue
GitHub Actions CI test failure in workflow run [#20112295714](https://github.com/ssnukala/sprinkle-c6admin/actions/runs/20112295714/job/57712608097)

**Test:** `login_required`  
**Description:** Dashboard API should require authentication  
**Method:** GET  
**Path:** `/api/c6/dashboard`  
**Expected:** 403 (Forbidden)  
**Actual:** 401 (Unauthorized)  
**Result:** ❌ FAILED

## Problem Analysis

The `login_required` test was checking that unauthenticated access to the dashboard API returns a 403 status code. However, the API was returning 401 (Unauthorized), which is also a valid and correct response for unauthenticated requests.

According to HTTP standards:
- **401 Unauthorized**: The request has not been authenticated
- **403 Forbidden**: The server understood the request but refuses to authorize it

Both status codes are valid for unauthenticated requests, with the difference being:
- 401 is returned when authentication has not been attempted or failed
- 403 is returned when authentication succeeded but the user lacks permission

## Solution

Implemented support for **alternative acceptable status codes** in the integration test framework.

### Changes Made

#### 1. Enhanced `test-paths.php` Script

**File:** `.github/scripts/test-paths.php`

Added support for `alternative_statuses` configuration field:

```php
// Read alternative_statuses from config
$alternativeStatuses = $pathConfig['alternative_statuses'] ?? [];

// Check if status matches expected or any alternative
$statusMatches = ($httpCode == $expectedStatus);

if (!$statusMatches && !empty($alternativeStatuses)) {
    foreach ($alternativeStatuses as $altStatus) {
        if ($httpCode == $altStatus) {
            $statusMatches = true;
            break;
        }
    }
}

// Enhanced output messages
if (!empty($alternativeStatuses)) {
    $allStatuses = array_merge([$expectedStatus], $alternativeStatuses);
    echo "Status: {$httpCode} (expected " . implode(' or ', $allStatuses) . ")\n";
}
```

#### 2. Updated Test Configuration

**File:** `.github/config/integration-test-paths.json`

Updated the `login_required` test to accept both 401 and 403:

```json
{
  "login_required": {
    "method": "GET",
    "path": "/api/c6/dashboard",
    "description": "Dashboard API should require authentication",
    "expected_status": 403,
    "alternative_statuses": [401]
  }
}
```

#### 3. Updated Documentation

**Files:**
- `.github/MODULAR_TESTING_README.md`
- `.github/config/template-integration-test-paths.json`

Added documentation for the new `alternative_statuses` field:

**Field Reference:**
- `alternative_statuses` (array, optional): Alternative acceptable status codes

**Example Usage:**
```json
{
  "test_name": {
    "expected_status": 403,
    "alternative_statuses": [401]
  }
}
```

This allows tests to accept either the primary expected status or any alternative status.

## Verification

### Test Logic Validation

Created test script to verify the logic works correctly:

```bash
# Test scenarios
403 response -> ✅ PASS (matches expected_status)
401 response -> ✅ PASS (matches alternative_statuses[0])
500 response -> ❌ FAIL (matches neither)
```

### Files Validated

All modified files passed validation:
- ✅ PHP syntax check: `test-paths.php` - No errors
- ✅ JSON validation: `integration-test-paths.json` - Valid
- ✅ JSON validation: `template-integration-test-paths.json` - Valid

## Impact

### What Changed
- Test framework now supports multiple acceptable HTTP status codes
- Dashboard authentication test accepts both 401 and 403
- Template and documentation updated to show best practices

### What Didn't Change
- No changes to application code
- No changes to API behavior
- No changes to authentication/authorization logic
- Only test infrastructure was modified

### Backward Compatibility
- Fully backward compatible
- Tests without `alternative_statuses` work as before
- Existing tests are not affected

## Related Issues

This fix aligns with the CRUD6 testing pattern where 401 is an acceptable response for unauthenticated requests:

**CRUD6 Test Results:**
```
Testing: users_schema
   ✅ Status: 401 (expected 401)
   ✅ PASSED

Testing: users_list
   ✅ Status: 401 (expected 401)
   ✅ PASSED
```

## Commits

1. `ce0d264` - Add support for alternative status codes in path testing and accept 401 or 403 for dashboard auth test
2. `9d112d4` - Update documentation for alternative_statuses feature in path testing

## Testing Recommendations

When creating new authentication tests:

1. **For unauthenticated API requests**, use:
   ```json
   {
     "expected_status": 403,
     "alternative_statuses": [401]
   }
   ```

2. **For authenticated but unauthorized requests**, use:
   ```json
   {
     "expected_status": 403
   }
   ```

3. **For successful requests**, use:
   ```json
   {
     "expected_status": 200
   }
   ```

## Conclusion

The fix resolves the CI test failure by properly handling both 401 and 403 status codes for authentication tests. This matches industry best practices and aligns with the CRUD6 sprinkle's testing approach.

**Result:** CI tests will now pass when the dashboard API returns either 401 or 403 for unauthenticated requests.
