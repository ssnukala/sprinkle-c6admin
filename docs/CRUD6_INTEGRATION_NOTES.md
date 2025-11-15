# CRUD6 v0.6.1 Integration Notes

## Overview

This document describes how sprinkle-c6admin integrates with sprinkle-crud6 v0.6.1 using the published GitHub release instead of manual cloning or downloading.

## Integration Method

### PHP/Composer Integration

**Configuration:** `composer.json`

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/ssnukala/sprinkle-crud6.git",
      "no-api": true
    }
  ],
  "require": {
    "ssnukala/sprinkle-crud6": "^0.6.1"
  }
}
```

**How it works:**
1. Composer is configured to use a VCS (Version Control System) repository
2. The `no-api` option forces Composer to clone the repository instead of using GitHub API
3. When `composer install` runs, it clones CRUD6 from GitHub and checks out the 0.6.1 tag
4. CRUD6 is installed in `vendor/ssnukala/sprinkle-crud6`

**Authentication:**
- In CI environments, GitHub token is required: `composer config github-oauth.github.com $GITHUB_TOKEN`
- Locally, HTTPS access works without authentication for public repositories

### JavaScript/NPM Integration

**Configuration:** `package.json`

```json
{
  "dependencies": {
    "@ssnukala/sprinkle-crud6": "^0.6.1"
  }
}
```

**Important Note:** NPM cannot install CRUD6 directly from Git because:
- GitHub tarballs (e.g., `https://github.com/ssnukala/sprinkle-crud6/archive/refs/tags/0.6.1.tar.gz`) don't include package.json
- NPM expects package.json to be present when installing from Git repositories
- This is a limitation of how GitHub generates release tarballs

**Workaround for CI/Testing:**
The integration test workflow packages CRUD6 from the Composer vendor directory:

```yaml
- name: Package sprinkles for NPM
  run: |
    # Package CRUD6 sprinkle from vendor (installed via Composer)
    cd userfrosting/vendor/ssnukala/sprinkle-crud6
    npm pack
    mv ssnukala-sprinkle-crud6-*.tgz ../../../../

- name: Install NPM dependencies
  run: |
    cd userfrosting
    # Install CRUD6 package first
    npm install ./ssnukala-sprinkle-crud6-*.tgz
    # Install C6Admin package
    npm install ./ssnukala-sprinkle-c6admin-*.tgz
```

## Known Issues

### ✅ RESOLVED: Version Mismatch in package.json

**Issue:** The CRUD6 release 0.6.1 initially contained `package.json` with version "0.5.7" instead of "0.6.1".

**Status:** ✅ **RESOLVED** - The CRUD6 package.json has been updated to version "0.6.1".

**Verification:**
```bash
$ git clone --depth 1 --branch 0.6.1 https://github.com/ssnukala/sprinkle-crud6.git
$ cat sprinkle-crud6/package.json | grep version
  "version": "0.6.1",
```

**Current State:**
- ✅ NPM will now correctly report the package as version 0.6.1
- ✅ Version is now consistent between Git tag and package.json
- ✅ Both Composer and NPM use the correct version

### NPM Registry Availability

**Current State:** CRUD6 is not published to the NPM registry (npmjs.com).

**Implications:**
- Direct `npm install @ssnukala/sprinkle-crud6` will fail
- Must use the workaround of packaging from vendor directory
- Integration tests use local packaging instead of registry installation

**Future Enhancement:**
Publishing CRUD6 to NPM registry would simplify installation:
1. No need to package from vendor directory
2. Version management handled by NPM
3. Easier for end users to install

## Testing

### Local Development

**Composer:**
```bash
cd sprinkle-c6admin
composer install
# CRUD6 will be installed from GitHub to vendor/ssnukala/sprinkle-crud6
```

**NPM (development setup):**
```bash
cd sprinkle-c6admin
npm install
# This will work if CRUD6 is published to NPM registry
# Otherwise, use the packaging workaround from CI
```

### CI Environment

The integration test workflow (`.github/workflows/integration-test-modular.yml`) demonstrates the complete setup:

1. **Create UserFrosting project**
2. **Configure Composer** with local C6Admin path and GitHub token
3. **Install PHP dependencies** (CRUD6 installed via Composer VCS)
4. **Package both sprinkles** from their respective locations
5. **Install NPM packages** from packaged tarballs
6. **Verify installation** by checking file accessibility

## Benefits of This Approach

### Compared to Manual Download/Clone

✅ **Version Control**
- Composer automatically manages the correct version (0.6.1)
- No manual version tracking needed

✅ **Dependency Management**
- Composer handles CRUD6 as a proper dependency
- Automatic resolution of CRUD6's dependencies

✅ **Reproducibility**
- Same installation process everywhere
- Lock files ensure consistent versions

✅ **Simplicity**
- Single `composer install` command
- No manual download or extraction steps

### Compared to Packagist/NPM Registry

⚠️ **Requires VCS Configuration**
- Need to specify repository in composer.json
- GitHub authentication required in CI

⚠️ **NPM Packaging Workaround**
- Cannot install directly from Git via NPM
- Must package from vendor directory

✅ **Uses Published Release**
- Uses official 0.6.1 release tag
- No dependency on main/development branch

## Migration Path

When CRUD6 is published to Packagist and NPM registry:

1. **Remove VCS repository from composer.json**
   ```json
   // Remove this:
   "repositories": [ ... ]
   ```

2. **Keep version constraint**
   ```json
   "require": {
     "ssnukala/sprinkle-crud6": "^0.6.1"
   }
   ```

3. **Update workflow** to remove packaging steps
   ```yaml
   # Can remove the CRUD6 packaging from vendor
   # NPM will install directly from registry
   ```

4. **Simplify installation**
   ```bash
   composer install  # Installs from Packagist
   npm install       # Installs from NPM registry
   ```

## Conclusion

The current integration uses CRUD6 v0.6.1 from GitHub via Composer VCS repository. This provides version control and dependency management benefits while working around the limitation that CRUD6 is not yet published to Packagist/NPM registries.

The integration test workflow demonstrates the complete setup and can be used as a reference for integrating C6Admin and CRUD6 in other projects.
