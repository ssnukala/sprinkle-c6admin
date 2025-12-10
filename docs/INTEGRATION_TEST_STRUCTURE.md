# C6Admin Integration Test Structure

## Overview

C6Admin's integration test **100% replicates the CRUD6 integration test structure** with minimal modifications to accommodate the C6Admin sprinkle.

## Testing Philosophy

### HTTP-Based Testing (Not PHPUnit)

**Why HTTP Testing?**
- CRUD6 sprinkle has comprehensive PHPUnit integration tests for testing CRUD6's own code
- C6Admin verifies that APIs work via HTTP requests (as the frontend does)
- Tests the complete stack: routing, authentication, authorization, responses
- Matches real-world usage - how Vue.js frontend actually uses the APIs

**What We Don't Test:**
- ❌ No PHPUnit tests in C6Admin (CRUD6 already has them)
- ❌ No duplication of CRUD6's internal logic tests
- ❌ No schema generation tests (those are for CRUD6 development)

**What We Do Test:**
- ✅ All CRUD6 API endpoints via HTTP (unauthenticated + authenticated)
- ✅ All C6Admin frontend pages (screenshots + network tracking)
- ✅ Interactive UI elements (buttons, modals, forms)
- ✅ Authentication flow (login, session management)
- ✅ Complete integration (C6Admin + CRUD6 + UserFrosting 6)

## Scripts - 100% From CRUD6

All critical scripts are **exact copies** from CRUD6:

### Core Testing Scripts
| Script | Purpose | Source | Identical? |
|--------|---------|--------|------------|
| `test-paths.php` | Tests API endpoints via HTTP | CRUD6 | ✅ Yes |
| `take-screenshots-with-tracking.js` | Login + screenshots + API tests | CRUD6 | ✅ Yes |
| `run-seeds.php` | Modular database seeding | CRUD6 | ✅ Yes |
| `check-seeds-modular.php` | Seed data validation | CRUD6 | ✅ Yes |
| `test-seed-idempotency-modular.php` | Idempotency testing | CRUD6 | ✅ Yes |

### Additional Scripts
| Script | Purpose | Source |
|--------|---------|--------|
| `test-user-detail-buttons.js` | Button interaction testing | C6Admin |
| `capture-php-error-logs.sh` | PHP error log capture | CRUD6 |

## Configuration Files

### integration-test-paths.json

Defines all paths to test:

**Structure:**
```json
{
  "config": {
    "base_url": "http://localhost:8080",
    "auth": {
      "username": "admin",
      "password": "admin123"
    }
  },
  "paths": {
    "authenticated": {
      "api": { /* Authenticated API endpoints */ },
      "frontend": { /* Frontend pages requiring login */ }
    },
    "unauthenticated": {
      "api": { /* API endpoints without auth - should return 401 */ },
      "frontend": { /* Frontend pages without auth - should redirect */ }
    }
  }
}
```

**Current Coverage:**

**Unauthenticated API Tests (19 total):**
- C6Admin Dashboard API: 1 endpoint (`/api/c6/dashboard`)
- Users CRUD6 API: 6 endpoints (schema, list, single, create, update, delete)
- Groups CRUD6 API: 3 endpoints (schema, list, single)
- Roles CRUD6 API: 3 endpoints (schema, list, single)
- Permissions CRUD6 API: 3 endpoints (schema, list, single)
- Activities CRUD6 API: 3 endpoints (schema, list, single)

**Authenticated API Tests:**
- All CRUD operations for all models
- Relationship management (attach/detach)
- Field-level updates
- Custom actions
- Nested relationship endpoints

**Frontend Tests:**
- Dashboard, Users, Groups, Roles, Permissions, Activities pages
- Detail pages for each model
- Configuration page

### integration-test-seeds.json

Defines seeds to run and validation:

```json
{
  "seeds": [
    {
      "sprinkle": "Account",
      "class": "DefaultGroups",
      "description": "Create default user groups"
    }
    // ... more seeds
  ],
  "validation": {
    "groups": { "min": 2 },
    "users": { "min": 1 }
    // ... validation rules
  }
}
```

## Test Flow

### Complete Test Sequence

1. **Setup Phase**
   - Setup PHP 8.1, Node.js 20
   - Create UserFrosting project
   - Install C6Admin + CRUD6 dependencies
   - Configure sprinkles (MyApp.php, router, main.ts)

2. **Database Phase**
   - Run migrations
   - Seed database (modular approach)
   - Validate seed data
   - Test seed idempotency
   - Create admin user

3. **Server Phase**
   - Build frontend assets
   - Start PHP server (`php bakery serve`)
   - Start Vite dev server (`php bakery assets:vite`)

