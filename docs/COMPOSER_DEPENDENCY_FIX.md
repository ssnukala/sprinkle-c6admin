# Fix for Composer Dependency Resolution Issues

## Latest Fix (2025-11-16): Duplicate Repositories and SSH URL

### Problem
CI workflow was failing with the error:
```
ssnukala/sprinkle-c6admin dev-main requires ssnukala/sprinkle-crud6 ^0.6.1 -> could not be found in any version
```

**GitHub Actions Run:** https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19398412108/job/55501847839

### Root Cause
The `composer.json` had duplicate "repositories" keys which caused composer to fail resolving the `ssnukala/sprinkle-crud6` dependency:

1. **First definition** (lines 24-29): Used array syntax with `"no-api": true`
2. **Second definition** (lines 74-80): Used SSH URL `git@github.com:` which fails in CI without SSH keys

### Solution
Fixed `composer.json` by:
1. **Removed duplicate `repositories` array**
2. **Changed SSH URL to HTTPS**
   - From: `git@github.com:ssnukala/sprinkle-crud6.git`
   - To: `https://github.com/ssnukala/sprinkle-crud6.git`
3. **Removed unnecessary `no-api` parameter**

Final configuration:
```json
"repositories": {
    "sprinkle-crud6": {
        "type": "vcs",
        "url": "https://github.com/ssnukala/sprinkle-crud6.git"
    }
}
```

### Why This Works
- **HTTPS URLs** work with GitHub token authentication in CI
- **SSH URLs** require SSH keys which aren't available in CI
- CI workflows use: `composer config github-oauth.github.com ${{ secrets.GITHUB_TOKEN }}`
- Single repository definition prevents conflicts

### Validation
- ✅ `composer.json` is valid JSON
- ✅ `composer validate` passes
- ✅ Repository URL is publicly accessible
- ✅ Tag `0.6.1` exists in the repository
- ✅ All PHP files have valid syntax

---

## Previous Fix: Minimum Stability Constraint

### Problem
The integration-test workflow was failing with this error:
```
Error: Your requirements could not be resolved to an installable set of packages.

Problem 1
  - Root composer.json requires ssnukala/sprinkle-c6admin @dev -> satisfiable by ssnukala/sprinkle-c6admin[dev-main].
  - ssnukala/sprinkle-c6admin dev-main requires ssnukala/sprinkle-crud6 dev-main -> found ssnukala/sprinkle-crud6[dev-main] but it does not match your minimum-stability.
```

### Root Cause
When the UserFrosting project (which sets `minimum-stability: beta`) tries to install sprinkle-c6admin, it encounters a conflict:
- sprinkle-c6admin requires `ssnukala/sprinkle-crud6: "dev-main"`
- The `dev-main` branch is treated as a dev package (less stable than beta)
- This violates the parent project's minimum-stability constraint

### Solution
Changed the dependency specification from:
```json
"ssnukala/sprinkle-crud6": "dev-main"
```

To:
```json
"ssnukala/sprinkle-crud6": "^0.6.1"
```

Using a tagged version constraint instead of a branch alias ensures compatibility with the parent project's stability requirements.
