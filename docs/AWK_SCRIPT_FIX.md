# AWK Script Fix for Router Configuration

## Problem

The awk script in `.github/workflows/integration-test-modular.yml` was not correctly inserting C6Admin routes into the `router/index.ts` file during integration tests.

### Original Issue

The workflow failed at the "Configure router/index.ts" step because the awk script couldn't find the correct insertion point.

**GitHub Actions Run:** https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19454099048/job/55664625834

### Root Cause

The original awk script attempted to:
1. Find the line with `title: 'ADMIN_PANEL'`
2. Then find the next closing brace `},`
3. Insert the C6Admin routes after that brace

This approach was unreliable because:
- The pattern matching was too specific to the ADMIN_PANEL section
- It relied on specific indentation of the closing brace
- It didn't account for variations in router structure

## Solution

### New Approach

Instead of looking for the AdminRoutes closing brace, the new script:
1. Finds the closing bracket `]` of the routes array
2. Inserts the C6Admin routes BEFORE the closing bracket
3. Uses simpler pattern matching: `/^    \]$/`

### Code Changes

#### Before (Not Working)

```awk
awk '
/title: .ADMIN_PANEL./ { found=1 }
found && /^        },/ && !done {
  print
  print "        // C6Admin routes with their own layout"
  print "        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard }),"
  done=1
  next
}
{ print }
' app/assets/router/index.ts > app/assets/router/index.ts.tmp
```

#### After (Working)

```awk
awk '
/^    \]$/ && !done {
  print "        ,"
  print "        // C6Admin routes with their own layout"
  print "        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })"
  done=1
}
{ print }
' app/assets/router/index.ts > app/assets/router/index.ts.tmp
```

### Key Differences

1. **Pattern matching**: Changed from `title: .ADMIN_PANEL.` to `/^    \]$/`
2. **Insertion point**: Changed from after `},` to before `]`
3. **Comma placement**: Changed from trailing comma to leading comma
4. **Removed `next`**: Simplified control flow

## Expected Output

The C6Admin routes are inserted at the end of the routes array:

```typescript
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '',
            redirect: { name: 'home' },
            component: () => import('../layouts/LayoutPage.vue'),
            children: [
                // ... home routes
            ]
        },
        {
            path: '/admin',
            component: () => import('../layouts/LayoutDashboard.vue'),
            children: [...AdminRoutes],
            meta: {
                title: 'ADMIN_PANEL'
            }
        }
        ,
        // C6Admin routes with their own layout
        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })
    ]
})
export default router
```

## Testing

A test script is available to validate the fix:

```bash
.github/scripts/test-router-insertion.sh
```

This script:
1. Creates a sample router/index.ts file
2. Applies the workflow transformations
3. Verifies the output structure
4. Runs 6 verification tests

All tests should pass with ✅ marks.

## Fallback Method

The workflow also includes a sed-based fallback method:

```bash
sed -i '/^    \]/i\        ,\n        // C6Admin routes with their own layout\n        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })' app/assets/router/index.ts
```

This provides redundancy in case the awk script fails for any reason.

## Related Files

- `.github/workflows/integration-test-modular.yml` - Main workflow file (lines 81-121)
- `.github/scripts/test-router-insertion.sh` - Test script for validation

## References

- Issue: https://github.com/ssnukala/sprinkle-c6admin/issues/[number]
- Failed workflow: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19454099048/job/55664625834
- Pull Request: https://github.com/ssnukala/sprinkle-c6admin/pull/[number]
