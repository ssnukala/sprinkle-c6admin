# Quick Start Guide: Modular Integration Testing for C6Admin

This guide explains how to use and customize the modular integration testing framework for C6Admin.

## Overview

C6Admin now uses a **modular, configuration-driven integration testing framework** based on the approach from sprinkle-crud6. This makes testing easier to maintain and customize.

## How It Works

Instead of hardcoding test logic in workflow files, we use:

1. **JSON Configuration Files** - Define what to test
2. **Reusable PHP/JS Scripts** - Run the tests based on configuration
3. **GitHub Actions Workflow** - Orchestrate the tests

## Quick Start

### Running Tests Locally

```bash
# In your UserFrosting project root (after setting up C6Admin)

# 1. Copy configuration files
cp vendor/ssnukala/sprinkle-c6admin/.github/config/integration-test-seeds.json .
cp vendor/ssnukala/sprinkle-c6admin/.github/config/integration-test-paths.json .

# 2. Run seeds
php vendor/ssnukala/sprinkle-c6admin/.github/scripts/run-seeds.php integration-test-seeds.json

# 3. Validate seeds
php vendor/ssnukala/sprinkle-c6admin/.github/scripts/check-seeds-modular.php integration-test-seeds.json

# 4. Test paths (requires running servers)
php vendor/ssnukala/sprinkle-c6admin/.github/scripts/test-paths.php integration-test-paths.json unauth api

# 5. Take screenshots (requires Playwright and running servers)
node vendor/ssnukala/sprinkle-c6admin/.github/scripts/take-screenshots-modular.js integration-test-paths.json
```

## Configuration Files

### `.github/config/integration-test-seeds.json`

Defines which database seeds to run and how to validate them.

**Structure:**
```json
{
  "seeds": {
    "sprinkle_name": {
      "description": "Description of sprinkle seeds",
      "order": 1,
      "seeds": [
        {
          "class": "Full\\Seed\\Class\\Name",
          "description": "What this seed does",
          "required": true,
          "validation": {
            "type": "role",
            "slug": "role-slug",
            "expected_count": 1
          }
        }
      ]
    }
  },
  "validation": {
    "idempotency": {
      "enabled": true,
      "test_seeds": ["sprinkle_name"]
    }
  },
  "admin_user": {
    "enabled": true,
    "username": "admin",
    "password": "admin123"
  }
}
```

**For C6Admin:**
- Seeds are organized by sprinkle (account, crud6, c6admin)
- Each seed specifies its class, description, and validation rules
- Idempotency testing ensures seeds can run multiple times
- Admin user configuration for authenticated tests

### `.github/config/integration-test-paths.json`

Defines which API endpoints and frontend routes to test, plus which pages to screenshot.

**Structure:**
```json
{
  "paths": {
    "authenticated": {
      "api": {
        "endpoint_name": {
          "method": "GET",
          "path": "/api/your/path",
          "expected_status": 200,
          "validation": {
            "type": "json",
            "contains": ["field1", "field2"]
          }
        }
      },
      "frontend": {
        "page_name": {
          "path": "/your/page",
          "description": "Page description",
          "screenshot": true,
          "screenshot_name": "unique_name"
        }
      }
    },
    "unauthenticated": {
      "api": { /* Same structure, tests without auth */ },
      "frontend": { /* Same structure, should redirect to login */ }
    }
  },
  "config": {
    "base_url": "http://localhost:8080",
    "auth": {
      "username": "admin",
      "password": "admin123"
    }
  }
}
```

**For C6Admin:**
- Tests C6Admin dashboard API (`/api/c6/dashboard`)
- Tests CRUD6 API endpoints (`/api/crud6/users`, `/api/crud6/groups`, etc.)
- Tests all C6Admin frontend routes (`/c6/admin/*`)
- Captures screenshots of all 12 C6Admin pages

## Testing Scripts

All scripts are located in `.github/scripts/` and are reusable across sprinkles.

### `run-seeds.php`

Runs database seeds based on JSON configuration.

```bash
# Run all seeds
php run-seeds.php integration-test-seeds.json

# Run only specific sprinkle seeds
php run-seeds.php integration-test-seeds.json crud6
```

**What it does:**
- Reads seed configuration from JSON
- Executes seeds in correct order (by sprinkle order number)
- Stops if a required seed fails
- Reports success/failure for each seed

### `check-seeds-modular.php`

Validates that seeds created the expected data.

```bash
php check-seeds-modular.php integration-test-seeds.json
```

**What it does:**
- Validates roles exist (checks slug and count)
- Validates permissions exist (checks slugs and count)
- Validates permission assignments to roles
- Validates groups and users (if configured)
- Exits with error if validation fails

### `test-seed-idempotency-modular.php`

Tests that seeds can be run multiple times without creating duplicates.

```bash
php test-seed-idempotency-modular.php integration-test-seeds.json
```

**What it does:**
- Counts records before re-running seeds
- Re-runs configured seeds
- Counts records after re-running
- Fails if counts changed (indicates duplicates created)

### `test-paths.php`

Tests API endpoints and frontend routes.

```bash
# Test unauthenticated API paths
php test-paths.php integration-test-paths.json unauth api

# Test unauthenticated frontend paths
php test-paths.php integration-test-paths.json unauth frontend

# Test authenticated API paths
php test-paths.php integration-test-paths.json auth api

# Test authenticated frontend paths
php test-paths.php integration-test-paths.json auth frontend
```

