#!/bin/bash

# Script Validation Test
# Validates that the button testing scripts have correct structure and syntax

echo "========================================"
echo "Validating Button Testing Scripts"
echo "========================================"
echo ""

SCRIPTS=(
    ".github/scripts/test-user-detail-buttons.js"
    ".github/backup/take-authenticated-screenshots.js"
)

PASS_COUNT=0
FAIL_COUNT=0

for script in "${SCRIPTS[@]}"; do
    echo "Testing: $script"
    
    # Check if file exists
    if [ ! -f "$script" ]; then
        echo "  ❌ File not found"
        ((FAIL_COUNT++))
        continue
    fi
    
    # Check shebang
    if head -n 1 "$script" | grep -q "^#!/usr/bin/env node"; then
        echo "  ✅ Shebang present"
    else
        echo "  ❌ Missing or incorrect shebang"
        ((FAIL_COUNT++))
        continue
    fi
    
    # Check for playwright import
    if grep -q "import.*chromium.*from.*playwright" "$script"; then
        echo "  ✅ Playwright import found"
    else
        echo "  ❌ Playwright import missing"
        ((FAIL_COUNT++))
        continue
    fi
    
    # Check for login functionality
    if grep -q "waitForSelector.*username" "$script" && grep -q "fill.*password" "$script"; then
        echo "  ✅ Login functionality present"
    else
        echo "  ❌ Login functionality missing"
        ((FAIL_COUNT++))
        continue
    fi
    
    # Check for button testing (specific to button testing scripts)
    if echo "$script" | grep -q "test-user-detail-buttons.js\|take-authenticated-screenshots.js"; then
        if grep -q "button.*\$\$\|Testing.*button\|click.*button" "$script"; then
            echo "  ✅ Button testing functionality found"
        else
            echo "  ❌ Button testing functionality missing"
            ((FAIL_COUNT++))
            continue
        fi
    fi
    
    # Check for screenshot functionality
    if grep -q "screenshot.*path.*fullPage" "$script"; then
        echo "  ✅ Screenshot functionality present"
    else
        echo "  ❌ Screenshot functionality missing"
        ((FAIL_COUNT++))
        continue
    fi
    
    # Check syntax (Node.js --check)
    if node --check "$script" 2>/dev/null; then
        echo "  ✅ JavaScript syntax valid"
    else
        echo "  ❌ JavaScript syntax error"
        ((FAIL_COUNT++))
        continue
    fi
    
    echo "  ✅ All checks passed"
    ((PASS_COUNT++))
    echo ""
done

echo "========================================"
echo "Validation Summary"
echo "========================================"
echo "Scripts tested: ${#SCRIPTS[@]}"
echo "Passed: $PASS_COUNT"
echo "Failed: $FAIL_COUNT"
echo "========================================"

if [ $FAIL_COUNT -eq 0 ]; then
    echo "✅ All scripts validated successfully"
    exit 0
else
    echo "❌ Some scripts failed validation"
    exit 1
fi