4. **Testing Phase**
   ```
   a) Test Unauthenticated APIs
      ├─ Run: php test-paths.php integration-test-paths.json unauth api
      ├─ Tests: 19 API endpoints
      └─ Expected: All return 401 or 403
   
   b) Test Unauthenticated Frontend
      ├─ Run: php test-paths.php integration-test-paths.json unauth frontend
      ├─ Tests: C6Admin pages
      └─ Expected: Redirect to login
   
   c) Test Authenticated APIs + Screenshots
      ├─ Run: node take-screenshots-with-tracking.js integration-test-paths.json
      ├─ Actions:
      │  ├─ Login with admin credentials
      │  ├─ Take screenshots of all pages
      │  ├─ Test all authenticated API endpoints
      │  └─ Track network requests
      └─ Expected: All APIs return 200, screenshots captured
   
   d) Test Button Interactions
      ├─ Run: node test-user-detail-buttons.js
      ├─ Tests: Edit, Reset Password, Disable, Delete buttons
      └─ Expected: All buttons work, modals appear
   ```

5. **Artifact Collection**
   - Screenshots
   - Network request summary
   - Browser console errors
   - PHP error logs
   - Button test results

## Key Differences from CRUD6

| Aspect | CRUD6 | C6Admin |
|--------|-------|---------|
| **PHPUnit Tests** | ✅ Has comprehensive suite | ❌ Skipped (uses CRUD6's tests) |
| **Schema Generation** | ✅ Generates tests from schemas | ❌ Not needed |
| **HTTP API Tests** | ✅ Via test-paths.php | ✅ Same script |
| **Screenshot Tests** | ✅ Via take-screenshots-with-tracking.js | ✅ Same script |
| **Models Tested** | users, groups, roles, permissions, activities | Same + C6Admin dashboard |
| **Test Approach** | Tests CRUD6 code directly | Tests C6Admin + CRUD6 integration |

## Maintenance

### Adding New API Endpoints

1. **Add to integration-test-paths.json:**
   ```json
   {
     "unauthenticated": {
       "api": {
         "new_endpoint": {
           "method": "GET",
           "path": "/api/crud6/newmodel",
           "description": "Test new endpoint",
           "expected_status": 401,
           "validation": { "type": "status_only" }
         }
       }
     },
     "authenticated": {
       "api": {
         "new_endpoint_auth": {
           "method": "GET",
           "path": "/api/crud6/newmodel",
           "description": "Get new model data",
           "expected_status": 200,
           "validation": {
             "type": "json",
             "contains": ["rows"]
           }
         }
       }
     }
   }
   ```

2. **No code changes needed** - scripts are data-driven!

### Adding New Frontend Pages

1. **Add to integration-test-paths.json:**
   ```json
   {
     "authenticated": {
       "frontend": {
         "new_page": {
           "path": "/c6/admin/newpage",
           "description": "New admin page",
           "screenshot": true,
           "screenshot_name": "c6admin_newpage"
         }
       }
     }
   }
   ```

2. **Screenshot automatically captured** on next run!

## Troubleshooting

### Common Issues

**Issue: "Login not working"**
- Check: Admin user created successfully
- Check: Credentials in integration-test-paths.json match (`admin` / `admin123`)
- Check: Session cookies being saved by Playwright
- Check: CSRF token extracted from login page

**Issue: "Unauthenticated tests failing"**
- Expected: All should return 401 or 403
- Check: AuthGuard middleware applied to all CRUD6 routes
- Review: test-paths.php warning vs failure logic

**Issue: "Screenshots failing"**
- Check: Vite server running and accessible
- Check: PHP server running on port 8080
- Check: No JavaScript errors in console
- Check: Network requests completing successfully

**Issue: "Authenticated API tests not running"**
- Check: Login successful in screenshot script
- Check: CSRF token extracted
- Check: Session cookies preserved across requests

## Success Criteria

A successful integration test run will:

✅ All 19 unauthenticated API tests return 401/403 (pass or warn)
✅ All authenticated API tests return 200 (pass)
✅ All frontend pages load successfully (200)
✅ All screenshots captured without errors
✅ Network tracking report generated
✅ No JavaScript console errors
✅ No PHP fatal errors in logs
✅ Button interactions work correctly

## References

- CRUD6 Integration Test: https://github.com/ssnukala/sprinkle-crud6/blob/main/.github/workflows/integration-test.yml
- CRUD6 Test Scripts: https://github.com/ssnukala/sprinkle-crud6/tree/main/.github/scripts
- CRUD6 Test Config: https://github.com/ssnukala/sprinkle-crud6/tree/main/.github/config
