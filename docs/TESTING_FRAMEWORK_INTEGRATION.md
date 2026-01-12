# C6Admin Testing Framework Integration

## Overview

C6Admin leverages the **CRUD6 testing framework directly** without copying any scripts. We simply feed C6Admin's schema files to CRUD6's testing infrastructure during CI runs.

## Why This Approach?

Since C6Admin:
1. **Depends on CRUD6** as a core dependency
2. **Uses CRUD6's API endpoints** (`/api/crud6/{model}`)
3. **Defines schemas** in the same format as CRUD6 (`app/schema/crud6/`)

We can **use CRUD6's testing framework as-is** - no copying, no duplication!

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│              GitHub Actions CI Workflow                  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  1. Checkout C6Admin Sprinkle                          │
│     └── app/schema/crud6/*.json                        │
│                                                          │
│  2. Checkout CRUD6 (for testing framework)             │
│     └── .github/testing-framework/                     │
│         ├── scripts/generate-integration-test-paths.js │
│         ├── scripts/test-paths.php                     │
│         └── scripts/test-seed-idempotency-modular.php  │
│                                                          │
│  3. Point CRUD6's scripts at C6Admin's schemas         │
│     node crud6/scripts/generate-*.js                    │
│       --schema-dir c6admin/app/schema/crud6/           │
│                                                          │
│  4. Run CRUD6's tests with generated paths             │
│     php crud6/scripts/test-paths.php                    │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

## What C6Admin Maintains

C6Admin only needs to maintain:

1. **Schema Files** - `app/schema/crud6/*.json`
2. **Model Configuration** - `.github/config/integration-test-models.json`
3. **Seed Configuration** - `.github/config/integration-test-seeds.json`

That's it! No testing scripts to maintain.

## Files and Structure

### What C6Admin Has

```
.github/
├── config/
│   ├── integration-test-models.json    # ✅ C6Admin model configuration
│   └── integration-test-seeds.json     # ✅ C6Admin seed configuration
└── workflows/
    └── integration-test-modular.yml    # ✅ Workflow that uses CRUD6 framework

app/
└── schema/crud6/                        # ✅ C6Admin schemas (source of truth)
    ├── users.json
    ├── roles.json
    ├── groups.json
    ├── permissions.json
    └── activities.json
```

### What CRUD6 Provides (Used Directly in CI)

```
sprinkle-crud6/
└── .github/testing-framework/
    ├── scripts/
    │   ├── generate-integration-test-paths.js  # ✅ Path generator
    │   ├── generate-ddl-sql.js                 # ✅ DDL generator
    │   ├── generate-seed-sql.js                # ✅ Seed generator
    │   ├── test-paths.php                      # ✅ Path tester
    │   ├── run-seeds.php                       # ✅ Seed runner
    │   └── check-seeds-modular.php             # ✅ Seed validator
    └── config/
        └── *.json templates                     # ✅ Configuration templates
```

**Key Point**: C6Admin doesn't copy these scripts - it uses them directly from CRUD6!

## How It Works in CI

The GitHub Actions workflow:

### Step 1: Checkout Both Repositories

```yaml
- name: Checkout C6Admin
  uses: actions/checkout@v4
  with:
    path: sprinkle-c6admin

- name: Checkout CRUD6 (for testing framework)
  uses: actions/checkout@v4
  with:
    repository: ssnukala/sprinkle-crud6
    ref: main
    path: crud6-framework
```

### Step 2: Generate Test Paths from C6Admin Schemas

```yaml
- name: Generate test paths from C6Admin schemas
  run: |
    # Use CRUD6's path generator with C6Admin's schemas
    node crud6-framework/.github/testing-framework/scripts/generate-integration-test-paths.js \
      sprinkle-c6admin/app/schema/crud6 \
      /tmp/c6admin-test-paths.json \
      sprinkle-c6admin/.github/config/integration-test-models.json
```

### Step 3: Run CRUD6's Tests

```yaml
- name: Test C6Admin with CRUD6's test scripts
  run: |
    cd userfrosting
    # Copy generated paths
    cp /tmp/c6admin-test-paths.json integration-test-paths.json
    # Copy CRUD6's test script
    cp ../crud6-framework/.github/testing-framework/scripts/test-paths.php .
    # Run the tests
    php test-paths.php integration-test-paths.json
```

### Step 4: Run Seed Tests

```yaml
- name: Test seeds with CRUD6's scripts
  run: |
    cd userfrosting
    # Copy C6Admin's seed configuration
    cp ../sprinkle-c6admin/.github/config/integration-test-seeds.json .
    # Copy CRUD6's seed test scripts
    cp ../crud6-framework/.github/testing-framework/scripts/run-seeds.php .
    cp ../crud6-framework/.github/testing-framework/scripts/check-seeds-modular.php .
    # Run seed tests
    php run-seeds.php integration-test-seeds.json
    php check-seeds-modular.php integration-test-seeds.json
```

## Configuration File

The only C6Admin-specific file needed is `.github/config/integration-test-models.json`:

```json
{
  "sprinkle": {
    "name": "c6admin",
    "schema_directory": "app/schema/crud6",
    "api_prefix": "/api/crud6",
    "frontend_prefix": "/c6admin"
  },
  "models": [
    { "name": "users", "schema_file": "users.json", "test_id": 100 },
    { "name": "roles", "schema_file": "roles.json", "test_id": 100 },
    { "name": "groups", "schema_file": "groups.json", "test_id": 100 },
    { "name": "permissions", "schema_file": "permissions.json", "test_id": 100 },
    { "name": "activities", "schema_file": "activities.json", "test_id": 100 }
  ],
  "config": {
    "base_url": "http://localhost:8080",
    "auth": { "username": "admin", "password": "admin123" }
  }
}
```

This tells CRUD6's generator:
- Where to find C6Admin's schemas
- What API/frontend prefixes to use
- Which models to generate tests for

## Generated Test Paths

When CRUD6's generator runs with C6Admin's schemas, it creates:

### For Each Model (users, roles, groups, permissions, activities)

**API Endpoints:**
- `{model}_schema` - GET `/api/crud6/{model}/schema`
- `{model}_list` - GET `/api/crud6/{model}`
- `{model}_single` - GET `/api/crud6/{model}/100`

**Frontend Pages:**
- `{model}_list` - GET `/c6admin/{model}`
- `{model}_detail` - GET `/c6admin/{model}/100`

**Result**: 5 schemas → 25 test paths automatically generated!

## Benefits

### 1. **Zero Code Duplication**
- No scripts copied to C6Admin
- CRUD6 testing framework used directly
- Always up-to-date with CRUD6 improvements

### 2. **Schema-Driven Testing**
- Schemas are the single source of truth
- Tests automatically stay in sync with schema changes
- No manual path definition needed

### 3. **Simple Maintenance**
- C6Admin only maintains schemas and configuration
- CRUD6 maintains all testing scripts
- Updates to CRUD6 framework benefit C6Admin automatically

### 4. **Consistent Approach**
- Same testing approach as CRUD6
- Familiar to developers working on both sprinkles
- Best practices baked in

## When Adding a New Model

1. Create schema file: `app/schema/crud6/new_model.json`
2. Add entry to `.github/config/integration-test-models.json`
3. CI automatically generates and runs tests

No scripts to copy, no code to write!

## Comparison: Before vs After

### Before (Copying Scripts Approach)

```
C6Admin Repository:
├── .github/scripts/
│   ├── generate-integration-test-paths.js  ❌ Copied from CRUD6
│   ├── generate-ddl-sql.js                 ❌ Copied from CRUD6
│   ├── generate-seed-sql.js                ❌ Copied from CRUD6
│   ├── test-paths.php                      ❌ Copied from CRUD6
│   └── generate-c6admin-test-paths.sh      ❌ Wrapper script
├── .github/config/
│   ├── integration-test-models.json        ✅ C6Admin specific
│   └── integration-test-paths.json         ⚠️  Generated file
└── app/schema/crud6/*.json                  ✅ C6Admin schemas
```

**Issues:**
- Must update copied scripts when CRUD6 improves
- Risk of scripts getting out of sync
- More files to maintain in C6Admin

### After (Use CRUD6 Directly)

```
C6Admin Repository:
├── .github/config/
│   ├── integration-test-models.json        ✅ C6Admin specific
│   └── integration-test-seeds.json         ✅ C6Admin specific
├── .github/workflows/
│   └── integration-test-modular.yml        ✅ Points to CRUD6
└── app/schema/crud6/*.json                  ✅ C6Admin schemas

CRUD6 Repository (checked out in CI):
└── .github/testing-framework/
    └── scripts/*.{js,php}                   ✅ Used directly
```

**Benefits:**
- No script duplication
- Always uses latest CRUD6 testing framework
- Minimal maintenance in C6Admin

## Commands Reference (CI Only)

These commands run in CI using CRUD6's framework:

### Generate Test Paths (in CI)
```bash
# CI workflow checks out CRUD6 and runs:
node crud6/.github/testing-framework/scripts/generate-integration-test-paths.js \
  c6admin/app/schema/crud6 \
  /tmp/test-paths.json \
  c6admin/.github/config/integration-test-models.json
```

### Test Generated Paths (in CI)
```bash
# CI uses CRUD6's test script:
php crud6/.github/testing-framework/scripts/test-paths.php /tmp/test-paths.json
```

### Run Seed Tests (in CI)
```bash
# CI uses CRUD6's seed scripts:
php crud6/.github/testing-framework/scripts/run-seeds.php integration-test-seeds.json
php crud6/.github/testing-framework/scripts/check-seeds-modular.php integration-test-seeds.json
```

**Note**: No local commands needed! Testing happens automatically in CI.

## Maintenance

### To Update CRUD6 Framework

No action needed! C6Admin always uses the latest version from CRUD6's main branch in CI.

### To Update Tests

Just update your schemas in `app/schema/crud6/` - CI will automatically generate new tests.

## Troubleshooting

### "No test paths generated"

Check that:
1. Schema files exist in `app/schema/crud6/`
2. `integration-test-models.json` references correct schema files
3. CI checked out CRUD6 repository successfully

### "Tests fail after schema change"

This is expected! Update your schemas to fix the issue, or update the test configuration if needed.

## Related Documentation

- [CRUD6 Testing Framework](https://github.com/ssnukala/sprinkle-crud6/tree/main/.github/testing-framework)
- [CRUD6_TESTING_LEVERAGE.md](CRUD6_TESTING_LEVERAGE.md) - Detailed explanation of the approach
- [C6Admin README](../README.md)

## Summary

C6Admin leverages CRUD6's testing framework by:

1. **Checking out CRUD6** in CI workflow
2. **Pointing CRUD6's scripts** at C6Admin's schemas
3. **Running CRUD6's tests** with C6Admin's configuration
4. **Zero script duplication** - uses CRUD6 directly

This embodies the UserFrosting principle of **modular, reusable components**.