**What it does:**
- Tests HTTP status codes match expected values
- Validates JSON responses contain expected fields
- Validates redirects work correctly
- Tests with and without authentication

### `take-screenshots-modular.js`

Captures screenshots of frontend pages using Playwright.

```bash
# Use config file settings
node take-screenshots-modular.js integration-test-paths.json

# Override base URL and credentials
node take-screenshots-modular.js integration-test-paths.json http://localhost:8080 admin admin123
```

**What it does:**
- Logs in with configured credentials
- Navigates to each configured frontend path
- Captures full-page screenshots
- Saves to `/tmp/screenshot_{name}.png`
- Reports success/failure for each screenshot

**Important:** This script uses the **working login implementation** from sprinkle-crud6 that properly handles session authentication.

## Customizing for Your Sprinkle

### 1. Copy Template Files

```bash
cp .github/config/template-integration-test-paths.json .github/config/integration-test-paths.json
cp .github/config/template-integration-test-seeds.json .github/config/integration-test-seeds.json
```

### 2. Update Seeds Configuration

Edit `integration-test-seeds.json`:

```json
{
  "seeds": {
    "yoursprinkle": {
      "description": "Your sprinkle seeds",
      "order": 2,
      "seeds": [
        {
          "class": "YourNamespace\\YourSprinkle\\Database\\Seeds\\YourSeed",
          "description": "What your seed does",
          "required": true,
          "validation": {
            "type": "role",
            "slug": "your-role-slug",
            "expected_count": 1
          }
        }
      ]
    }
  }
}
```

### 3. Update Paths Configuration

Edit `integration-test-paths.json`:

```json
{
  "paths": {
    "authenticated": {
      "api": {
        "your_endpoint": {
          "method": "GET",
          "path": "/api/yoursprinkle/yourmodel",
          "expected_status": 200
        }
      },
      "frontend": {
        "your_page": {
          "path": "/yoursprinkle/yourpage",
          "screenshot": true,
          "screenshot_name": "your_page"
        }
      }
    }
  }
}
```

### 4. Update GitHub Workflow

Copy the modular workflow pattern:

```yaml
- name: Seed database (Modular)
  run: |
    cd userfrosting
    cp ../your-sprinkle/.github/config/integration-test-seeds.json .
    cp ../your-sprinkle/.github/scripts/run-seeds.php .
    php run-seeds.php integration-test-seeds.json

- name: Validate seeds (Modular)
  run: |
    cd userfrosting
    cp ../your-sprinkle/.github/scripts/check-seeds-modular.php .
    php check-seeds-modular.php integration-test-seeds.json

- name: Take screenshots (Modular)
  run: |
    cd userfrosting
    cp ../your-sprinkle/.github/config/integration-test-paths.json .
    cp ../your-sprinkle/.github/scripts/take-screenshots-modular.js .
    node take-screenshots-modular.js integration-test-paths.json
```

## Benefits

✅ **Configuration-driven** - Update JSON files, not workflow code  
✅ **Reusable** - Same scripts work for all sprinkles  
✅ **Self-documenting** - JSON structure explains what's being tested  
✅ **Template-based** - Easy to customize for different sprinkles  
✅ **Consistent** - Same testing approach across all sprinkles  
✅ **Maintainable** - Changes to test logic benefit all sprinkles  
✅ **Working login** - Uses proven authentication from CRUD6  

## Troubleshooting

### Screenshots show login page

**Solution:** The modular script uses the working login implementation from CRUD6. Make sure you're using `take-screenshots-modular.js`, not the old `take-authenticated-screenshots.js`.

### Seed validation fails

**Solution:** Check that your validation configuration matches what the seed actually creates. Common issues:
- Wrong slug names
- Wrong expected counts
- Missing role assignments

### Path tests fail

**Solution:** 
- Ensure servers are running (`php bakery serve` and `php bakery assets:vite`)
- Check that paths in JSON match your actual routes
- Verify expected status codes are correct

### Idempotency test fails

**Solution:** Your seeds are creating duplicates. Use `firstOrCreate()` or similar methods to ensure idempotency.

## Migration from Old Approach

The old integration test files are backed up in `.github/backup/`:
- `integration-test.yml` - Old workflow
- `check-seeds.php` - Old non-modular seed check
- `test-seed-idempotency.php` - Old non-modular idempotency test
- `take-authenticated-screenshots.js` - Old screenshot script (had login issues)

The new modular approach:
- Uses configuration files instead of hardcoded values
- Uses reusable scripts instead of custom code
- Fixes the login authentication issues
- Is easier to maintain and customize

## References

- **Framework Overview**: `.github/FRAMEWORK_OVERVIEW.txt` (from CRUD6)
- **Full Documentation**: See sprinkle-crud6 `.github/MODULAR_TESTING_README.md`
- **Example Configurations**: `.github/config/integration-test-*.json`
- **Template Files**: `.github/config/template-integration-test-*.json`

## Need Help?

1. Check the configuration files in `.github/config/`
2. Review the scripts in `.github/scripts/`
3. Look at the modular workflow in `.github/workflows/integration-test-modular.yml`
4. Compare with sprinkle-crud6 for working examples
