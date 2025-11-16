# Composer Dependency Resolution Fix

## Issue Summary

**Problem**: GitHub Actions workflow was failing with the error:
```
No composer.lock file present. Updating dependencies to latest instead of installing from lock file.
ssnukala/sprinkle-c6admin dev-main requires ssnukala/sprinkle-crud6 ^0.6.1 -> could not be found in any version
```

**Root Cause**: 
1. sprinkle-crud6 is not published on Packagist
2. The VCS repository configuration in c6admin's composer.json was using SSH protocol by default
3. Parent projects don't automatically inherit repository configurations from dependencies
4. The GitHub Actions workflow wasn't configuring the sprinkle-crud6 repository

## Solution

### 1. Changed Repository Type (composer.json)

**Before**:
```json
"repositories": {
    "sprinkle-crud6": {
        "type": "vcs",
        "url": "https://github.com/ssnukala/sprinkle-crud6.git"
    }
}
```

**After**:
```json
"repositories": {
    "sprinkle-crud6": {
        "type": "git",
        "url": "https://github.com/ssnukala/sprinkle-crud6.git"
    }
}
```

**Why**: The "git" type forces Composer to use HTTPS protocol directly instead of trying SSH first, avoiding authentication issues in CI environments.

### 2. Updated Installation Documentation (README.md)

Added Step 1 requiring users to configure the sprinkle-crud6 repository before installing:

```bash
composer config repositories.sprinkle-crud6 git https://github.com/ssnukala/sprinkle-crud6.git
composer require ssnukala/sprinkle-c6admin
```

**Why**: Since sprinkle-crud6 is not on Packagist, parent projects must be explicitly told where to find it.

### 3. Updated GitHub Actions Workflow

Added repository configuration to the workflow:

```yaml
- name: Configure Composer for beta packages and local sprinkles
  run: |
    cd userfrosting
    # Add local C6Admin sprinkle path
    composer config repositories.local-c6admin path ../sprinkle-c6admin
    # Add sprinkle-crud6 repository (required dependency of C6Admin)
    composer config repositories.sprinkle-crud6 git https://github.com/ssnukala/sprinkle-crud6.git
    composer require ssnukala/sprinkle-c6admin:@dev --no-update
```

**Why**: The integration test workflow needs to configure the repository before installing dependencies.

## Technical Details

### Why Not Commit composer.lock?

For library packages (like sprinkles), it's a best practice NOT to commit `composer.lock`:
- Libraries should be tested against a range of dependency versions
- Committing lock files can hide compatibility issues
- Lock files are for applications, not libraries

### Repository Configuration Inheritance

Composer does NOT automatically inherit repository configurations from dependencies for security reasons:
- Parent projects must explicitly configure custom repositories
- This prevents dependencies from injecting arbitrary package sources
- Standard practice is to document repository requirements in installation instructions

### Testing

Verified the fix works by:
1. Creating a test composer project
2. Configuring the sprinkle-crud6 repository
3. Running `composer update --dry-run`
4. Confirming sprinkle-crud6 v0.6.1 is resolved correctly

## Files Changed

1. **composer.json**: Changed repository type from "vcs" to "git"
2. **README.md**: Added repository configuration step to installation instructions
3. **.github/workflows/integration-test-modular.yml**: Added sprinkle-crud6 repository configuration

## Impact

### For Users
- Must run `composer config repositories.sprinkle-crud6 git https://github.com/ssnukala/sprinkle-crud6.git` before installing
- Clear documentation provided in README
- One-time setup per project

### For CI/CD
- GitHub Actions workflow now properly configures repositories
- No authentication issues with HTTPS protocol
- Consistent installation across environments

## Future Improvements

Consider publishing sprinkle-crud6 to Packagist to eliminate the need for manual repository configuration.

## References

- [Composer Repositories Documentation](https://getcomposer.org/doc/05-repositories.md)
- [GitHub Actions Issue](https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19400230114/job/55506706187)
- [sprinkle-crud6 Repository](https://github.com/ssnukala/sprinkle-crud6)
