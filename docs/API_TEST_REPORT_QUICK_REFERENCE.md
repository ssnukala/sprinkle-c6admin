# Understanding API Test Reports - Quick Reference Guide

## Reading the Test Summary

When API tests complete, you'll see a summary like this:

```
=========================================
API Test Summary
=========================================
Total tests: 45
Passed: 38
Warnings: 5
Failed: 2
Skipped: 0
```

### What Each Number Means

- **Total tests**: Number of API endpoints tested across all schemas
- **Passed**: Tests that returned expected status codes (usually 200)
- **Warnings**: Tests with permission failures (HTTP 403) - expected for some endpoints
- **Failed**: Tests with server errors (HTTP 500+) or unexpected issues
- **Skipped**: Tests marked as disabled or skip in configuration

## Understanding the Table Format

The new table format provides a quick at-a-glance overview:

```
=========================================
API Test Results by Schema and Activity (Table Format)
=========================================

| Schema     | Activity     | Pass/Fail | Status   | Message                                            |
|------------|--------------|-----------|----------|-----------------------------------------------------|
| activities | create       | FAIL      | 500      | Foreign key constraint fails                       |
| groups     | create       | PASS      | 200      | Success                                            |
| groups     | delete       | PASS      | 200      | Success                                            |
| groups     | list         | PASS      | 200      | Success                                            |
| users      | create       | FAIL      | 500      | SQLSTATE[23000]: Integrity constraint violation... |
| users      | delete       | FAIL      | 403      | Permission denied                                  |
| users      | list         | PASS      | 200      | Success                                            |
```

### Table Features

1. **Alphabetical Sorting**: Results sorted by schema name, then activity
2. **All Results**: Shows both passed and failed tests
3. **Easy Scanning**: Table format makes it easy to spot patterns
4. **Truncated Messages**: Long error messages truncated to 50 chars
5. **Copy-Paste Friendly**: Can be copied directly into reports

### Table Columns

- **Schema**: The model/schema being tested (users, groups, roles, etc.)
- **Activity**: The action/operation (list, read, create, update, delete, etc.)
- **Pass/Fail**: PASS or FAIL
- **Status**: HTTP status code (200, 403, 500, etc.)
- **Message**: Success or error description

## Understanding Error Types

### Permission Errors (Warnings - Expected)
```
Type: permission
Status: 403
Message: Permission denied
⚠️  Permission required: delete_crud6
```

**What it means:** User lacks the specific permission needed for this action  
**Action needed:** Usually none - this is expected for some schemas/actions  
**When to investigate:** If a previously passing test now shows permission error

### Database/SQL Errors (Critical - Investigate)
```
Type: database_error
Status: 500
Message: SQLSTATE[23000]: Integrity constraint violation
⚠️  DATABASE/SQL ERROR - Check schema definition
```

**What it means:** Schema definition or database constraint issue  
**Action needed:** Review schema definition and database structure  
**Common causes:**
- Missing required fields in payload
- Unique constraint violations
- Foreign key constraint violations
- Invalid field types in schema

### Server Errors (Critical - Investigate)
```
Type: server_error
Status: 500
Message: Call to undefined method UserModel::badMethod()
```

**What it means:** PHP exception or application error  
**Action needed:** Check PHP error logs for stack trace  
**Common causes:**
- Null pointer exceptions
- Type errors
- Missing dependencies
- Configuration issues

### Unexpected Status Errors (Investigate)
```
Type: unexpected_status
Status: 400
Expected: 200
```

**What it means:** Response status doesn't match expected  
**Action needed:** Review request payload and validation rules  
**Common causes:**
- Invalid payload format
- Missing required fields
- Validation rule failures
- Incorrect field types

### Exception Errors (Critical - Investigate)
```
Type: exception
Message: Connection timeout
```

**What it means:** JavaScript exception during test execution  
**Action needed:** Check network connectivity and server status  
**Common causes:**
- Network timeouts
- Server not responding
- Invalid URL
- CSRF token issues

## Reading the Failure Report by Schema

