# C6Admin Testing Framework Integration

## Overview

C6Admin leverages the **reusable testing framework** from sprinkle-crud6 to automatically generate integration test paths from schema files. This approach follows the principle: **feed our schema files to CRUD6's testing infrastructure**.

## Why This Approach?

Since C6Admin:
1. **Depends on CRUD6** as a core dependency
2. **Uses CRUD6's API endpoints** (`/api/crud6/{model}`)
3. **Defines schemas** in the same format as CRUD6 (`app/schema/crud6/`)

It makes sense to **reuse CRUD6's proven testing framework** rather than duplicate it.

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│              C6Admin Sprinkle                           │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  Schema Files (app/schema/crud6/)                      │
│  ├── users.json                                        │
│  ├── roles.json                                        │
│  ├── groups.json                                       │
│  ├── permissions.json                                  │
│  └── activities.json                                   │
│                          │                              │
│                          ▼                              │
│  ┌───────────────────────────────────────┐            │
│  │  CRUD6 Testing Framework (Copied)     │            │
│  ├───────────────────────────────────────┤            │
│  │  • generate-integration-test-paths.js │            │
│  │  • generate-ddl-sql.js                │            │
│  │  • generate-seed-sql.js               │            │
│  └───────────────────────────────────────┘            │
│                          │                              │
│                          ▼                              │
│  Generated Test Paths                                   │
│  └── integration-test-paths.json                       │
│      • 15 API paths (schema, list, single)            │
│      • 10 frontend paths (list, detail)               │
│      • 25 total test paths                            │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

## Files and Structure

### Configuration Files

```
.github/
├── config/
│   ├── integration-test-models.json    # Model definitions for C6Admin
│   ├── integration-test-paths.json     # AUTO-GENERATED test paths
│   └── integration-test-seeds.json     # Seed configuration (existing)
└── scripts/
    ├── generate-c6admin-test-paths.sh  # Wrapper script for C6Admin
    ├── generate-integration-test-paths.js  # From CRUD6
    ├── generate-ddl-sql.js             # From CRUD6
    ├── generate-seed-sql.js            # From CRUD6
    ├── test-paths.php                  # Existing path tester
    ├── run-seeds.php                   # Existing seed runner
    └── check-seeds-modular.php         # Existing seed validator
```

### Schema Files (Source of Truth)

```
app/
└── schema/
    └── crud6/
        ├── users.json       # User model schema
        ├── roles.json       # Role model schema
        ├── groups.json      # Group model schema
        ├── permissions.json # Permission model schema
        └── activities.json  # Activity model schema
```

## How It Works

### Step 1: Define Models Once

Edit `.github/config/integration-test-models.json` to define which schemas to test:

```json
{
  "sprinkle": {
    "name": "c6admin",
    "schema_directory": "app/schema/crud6",
    "api_prefix": "/api/crud6",
    "frontend_prefix": "/c6admin"
  },
  "models": [
    {
      "name": "users",
      "schema_file": "users.json",
      "test_id": 100,
      "singular": "user",
      "title": "Users"
    }
    // ... more models
  ]
}
```

### Step 2: Generate Test Paths

Run the generator script:

```bash
.github/scripts/generate-c6admin-test-paths.sh
```

This will:
1. Read all schema files from `app/schema/crud6/`
2. Extract model information (fields, relationships, actions)
3. Apply path templates from `integration-test-models.json`
4. Generate complete `integration-test-paths.json` with all test paths

### Step 3: Use in CI/CD

The generated paths are used by existing testing scripts:

```bash
# Test API and frontend paths
php .github/scripts/test-paths.php .github/config/integration-test-paths.json

# Take screenshots of frontend pages
node .github/scripts/take-screenshots-modular.js .github/config/integration-test-paths.json
```

## Generated Test Paths

For each model defined in `integration-test-models.json`, the generator creates:

### Authenticated Paths

**API Endpoints:**
- `{model}_schema` - GET schema definition
- `{model}_list` - GET list of records
- `{model}_single` - GET single record by ID

**Frontend Pages:**
- `{model}_list` - List page with screenshot
- `{model}_detail` - Detail page with screenshot

### Unauthenticated Paths (Optional)

- Verify authentication required for protected routes
- Check redirects to login page

## Example: Users Model

From `app/schema/crud6/users.json`, the generator creates:

```json
{
  "users_schema": {
    "method": "GET",
    "path": "/api/crud6/users/schema",
    "expected_status": 200,
    "validation": { "type": "json", "contains": ["model", "fields"] }
  },
  "users_list": {
    "method": "GET",
    "path": "/api/crud6/users",
    "expected_status": 200,
    "validation": { "type": "json", "contains": ["rows"] }
  },
  "users_single": {
    "method": "GET",
    "path": "/api/crud6/users/100",
    "expected_status": 200,
    "validation": { "type": "json", "contains": ["id"] }
  }
}
```

