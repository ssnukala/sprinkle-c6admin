# Leveraging CRUD6 Testing Framework

## Simple Approach: Use CRUD6 Tests Directly

Instead of copying CRUD6's testing framework to C6Admin, we can **leverage CRUD6's tests directly** by pointing them at C6Admin's schema files.

## How It Works

CRUD6's testing framework is designed to be parameterized:
- It accepts a `SCHEMA_PATH` to locate schema files
- It can generate tests from any schema directory
- All scripts work with schemas from any location

## Implementation

### Option 1: Use CRUD6's Workflow with C6Admin Schemas (Recommended)

In C6Admin's GitHub Actions workflow, we can:

1. **Checkout both repositories**:
   ```yaml
   - name: Checkout C6Admin
     uses: actions/checkout@v4
     with:
       path: sprinkle-c6admin
   
   - name: Checkout CRUD6 (for testing framework)
     uses: actions/checkout@v4
     with:
       repository: ssnukala/sprinkle-crud6
       path: sprinkle-crud6
   ```

2. **Point CRUD6's tests at C6Admin's schemas**:
   ```yaml
   - name: Generate test paths from C6Admin schemas
     run: |
       cd sprinkle-crud6
       # Use C6Admin's schema directory
       node .github/testing-framework/scripts/generate-integration-test-paths.js \
         ../sprinkle-c6admin/app/schema/crud6 \
         .github/config/integration-test-paths.json
   
   - name: Run CRUD6's test suite with C6Admin schemas
     run: |
       cd userfrosting
       # Copy configuration that points to C6Admin schemas
       cp ../sprinkle-crud6/.github/config/integration-test-paths.json .
       cp ../sprinkle-crud6/.github/testing-framework/scripts/test-paths.php .
       php test-paths.php integration-test-paths.json
   ```

### Option 2: Configure CRUD6 Framework Inline

```yaml
- name: Install CRUD6 Testing Framework
  run: |
    # Clone CRUD6 to get testing framework
    git clone --depth 1 https://github.com/ssnukala/sprinkle-crud6.git /tmp/crud6
    
    # Copy framework scripts to a temporary location
    mkdir -p /tmp/testing-framework
    cp -r /tmp/crud6/.github/testing-framework/* /tmp/testing-framework/
    chmod +x /tmp/testing-framework/scripts/*.php

- name: Generate paths from C6Admin schemas
  run: |
    # Use CRUD6's path generator with C6Admin's schemas
    node /tmp/testing-framework/scripts/generate-integration-test-paths.js \
      sprinkle-c6admin/app/schema/crud6 \
      /tmp/integration-test-paths.json \
      sprinkle-c6admin/.github/config/integration-test-models.json

- name: Test C6Admin with CRUD6's test scripts
  run: |
    cd userfrosting
    cp /tmp/integration-test-paths.json .
    cp /tmp/testing-framework/scripts/test-paths.php .
    php test-paths.php integration-test-paths.json
```

## Benefits of This Approach

1. **Zero Code Duplication**: No need to copy scripts to C6Admin
2. **Always Up-to-Date**: Get latest CRUD6 testing improvements automatically
3. **Single Source of Truth**: CRUD6 maintains the testing framework
4. **Simpler Maintenance**: C6Admin only maintains its schemas
5. **Consistent Testing**: Exact same tests as CRUD6

## What C6Admin Needs

C6Admin only needs:
1. **Schema files**: `app/schema/crud6/*.json` (already has these)
2. **Model configuration**: `.github/config/integration-test-models.json` (defines which schemas to test)
3. **Workflow configuration**: Point to CRUD6's testing framework

That's it! No testing scripts needed in C6Admin.

## Comparison

### Before (Copying Scripts)
```
sprinkle-c6admin/
├── .github/
│   ├── scripts/
│   │   ├── generate-integration-test-paths.js  ❌ Copied from CRUD6
│   │   ├── generate-ddl-sql.js                 ❌ Copied from CRUD6
│   │   ├── generate-seed-sql.js                ❌ Copied from CRUD6
│   │   ├── test-paths.php                      ❌ Copied from CRUD6
│   │   └── ...                                 ❌ More copies
│   └── config/
│       └── integration-test-models.json        ✅ C6Admin specific
└── app/schema/crud6/                            ✅ C6Admin schemas
    ├── users.json
    ├── roles.json
    └── ...
```

### After (Using CRUD6 Directly)
```
sprinkle-c6admin/
├── .github/
│   ├── config/
│   │   └── integration-test-models.json        ✅ C6Admin specific
│   └── workflows/
│       └── integration-test.yml                ✅ Points to CRUD6 framework
└── app/schema/crud6/                            ✅ C6Admin schemas
    ├── users.json
    ├── roles.json
    └── ...
```

## Implementation in Workflow

Here's the minimal workflow approach:

```yaml
name: C6Admin Integration Test

on:
  push:
    branches: [main, develop]
  pull_request:
    branches: [main, develop]

jobs:
  integration-test:
    runs-on: ubuntu-latest
    
    steps:
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
      
      - name: Setup PHP & Node
        # ... standard setup steps
      
      - name: Generate test paths from C6Admin schemas
        run: |
          cd crud6-framework
          node .github/testing-framework/scripts/generate-integration-test-paths.js \
            ../sprinkle-c6admin/app/schema/crud6 \
            /tmp/c6admin-test-paths.json \
            ../sprinkle-c6admin/.github/config/integration-test-models.json
      
      - name: Test C6Admin endpoints
        run: |
          cd userfrosting
          cp /tmp/c6admin-test-paths.json integration-test-paths.json
          cp ../crud6-framework/.github/testing-framework/scripts/test-paths.php .
          php test-paths.php integration-test-paths.json
```

## Next Steps

1. Remove copied scripts from C6Admin (generate-*.js, test-paths.php, etc.)
2. Keep only C6Admin-specific configuration (integration-test-models.json)
3. Update workflow to checkout CRUD6 and use its testing framework
4. Document that C6Admin uses CRUD6's testing framework directly

## Summary

**New Requirement**: Use CRUD6 testing as-is, without copying.

**Solution**: 
- Checkout CRUD6 repository in CI workflow
- Point CRUD6's testing scripts at C6Admin's schema files
- Run CRUD6's tests with C6Admin's schemas
- No script duplication needed in C6Admin

This is the cleanest approach and follows the principle of reusable components!