```
=========================================
API Failure Report by Schema
=========================================

📋 Schema: users
   Status: 5 passed, 2 failed
   Failed actions:
      • create:
         Type: database_error
         Status: 500
         Message: SQLSTATE[23000]: Integrity constraint violation
         ⚠️  DATABASE/SQL ERROR - Check schema definition
      • delete:
         Type: permission
         Status: 403
         Message: Permission denied
         ⚠️  Permission required: delete_crud6
```

### How to Read This

1. **Schema name**: Which model/schema had failures (e.g., "users")
2. **Status summary**: Quick overview (e.g., "5 passed, 2 failed")
3. **Failed actions**: List of operations that failed
4. **For each failure:**
   - **Action**: What operation failed (create, update, delete, etc.)
   - **Type**: Category of error (see error types above)
   - **Status**: HTTP status code received
   - **Message**: Error message from server
   - **Additional info**: Hints for fixing the issue

## Reading the Success Report by Schema

```
=========================================
API Success Report by Schema
=========================================

✅ Schema: users
   Passed actions: list, read, update, update_field, schema

✅ Schema: groups
   Passed actions: list, read, create, update, delete, schema
```

### How to Read This

- Shows which schemas had successful tests
- Lists all actions that passed for each schema
- Useful for seeing what's working correctly

## Quick Troubleshooting Checklist

When you see test failures:

- [ ] Check the **table format** for a quick overview
- [ ] Look at **error type** (permission vs database vs server)
- [ ] Review **error message** for specific details
- [ ] Check **Failure Report by Schema** for detailed breakdown
- [ ] Examine **Success Report** to see what's working
- [ ] For database errors: Check schema JSON and database structure
- [ ] For permission errors: Verify user has required role/permission
- [ ] For server errors: Check PHP error logs
- [ ] For unexpected status: Review validation rules

## Common Patterns to Look For

### All Creates Failing
```
users      | create | FAIL | 500
groups     | create | FAIL | 500
roles      | create | FAIL | 500
```
**Likely issue:** Payload generation problem or CSRF token issue

### All Deletes Return 403
```
users      | delete | FAIL | 403
groups     | delete | FAIL | 403
roles      | delete | FAIL | 403
```
**Likely issue:** Missing delete permission - expected behavior

### Specific Schema Failing All Tests
```
activities | list   | FAIL | 500
activities | create | FAIL | 500
activities | update | FAIL | 500
```
**Likely issue:** Schema definition problem or database migration not run

### Random 500 Errors
```
users      | update | FAIL | 500
roles      | create | FAIL | 500
```
**Likely issue:** Code bug - check server logs for stack traces

## What to Do When Tests Have Warnings

### Step 1: Review the Table
Scan the table format for quick overview of all results

### Step 2: Check Error Types
- **permission** → Usually expected, verify if needed
- **database_error** → Check schema definition
- **server_error** → Check PHP error logs
- **unexpected_status** → Review request payload
- **exception** → Check server connectivity

### Step 3: Prioritize Fixes
1. **Critical**: database_error, server_error, exception
2. **Review**: unexpected_status
3. **Expected**: permission (often expected)

### Step 4: Use Reports
- **Table format**: See all results at once
- **Failure report**: Get detailed error info
- **Success report**: Confirm what works

## Expected vs Unexpected Warnings

### Expected Warnings (Usually OK)
```
Type: permission
Status: 403
Message: Permission denied
```
**Why:** Not all users have all permissions  
**Expected for:** Restricted actions on production-like schemas

### Unexpected Warnings (Investigate)
```
Type: database_error
Status: 500
Message: Unknown column 'bad_field' in 'field list'
```
**Why:** Schema doesn't match database structure  
**Fix:** Update schema JSON or run migrations

## Benefits of New Approach

1. **See Everything**: All test results in one run
2. **Easy Scanning**: Table format for quick overview
3. **Detailed Info**: Schema-level breakdown when needed
4. **Non-Blocking**: Tests always complete
5. **Better Debugging**: See patterns across schemas
6. **Complete Artifacts**: All logs/screenshots generated

## Summary

**Good News:** Tests no longer fail hard - they report and continue  
**Table Format:** New feature for quick at-a-glance overview  
**Better Visibility:** See all failures across all schemas in one run  
**Actionable Reports:** Know exactly what failed, why, and where  
**Non-Blocking:** Other functionality tested even when some APIs fail  
**Complete Artifacts:** All logs and screenshots generated regardless of failures
