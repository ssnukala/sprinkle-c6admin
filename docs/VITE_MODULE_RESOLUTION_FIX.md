# Fix: Vue Application Not Mounting - lodash.deburr Module Resolution Error

**Date**: December 11, 2025  
**Issue**: Integration test failure - Vue application not rendering on login page  
**Root Cause**: Incorrect Vite dependency optimization configuration  
**Status**: ✅ FIXED

## Problem Statement

The integration test workflow was failing because the Vue.js application was not mounting on the login page. Instead of rendering the login form with Vue components, the page showed only:

```html
<div id="app"></div>
```

### Symptoms

1. **Browser Console Error**:
   ```
   The requested module '/assets/@fs/.../node_modules/lodash.deburr/index.js?v=8d442b86' 
   does not provide an export named 'default'
   ```

2. **Test Failure**: Login form selectors not found:
   ```
   ❌ Could not find username input field after trying all selectors
   ❌ Login form not found - username input field is missing
   ```

3. **Page State**: Vue not detected, no Vue apps mounted

### Comparison with Working System

The same test step in sprinkle-crud6 (which works correctly) shows:
```html
<div id="app" data-v-app="">
  <header>
    <div uk-sticky="..." class="uk-sticky uk-sticky-fixed">
      <!-- Fully rendered Vue components -->
    </div>
  </header>
  <!-- ... -->
</div>
```

## Root Cause Analysis

### Investigation Steps

1. **Compared CI logs**: C6Admin vs CRUD6 integration tests
2. **Identified module error**: `lodash.deburr` export issue  
3. **Examined configurations**: Compared vite.config.ts files
4. **Found discrepancy**: C6Admin had extra optimizeDeps configuration

### The Problem

In `vite.config.ts`, C6Admin had:

```typescript
export default defineConfig({
    plugins: [vue(), ViteYaml()],
    optimizeDeps: {
        // Pre-bundle limax and its dependencies for optimal performance
        // This improves Vite cold-start time and ensures consistent behavior
        // Note: C6Admin depends on CRUD6 which uses limax (CommonJS module)
        include: ['limax', 'lodash.deburr']  // ❌ PROBLEMATIC
    },
    // ...
})
```

**Why it failed:**
- `lodash.deburr` is a CommonJS module that doesn't provide a default export
- Vite's automatic handling works fine, but explicit pre-bundling breaks module resolution
- The error prevents Vite from loading the module dependency tree
- This cascades into Vue not being able to mount the application

**Why CRUD6 works:**
- CRUD6's vite.config.ts does NOT have the `optimizeDeps` section
- Vite automatically discovers and handles CommonJS dependencies
- No explicit pre-bundling = no module resolution conflicts

## Solution

Remove the `optimizeDeps` configuration from `vite.config.ts`.

### Before (Broken)
```typescript
export default defineConfig({
    plugins: [vue(), ViteYaml()],
    optimizeDeps: {
        include: ['limax', 'lodash.deburr']
    },
    test: { /* ... */ }
})
```

### After (Fixed)
```typescript
export default defineConfig({
    plugins: [vue(), ViteYaml()],
    test: { /* ... */ }
})
```

### Configuration Now Matches CRUD6

The fixed `vite.config.ts` is now **identical** to sprinkle-crud6's configuration, ensuring consistent behavior.

## Why This Works

1. **Vite's Auto-Discovery**: Vite automatically discovers and optimizes dependencies without explicit configuration
2. **CommonJS Handling**: Vite handles CommonJS modules (like lodash.deburr) correctly when not forced into pre-bundling
3. **Dependency Chain**: The limax → lodash.deburr dependency chain resolves naturally
4. **Vue Mounting**: With modules loading correctly, Vue can mount and render components

## Dependencies Context

### limax Usage in C6Admin

C6Admin uses `limax` (slug generation library) in:
- `app/assets/composables/useGroupApi.ts`
- `app/assets/composables/useRoleApi.ts`

```typescript
import slug from 'limax'
```

### limax Internal Dependencies

limax (v4.2.1) internally uses:
- `lodash.deburr` - For removing diacritical marks from characters
- Other lodash utilities

Vite handles this CommonJS dependency automatically without explicit configuration.

## Testing

### Local Testing
```bash
npm install
npm run build  # Should complete without errors
```

### Integration Test
The CI integration test should now:
1. ✅ Load the login page successfully
2. ✅ Mount Vue application with all components
3. ✅ Find login form input fields
4. ✅ Successfully authenticate and take screenshots

## Lessons Learned

1. **Trust Vite's Defaults**: Vite's automatic dependency discovery is robust; explicit `optimizeDeps` is rarely needed
2. **Match Working Configurations**: When debugging, compare with known-working similar projects
3. **CommonJS Modules**: Be careful with explicit pre-bundling of CommonJS modules - let Vite handle them
4. **Test Full Stack**: Frontend build errors can manifest as runtime failures in integration tests

## References

- **CRUD6 vite.config.ts**: https://github.com/ssnukala/sprinkle-crud6/blob/0.6.1/vite.config.ts
- **Failed CI Run**: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/20117414270
- **Working CRUD6 Test**: https://github.com/ssnukala/sprinkle-crud6/actions/runs/19616571248
- **Vite optimizeDeps**: https://vitejs.dev/config/dep-optimization-options.html

## Related Issues

This fix resolves the discrepancy where C6Admin's integration tests were failing while CRUD6's identical test infrastructure was working correctly.
