#!/bin/bash
# Test script for router/index.ts C6Admin routes insertion
# This script validates the awk script fix for the integration test workflow

set -e

echo "========================================="
echo "Testing Router Configuration Script"
echo "========================================="

# Create temporary test directory
TEST_DIR=$(mktemp -d)
cd "$TEST_DIR"

# Create a sample router/index.ts file (simulating UserFrosting 6 default structure)
cat > router-index.ts << 'EOF'
import AccountRoutes from '@userfrosting/sprinkle-account/routes'
import AdminRoutes from '@userfrosting/sprinkle-admin/routes'
import ErrorRoutes from '@userfrosting/sprinkle-core/routes'
import { createRouter, createWebHistory } from 'vue-router'
const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '',
            redirect: { name: 'home' },
            component: () => import('../layouts/LayoutPage.vue'),
            children: [
                {
                    path: '/',
                    name: 'home',
                    component: () => import('../views/HomeView.vue')
                },
                {
                    path: '/about',
                    name: 'about',
                    meta: {
                        title: 'ABOUT'
                    },
                    component: () => import('../views/AboutView.vue')
                },
                // Include sprinkles routes
                ...AccountRoutes,
                ...ErrorRoutes
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
    ]
})
export default router
EOF

echo "✅ Created test router file"

# Apply the workflow transformations
echo ""
echo "Applying workflow transformations..."

# Step 1: Add imports
sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import { createC6AdminRoutes } from '@ssnukala\/sprinkle-c6admin\/routes'" router-index.ts
sed -i "/import AdminRoutes from '@userfrosting\/sprinkle-admin\/routes'/a import LayoutDashboard from '@userfrosting\/theme-pink-cupcake\/layouts\/LayoutDashboard.vue'" router-index.ts

echo "✅ Added imports"

# Step 2: Add C6Admin routes using the fixed awk script
awk '
/^    \]$/ && !done {
  print "        ,"
  print "        // C6Admin routes with their own layout"
  print "        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })"
  done=1
}
{ print }
' router-index.ts > router-index.ts.tmp

# Step 3: Verify and move
if [ ! -f router-index.ts.tmp ]; then
  echo "❌ Error: Failed to create router-index.ts.tmp"
  exit 1
fi

if grep -q "createC6AdminRoutes" router-index.ts.tmp; then
  echo "✅ C6Admin routes added successfully"
  mv router-index.ts.tmp router-index.ts
else
  echo "❌ Error: C6Admin routes not found in output"
  exit 1
fi

echo ""
echo "========================================="
echo "Verification Tests"
echo "========================================="

# Test 1: C6Admin routes present
if grep -q "...createC6AdminRoutes({ layoutComponent: LayoutDashboard })" router-index.ts; then
  echo "✅ Test 1: C6Admin routes present"
else
  echo "❌ Test 1: C6Admin routes missing"
  exit 1
fi

# Test 2: Leading comma present
if grep -B2 "// C6Admin routes with their own layout" router-index.ts | grep -q "^        ,$"; then
  echo "✅ Test 2: Leading comma present"
else
  echo "❌ Test 2: Leading comma missing"
  exit 1
fi

# Test 3: Routes array closes correctly after C6Admin routes
if grep -A1 "...createC6AdminRoutes" router-index.ts | grep -q "^    \]$"; then
  echo "✅ Test 3: Routes array closes correctly"
else
  echo "❌ Test 3: Routes array structure incorrect"
  exit 1
fi

# Test 4: No trailing comma after C6Admin routes
if grep "...createC6AdminRoutes" router-index.ts | grep -q ",$"; then
  echo "❌ Test 4: Unwanted trailing comma found"
  exit 1
else
  echo "✅ Test 4: No trailing comma (correct)"
fi

# Test 5: Imports are present
if grep -q "import LayoutDashboard from '@userfrosting/theme-pink-cupcake/layouts/LayoutDashboard.vue'" router-index.ts; then
  echo "✅ Test 5: LayoutDashboard import present"
else
  echo "❌ Test 5: LayoutDashboard import missing"
  exit 1
fi

if grep -q "import { createC6AdminRoutes } from '@ssnukala/sprinkle-c6admin/routes'" router-index.ts; then
  echo "✅ Test 6: createC6AdminRoutes import present"
else
  echo "❌ Test 6: createC6AdminRoutes import missing"
  exit 1
fi

echo ""
echo "========================================="
echo "Expected Output Structure"
echo "========================================="
echo "The C6Admin routes should be inserted like this:"
echo ""
echo "        }              ← AdminRoutes closing brace"
echo "        ,              ← Comma separator"
echo "        // C6Admin routes with their own layout"
echo "        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard })"
echo "    ]                  ← Routes array closing bracket"
echo ""

echo "========================================="
echo "Actual Output (last 10 lines)"
echo "========================================="
tail -10 router-index.ts

# Cleanup
cd /
rm -rf "$TEST_DIR"

echo ""
echo "========================================="
echo "✅ All tests passed!"
echo "========================================="