## Benefits

### 1. **Schema-Driven Testing**
- Schemas are the single source of truth
- Tests automatically stay in sync with schema changes
- No manual path definition needed

### 2. **Reusable Infrastructure**
- Leverage CRUD6's proven testing scripts
- No code duplication
- Updates to CRUD6 framework benefit C6Admin

### 3. **Automatic Generation**
- 5 schema files → 25 test paths automatically
- Includes API and frontend paths
- Configurable via JSON templates

### 4. **Consistent Approach**
- Same testing approach as CRUD6
- Familiar to developers working on both sprinkles
- Best practices baked in

## Updating Tests

### When Adding a New Model

1. Create schema file: `app/schema/crud6/new_model.json`
2. Add model entry to `.github/config/integration-test-models.json`:
   ```json
   {
     "name": "new_model",
     "schema_file": "new_model.json",
     "test_id": 100,
     "singular": "new_item",
     "title": "New Model"
   }
   ```
3. Regenerate paths: `.github/scripts/generate-c6admin-test-paths.sh`

### When Updating a Schema

1. Edit schema file: `app/schema/crud6/model.json`
2. Regenerate paths: `.github/scripts/generate-c6admin-test-paths.sh`
3. Paths automatically updated to match new schema structure

## GitHub Actions Integration

In `.github/workflows/integration-test-modular.yml`:

```yaml
- name: Generate test paths from C6Admin schemas
  run: |
    cd sprinkle-c6admin
    .github/scripts/generate-c6admin-test-paths.sh

- name: Test generated paths
  run: |
    cd userfrosting
    cp ../sprinkle-c6admin/.github/config/integration-test-paths.json .
    php test-paths.php integration-test-paths.json
```

## Comparison: Before vs After

### Before (Manual Approach)

- ❌ Manually define 25+ test paths in JSON
- ❌ Keep paths in sync with schemas manually
- ❌ Copy/paste from CRUD6 and modify
- ❌ Risk of paths becoming outdated

### After (Schema-Driven Approach)

- ✅ Define 5 models in configuration
- ✅ Generate 25 test paths automatically
- ✅ Reuse CRUD6's proven scripts
- ✅ Paths always match current schemas

## Commands Reference

### Generate Test Paths
```bash
.github/scripts/generate-c6admin-test-paths.sh
```

### Test Generated Paths
```bash
php .github/scripts/test-paths.php .github/config/integration-test-paths.json
```

### Run Only API Tests
```bash
php .github/scripts/test-paths.php .github/config/integration-test-paths.json auth api
```

### Run Only Frontend Tests
```bash
php .github/scripts/test-paths.php .github/config/integration-test-paths.json auth frontend
```

### Take Screenshots
```bash
node .github/scripts/take-screenshots-modular.js .github/config/integration-test-paths.json
```

## Maintenance

### Updating CRUD6 Framework Scripts

To get latest versions of testing scripts from CRUD6:

```bash
# Copy updated scripts
cp /path/to/sprinkle-crud6/.github/testing-framework/scripts/generate-integration-test-paths.js \
   .github/scripts/

cp /path/to/sprinkle-crud6/.github/testing-framework/scripts/generate-ddl-sql.js \
   .github/scripts/

cp /path/to/sprinkle-crud6/.github/testing-framework/scripts/generate-seed-sql.js \
   .github/scripts/

# Make executable
chmod +x .github/scripts/generate-*.js

# Regenerate test paths with updated scripts
.github/scripts/generate-c6admin-test-paths.sh
```

## Troubleshooting

### "Node.js not found"

Install Node.js 16+ to run the generator script.

### "Schema directory not found"

Ensure `app/schema/crud6/` exists with schema JSON files.

### "No paths generated"

Check that:
1. Schema files are valid JSON
2. `integration-test-models.json` references correct schema files
3. Models array is not empty

### "Generated paths don't match actual routes"

Verify in `integration-test-models.json`:
- `api_prefix` is correct (should be `/api/crud6`)
- `frontend_prefix` is correct (should be `/c6admin`)
- Model names match actual routes

## Related Documentation

- [CRUD6 Testing Framework](https://github.com/ssnukala/sprinkle-crud6/tree/main/.github/testing-framework)
- [Modular Testing README](https://github.com/ssnukala/sprinkle-crud6/blob/main/.github/MODULAR_TESTING_README.md)
- [C6Admin README](../../README.md)

## Summary

By leveraging CRUD6's testing framework and feeding it C6Admin's schema files, we achieve:

1. **Zero Duplication** - Reuse proven infrastructure
2. **Schema-Driven** - Single source of truth
3. **Automatic Generation** - 5 schemas → 25 test paths
4. **Easy Maintenance** - Update schemas, regenerate paths
5. **Consistent Testing** - Same approach as CRUD6

This integration embodies the UserFrosting principle of **modular, reusable components**.
