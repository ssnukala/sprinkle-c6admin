# Integration Test Workflow Fix

## Issue Summary
The integration-test.yml workflow was attempting to edit UserFrosting application files (MyApp.php, router/index.ts, main.ts, package.json) **AFTER** running `composer install` and `npm install`. This is incorrect because these commands install the files from the UserFrosting skeleton project.

## Problem
When `composer create-project userfrosting/userfrosting` is run with `--no-install`, it creates the basic directory structure and skeleton files. These files include:
- `app/src/MyApp.php`
- `app/assets/router/index.ts`
- `app/assets/main.ts`
- `package.json`

The workflow was trying to edit these files AFTER running `composer install`, but by that point the files had already been brought in with their default configuration from the UserFrosting skeleton.

## Solution
Reorganize the workflow steps to perform ALL configuration edits BEFORE running `composer install` and `npm install`:

### Previous (Incorrect) Order:
1. Create UserFrosting project (no install)
2. Configure composer.json
3. **Install PHP dependencies** ❌
4. Package sprinkle-c6admin for NPM
5. **Install NPM dependencies** ❌
6. Configure MyApp.php ❌ (too late - file already installed)
7. Configure router/index.ts ❌ (too late - file already installed)
8. Configure main.ts ❌ (too late - file already installed)

### New (Correct) Order:
1. Create UserFrosting project (no install)
2. Configure composer.json
3. Configure package.json ✅ (before npm install)
4. Configure MyApp.php ✅ (before composer install)
5. Configure router/index.ts ✅ (before npm install)
6. Configure main.ts ✅ (before npm install)
7. **Install PHP dependencies** ✅ (after all PHP config)
8. Package sprinkle-c6admin for NPM
9. **Install NPM dependencies** ✅ (after all NPM config)

## Changes Made

### Step Reordering
- Moved "Configure package.json" step before "Install PHP dependencies"
- Moved "Configure MyApp.php" step before "Install PHP dependencies"
- Moved "Configure router/index.ts" step before "Install PHP dependencies"
- Moved "Configure main.ts" step before "Install PHP dependencies"

### Additional Fixes
- Fixed step name from "Configure /main.ts" to "Configure main.ts"
- Verified YAML syntax is valid

## Why This Works

### composer create-project Behavior
When running:
```bash
composer create-project userfrosting/userfrosting userfrosting "^6.0-beta" --no-scripts --no-install --ignore-platform-reqs
```

This creates the skeleton structure WITHOUT installing dependencies. The files exist in their default state from the skeleton.

### Editing Before Install
By editing the files BEFORE running `composer install` and `npm install`:
1. We modify the skeleton files to reference c6admin instead of admin
2. When composer/npm install runs, they correctly resolve the c6admin dependencies
3. The application is configured properly from the start

### The Critical Sequence
```yaml
# 1. Create skeleton (files exist but not configured)
- composer create-project --no-install

# 2. Edit all the files
- Configure composer.json
- Configure package.json
- Configure MyApp.php
- Configure router/index.ts
- Configure main.ts

# 3. Now install dependencies
- composer install
- npm install

# 4. Continue with migrations, tests, etc.
```

## Impact
This fix ensures that the integration test workflow correctly sets up a UserFrosting 6 application with sprinkle-c6admin replacing sprinkle-admin. The configuration changes are applied to the skeleton files before dependencies are resolved and installed.

## Verification
The workflow now:
1. ✅ Creates UserFrosting skeleton with correct file structure
2. ✅ Edits configuration files before installation
3. ✅ Installs PHP dependencies with correct sprinkle configuration
4. ✅ Installs NPM dependencies with correct package configuration
5. ✅ Runs migrations and tests with properly configured application

## Date
2025-10-29
