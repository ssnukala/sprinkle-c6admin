# Vite Version Fix for C6Admin

## Issue

Frontend build failing with TypeError when running `php bakery assets:vite` in UserFrosting 6:

```
TypeError: Cannot read properties of undefined (reading 'join')
    at file:///path/to/userfrosting/node_modules/vite/dist/node/chunks/dep-D4NMHUTW.js:14189:13
```

While CRUD6 login succeeded despite this error, C6Admin's frontend build was actually failing.

## Root Cause

**Vite version mismatch:**
- UserFrosting 6: `vite: ^6.4.1`
- C6Admin (before fix): `vite: ^5.0.0`
- CRUD6 (also affected): `vite: ^5.0.0`

The major version difference caused compatibility issues when UserFrosting 6's Vite tried to process the sprinkle's assets.

## Solution

Upgraded Vite to v6 in package.json:

```diff
 "devDependencies": {
-  "vite": "^5.0.0",
+  "vite": "^6.0.0",
 }
```

## Configuration Pattern

Following CRUD6's proven pattern, sprinkle vite.config.ts remains **minimal**:

```typescript
export default defineConfig({
    plugins: [vue(), ViteYaml()],
    optimizeDeps: {
        include: ['limax', 'lodash.deburr']
    },
    test: {
        // test configuration only
    }
})
```

**No build configuration needed** - UserFrosting 6's vite.config.ts handles all building.

## Why This Works

1. **Sprinkles are source packages** - compiled by the main UF6 app
2. **CRUD6 uses same pattern** - proven to work in integration tests
3. **Version compatibility** - eliminates TypeError during asset processing
4. **Minimal configuration** - follows UserFrosting 6 sprinkle conventions

## References

- UserFrosting 6: https://github.com/userfrosting/userfrosting/blob/6.0/package.json
- CRUD6 pattern: https://github.com/ssnukala/sprinkle-crud6/blob/0.6.1/vite.config.ts
- Official sprinkle-admin: https://github.com/userfrosting/sprinkle-admin/blob/6.0/vite.config.ts
- CI failure: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/20116428747/job/57726878431#step:31:157
