# Modular Integration Testing Framework for C6Admin

This document provides complete documentation for the modular integration testing framework used in sprinkle-c6admin.

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Configuration Files](#configuration-files)
4. [Testing Scripts](#testing-scripts)
5. [GitHub Actions Workflow](#github-actions-workflow)
6. [Login Authentication Fix](#login-authentication-fix)
7. [Customization Guide](#customization-guide)
8. [Troubleshooting](#troubleshooting)

## Overview

The modular integration testing framework is a **configuration-driven** approach to testing UserFrosting 6 sprinkles. It was developed for sprinkle-crud6 and adapted for C6Admin.

### Key Benefits

- **Configuration-Driven**: Define tests in JSON, not code
- **Reusable Scripts**: Same testing scripts work across all sprinkles
- **Self-Documenting**: JSON structure explains what's being tested
- **Template-Based**: Easy to adapt for new sprinkles
- **Consistent**: Same approach everywhere
- **Maintainable**: Update config files, not workflow code
- **Proven**: Uses working login authentication from CRUD6

### Migration from Old Approach

The old integration testing files have been backed up to `.github/backup/`:

| Old File | Issue | New File | Improvement |
|----------|-------|----------|-------------|
| `integration-test.yml` | Hardcoded test logic | `integration-test-modular.yml` | Configuration-driven |
| `check-seeds.php` | C6Admin-specific | `check-seeds-modular.php` | Works for any sprinkle |
| `test-seed-idempotency.php` | Hardcoded validation | `test-seed-idempotency-modular.php` | Config-based validation |
| `take-authenticated-screenshots.js` | Login not working | `take-screenshots-modular.js` | Working login from CRUD6 |

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                  MODULAR TESTING FRAMEWORK                   │
└─────────────────────────────────────────────────────────────┘

┌──────────────────────────┐     ┌──────────────────────────┐
│  Configuration Files     │     │  Reusable Scripts        │
│  (.github/config/)       │────▶│  (.github/scripts/)      │
├──────────────────────────┤     ├──────────────────────────┤
│ • integration-test-      │     │ • run-seeds.php          │
│   seeds.json             │     │ • check-seeds-modular.php│
│ • integration-test-      │     │ • test-seed-idempotency- │
│   paths.json             │     │   modular.php            │
│ • template-*.json        │     │ • test-paths.php         │
│                          │     │ • take-screenshots-      │
│                          │     │   modular.js             │
└──────────────────────────┘     └──────────────────────────┘
                │                            │
                └────────────┬───────────────┘
                             ▼
                ┌──────────────────────────┐
                │  GitHub Actions Workflow │
                │  (integration-test-      │
                │   modular.yml)           │
                └──────────────────────────┘
```

## Configuration Files

### Seeds Configuration (`integration-test-seeds.json`)

Defines which database seeds to run, their execution order, and validation rules.

**Full Structure:**

```json
{
  "description": "Integration test seeds configuration for C6Admin sprinkle",
  "seeds": {
    "sprinkle_name": {
      "description": "Human-readable description",
      "order": 1,
      "seeds": [
        {
          "class": "Fully\\Qualified\\Seed\\ClassName",
          "description": "What this seed does",
          "required": true,
          "validation": {
            "type": "role|permissions|groups|users|custom",
            "slug": "role-slug",
            "slugs": ["perm1", "perm2"],
            "usernames": ["user1", "user2"],
            "expected_count": 1,
            "role_assignments": {
              "role-slug": 6
            }
          }
        }
      ]
    }
  },
  "validation": {
    "idempotency": {
      "enabled": true,
      "description": "Test that seeds can run multiple times",
      "test_seeds": ["sprinkle_name"]
    },
    "relationships": {
      "enabled": true,
      "description": "Validate permission assignments"
    }
  },
  "admin_user": {
    "enabled": true,
    "username": "admin",
    "password": "admin123",
    "email": "admin@example.com",
    "firstName": "Admin",
    "lastName": "User",
    "description": "Admin user for authenticated tests"
  }
}
```

**Field Reference:**

| Field | Type | Description |
|-------|------|-------------|
| `order` | integer | Execution order (lower numbers run first) |
| `class` | string | Fully qualified seed class name |
| `required` | boolean | If true, failure stops execution |
| `validation.type` | string | Type of validation (role, permissions, groups, users, custom) |
| `validation.slug` | string | For single item validation (role slug) |
| `validation.slugs` | array | For multiple items (permission slugs) |
| `validation.expected_count` | integer | Expected number of records |
| `validation.role_assignments` | object | Expected permission counts per role |

**C6Admin Example:**

The C6Admin configuration includes:
- **Account seeds** (order: 1): Base UserFrosting data
- **CRUD6 seeds** (order: 2): Required dependency
- **C6Admin seeds** (order: 3): Test data

### Paths Configuration (`integration-test-paths.json`)

Defines API endpoints and frontend routes to test, plus screenshot configuration.

**Full Structure:**

```json
{
  "description": "Integration test paths configuration",
  "paths": {
    "authenticated": {
      "api": {
        "endpoint_name": {
          "method": "GET|POST|PUT|DELETE",
          "path": "/api/path",
          "description": "What this endpoint does",
          "expected_status": 200,
          "validation": {
            "type": "json|status_only",
            "contains": ["field1", "field2"]
          },
          "skip": false,
          "skip_reason": "Why skipped"
        }
      },
      "frontend": {
        "page_name": {
          "path": "/frontend/path",
          "description": "Page description",
          "screenshot": true,
          "screenshot_name": "unique_filename",
          "skip": false,
          "skip_reason": "Why skipped"
        }
      }
    },
    "unauthenticated": {
      "api": {
        "endpoint_name": {
          "method": "GET",
          "path": "/api/path",
          "description": "Should return 401",
          "expected_status": 401,
          "validation": {
            "type": "status_only"
          }
        }
      },
      "frontend": {
        "page_name": {
          "path": "/frontend/path",
          "description": "Should redirect to login",
          "expected_status": 200,
          "validation": {
            "type": "redirect_to_login",
            "contains": ["/account/sign-in"]
          }
        }
      }
    }
  },
  "config": {
    "base_url": "http://localhost:8080",
    "auth": {
      "username": "admin",
      "password": "admin123"
    },
    "timeout": {
      "api": 10,
      "frontend": 30
    }
  }
}
```

**Field Reference:**

| Field | Type | Description |
|-------|------|-------------|
| `method` | string | HTTP method (GET, POST, PUT, DELETE) |
| `path` | string | Route path (must start with /) |
| `expected_status` | integer | Expected HTTP status code |
| `alternative_statuses` | array | Alternative acceptable status codes (optional) |
| `validation.type` | string | json, status_only, redirect_to_login |
| `validation.contains` | array | Fields/strings that should be in response |
| `screenshot` | boolean | Whether to capture screenshot |
| `screenshot_name` | string | Filename (without extension) |
| `skip` | boolean | Whether to skip this test |
| `timeout.api` | integer | API request timeout in seconds |
| `timeout.frontend` | integer | Frontend page load timeout in seconds |

**C6Admin Example:**

The C6Admin configuration tests:
- **C6Admin Dashboard API** (`/api/c6/dashboard`)
- **CRUD6 APIs** (users, groups, roles, permissions, activities)
- **All C6Admin Pages** (12 pages with screenshots)
- **Authentication** (tests with and without login)

**Alternative Status Codes:**

For authentication tests, you can specify multiple acceptable status codes using `alternative_statuses`:

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

This is useful when an endpoint might return either:
- `403 Forbidden` (user is authenticated but lacks permission)
- `401 Unauthorized` (user is not authenticated)

The test will pass if the response matches either the `expected_status` or any status in `alternative_statuses`.

## Testing Scripts

All scripts are in `.github/scripts/` and accept a configuration file as the first parameter.

### run-seeds.php

**Purpose:** Run database seeds based on JSON configuration.

**Usage:**
```bash
php run-seeds.php <config_file> [sprinkle_filter]
```

**Examples:**
```bash
# Run all seeds
php run-seeds.php integration-test-seeds.json

# Run only C6Admin seeds
php run-seeds.php integration-test-seeds.json c6admin
```

**What it does:**
1. Loads seed configuration from JSON
2. Sorts sprinkles by order number
3. Executes each seed using `php bakery seed`
4. Stops if a required seed fails
5. Reports success/failure summary

**Exit codes:**
- `0`: All seeds succeeded
- `1`: A required seed failed or config error

### check-seeds-modular.php

**Purpose:** Validate that seeds created expected data.

**Usage:**
```bash
php check-seeds-modular.php <config_file>
```

**Examples:**
```bash
php check-seeds-modular.php integration-test-seeds.json
```

**What it does:**
1. Loads seed configuration
2. For each seed with validation:
   - **role**: Check role exists and count matches
   - **permissions**: Check permissions exist and are assigned to roles
   - **groups**: Check groups exist
   - **users**: Check users exist
3. Validates permission assignments to roles
4. Exits with error if any validation fails

**Exit codes:**
- `0`: All validations passed
- `1`: Validation failed or config error

### test-seed-idempotency-modular.php

**Purpose:** Test that seeds can be run multiple times without creating duplicates.

**Usage:**
```bash
php test-seed-idempotency-modular.php <config_file>
```

**Examples:**
```bash
php test-seed-idempotency-modular.php integration-test-seeds.json
```

**What it does:**
1. Counts records for configured seeds
2. Re-runs the seeds
3. Counts records again
4. Fails if counts changed (indicates duplicates)

**Important:** This only tests sprinkles listed in `validation.idempotency.test_seeds`.

**Exit codes:**
- `0`: Seeds are idempotent
- `1`: Counts changed (duplicates created) or config error

### test-paths.php

**Purpose:** Test API endpoints and frontend routes.

**Usage:**
```bash
php test-paths.php <config_file> <auth_mode> <path_type>
```

**Parameters:**
- `auth_mode`: `auth` or `unauth`
- `path_type`: `api` or `frontend`

**Examples:**
```bash
# Test unauthenticated API endpoints (should return 401)
php test-paths.php integration-test-paths.json unauth api

# Test authenticated API endpoints
php test-paths.php integration-test-paths.json auth api

# Test frontend pages without login (should redirect)
php test-paths.php integration-test-paths.json unauth frontend

# Test frontend pages with login
php test-paths.php integration-test-paths.json auth frontend
```

**What it does:**
1. Loads path configuration
2. For each configured path:
   - Makes HTTP request (with or without auth)
   - Validates HTTP status code
   - Validates response content (if configured)
3. Reports success/failure for each path
4. Provides summary

**Validation types:**
- `json`: Response is JSON and contains specified fields
- `status_only`: Only check HTTP status code
- `redirect_to_login`: Check URL contains login path

**Exit codes:**
- `0`: All paths passed
- `1`: Some paths failed or config error

### take-screenshots-modular.js

**Purpose:** Capture screenshots of frontend pages using Playwright.

**Usage:**
```bash
node take-screenshots-modular.js <config_file> [base_url] [username] [password]
```

**Examples:**
```bash
# Use config file settings
node take-screenshots-modular.js integration-test-paths.json

# Override credentials
node take-screenshots-modular.js integration-test-paths.json http://localhost:8080 admin admin123
```

**What it does:**
1. Loads path configuration
2. Launches Playwright browser
3. Navigates to login page
4. **Authenticates using working CRUD6 login logic**
5. For each frontend path with `screenshot: true`:
   - Navigates to the page
   - Waits for content to load
   - Captures full-page screenshot
   - Saves to `/tmp/screenshot_{name}.png`
6. Reports success/failure summary

**Screenshot files:**
- Location: `/tmp/screenshot_{screenshot_name}.png`
- Format: PNG
- Type: Full page

**Important:** This script uses the **proven working login** from sprinkle-crud6 that properly handles session authentication with `.uk-card` selectors.

**Exit codes:**
- `0`: All screenshots captured
- `1`: Some screenshots failed or config error

## GitHub Actions Workflow

The modular workflow (`.github/workflows/integration-test-modular.yml`) orchestrates all tests.

**Key Steps:**

```yaml
# 1. Seed database
- name: Seed database (Modular)
  run: |
    cd userfrosting
    cp ../sprinkle-c6admin/.github/config/integration-test-seeds.json .
    cp ../sprinkle-c6admin/.github/scripts/run-seeds.php .
    php run-seeds.php integration-test-seeds.json

# 2. Validate seeds
- name: Validate seed data (Modular)
  run: |
    cd userfrosting
    cp ../sprinkle-c6admin/.github/scripts/check-seeds-modular.php .
    php check-seeds-modular.php integration-test-seeds.json

# 3. Test idempotency
- name: Test seed idempotency (Modular)
  run: |
    cd userfrosting
    cp ../sprinkle-c6admin/.github/scripts/test-seed-idempotency-modular.php .
    php test-seed-idempotency-modular.php integration-test-seeds.json

# 4. Test paths
- name: Test API paths - Unauthenticated (Modular)
  run: |
    cd userfrosting
    cp ../sprinkle-c6admin/.github/config/integration-test-paths.json .
    cp ../sprinkle-c6admin/.github/scripts/test-paths.php .
    php test-paths.php integration-test-paths.json unauth api

# 5. Take screenshots
- name: Take screenshots of C6Admin pages (Modular)
  run: |
    cd userfrosting
    cp ../sprinkle-c6admin/.github/scripts/take-screenshots-modular.js .
    node take-screenshots-modular.js integration-test-paths.json
```

**Benefits over old workflow:**
- ✅ 73 fewer lines of code
- ✅ No hardcoded seed classes
- ✅ No hardcoded paths
- ✅ Easy to add new tests (just edit JSON)
- ✅ Consistent with other sprinkles

## Login Authentication Fix

### The Problem

The old `take-authenticated-screenshots.js` had login issues. Screenshots showed the login page instead of the actual pages, indicating authentication wasn't working.

### The Solution

We now use `take-screenshots-modular.js` from sprinkle-crud6, which has **proven working authentication**.

**Key fixes:**
1. **Proper selector targeting**: Uses `.uk-card` to target the main login form, not header dropdown
2. **Correct navigation handling**: Properly waits for login navigation
3. **Session persistence**: Correctly maintains authenticated session across page navigations

**Working selectors:**
```javascript
// Target the MAIN login form (not header)
await page.waitForSelector('.uk-card input[data-test="username"]');
await page.fill('.uk-card input[data-test="username"]', username);
await page.fill('.uk-card input[data-test="password"]', password);
await page.click('.uk-card button[data-test="submit"]');
```

**Why it matters:**
- UserFrosting 6 has login forms in TWO places:
  1. Header dropdown (for quick login when on public pages)
  2. Main body card (on `/account/sign-in` page)
- Without `.uk-card` qualifier, the script might target the wrong form
- The working CRUD6 version has been tested in CI and works reliably

### Verification

The working login is verified by:
- ✅ Screenshots show actual pages, not login page
- ✅ `currentUrl` checks don't show `/account/sign-in`
- ✅ Tests pass in CRUD6 CI
- ✅ Same script works in C6Admin CI

## Customization Guide

### For C6Admin Developers

To modify C6Admin tests:

1. **Add new seeds**: Edit `.github/config/integration-test-seeds.json`
2. **Add new paths**: Edit `.github/config/integration-test-paths.json`
3. **No script changes needed**: Scripts read from config

### For Other Sprinkles

To use this framework in your sprinkle:

1. **Copy template files:**
   ```bash
   cp .github/config/template-integration-test-paths.json mysprinkle-paths.json
   cp .github/config/template-integration-test-seeds.json mysprinkle-seeds.json
   ```

2. **Customize for your sprinkle:**
   - Replace `yoursprinkle` with your sprinkle name
   - Replace `yourmodel` with your model names
   - Update seed classes
   - Update API paths
   - Update frontend routes

3. **Copy scripts** (they're already generic)

4. **Update workflow** to use your config files

See `QUICK_START_GUIDE.md` for detailed customization steps.

## Troubleshooting

### Login not working / screenshots show login page

**Solution:** Make sure you're using `take-screenshots-modular.js`, not the old `take-authenticated-screenshots.js`. The modular version has working authentication.

**Check:**
```bash
# Should see modular script
ls -l .github/scripts/take-screenshots-modular.js

# Should have .uk-card selectors
grep ".uk-card" .github/scripts/take-screenshots-modular.js
```

### Seed validation fails

**Common causes:**
1. Wrong slug in validation config
2. Wrong expected count
3. Seed didn't run successfully
4. Role assignments missing

**Debug:**
```bash
# Check what was actually created
mysql -u root -p userfrosting_test
> SELECT * FROM roles WHERE slug LIKE '%your-slug%';
> SELECT * FROM permissions WHERE slug LIKE '%your-slug%';
```

### Path tests timeout

**Causes:**
1. Servers not running
2. Frontend not built
3. Network issues

**Solution:**
```bash
# Make sure servers are running
ps aux | grep "bakery serve"
ps aux | grep "vite"

# Check if accessible
curl http://localhost:8080
```

### Idempotency test fails

**Cause:** Seeds create duplicates when run multiple times.

**Solution:** Use `firstOrCreate()` or similar in your seeds:
```php
Role::firstOrCreate(
    ['slug' => 'my-role'],
    ['name' => 'My Role', ...]
);
```

### Configuration file not found

**Solution:** Make sure you're running from the correct directory and config file path is correct.

```bash
# Check current directory
pwd

# Should be in UserFrosting project root
# Config file should be copied there from sprinkle
ls -l integration-test-*.json
```

## Reference

### File Locations

```
.github/
├── backup/                      # Backup of old files
│   ├── integration-test.yml
│   ├── check-seeds.php
│   ├── test-seed-idempotency.php
│   └── take-authenticated-screenshots.js
├── config/                      # Configuration files
│   ├── integration-test-paths.json
│   ├── integration-test-seeds.json
│   ├── template-integration-test-paths.json
│   └── template-integration-test-seeds.json
├── scripts/                     # Reusable testing scripts
│   ├── run-seeds.php
│   ├── check-seeds-modular.php
│   ├── test-seed-idempotency-modular.php
│   ├── test-paths.php
│   └── take-screenshots-modular.js
├── workflows/                   # GitHub Actions workflows
│   ├── integration-test.yml     # Old workflow
│   └── integration-test-modular.yml  # New modular workflow
├── QUICK_START_GUIDE.md        # Quick start guide
└── MODULAR_TESTING_README.md   # This file
```

### Related Documentation

- **Quick Start**: `.github/QUICK_START_GUIDE.md`
- **Framework Overview**: Based on sprinkle-crud6 `.github/FRAMEWORK_OVERVIEW.txt`
- **Templates**: `.github/config/template-*.json`
- **Examples**: `.github/config/integration-test-*.json`

### Support

For issues or questions:
1. Check this documentation
2. Review the configuration files
3. Compare with working CRUD6 examples
4. Check GitHub Issues on ssnukala/sprinkle-c6admin
5. Check GitHub Issues on ssnukala/sprinkle-crud6 (for framework issues)

---

**Version:** 1.0.0  
**Updated:** November 2025  
**Based on:** sprinkle-crud6 modular testing framework  
**Key fix:** Working login authentication from CRUD6
