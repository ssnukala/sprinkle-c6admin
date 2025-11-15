# Screenshot Timeout Fix

**Issue:** GitHub Actions workflow failing at screenshot capture step
**Workflow Run:** https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19385870084/job/55472618029#logs
**Date:** 2025-11-15

## Problem Description

The integration test workflow was failing with a Playwright timeout error:

```
❌ Error taking screenshots:
page.waitForSelector: Timeout 10000ms exceeded.
Call log:
  - waiting for locator('.uk-card input[data-test="username"]') to be visible
```

### Root Cause

The screenshot script (`.github/scripts/take-authenticated-screenshots.js`) was attempting to interact with the login form immediately after page navigation, but:

1. **Vue.js Rendering Delay**: Even though `waitUntil: 'networkidle'` ensured the page resources loaded, Vue.js still needed time to render the login form components
2. **Too Short Timeout**: 10-second timeout was insufficient for the Vue.js app to fully initialize
3. **Too Specific Selector**: Using `.uk-card input[data-test="username"]` was more restrictive than needed
4. **Poor Error Handling**: Error screenshot was creating a new blank page instead of capturing the actual failed page state

## Solution Implemented

### 1. Add Explicit Wait for Vue.js Rendering

```javascript
// Give Vue.js time to render the login form after page load
console.log('⏳ Waiting for login form to render...');
await page.waitForTimeout(3000);
```

**Why**: This gives the Vue.js application time to mount and render components after the HTML loads.

### 2. Increase Selector Timeout

```javascript
// Before: 10 seconds
await page.waitForSelector('.uk-card input[data-test="username"]', { timeout: 10000 });

// After: 30 seconds with explicit state check
await page.waitForSelector('input[data-test="username"]', { 
    timeout: 30000, 
    state: 'visible' 
});
```

**Why**: 30 seconds provides ample time for slower CI environments.

### 3. Simplify Selector

```javascript
// Before: Too specific
'.uk-card input[data-test="username"]'

// After: More flexible
'input[data-test="username"]'
```

**Why**: The `.uk-card` qualifier was unnecessary and could fail if the HTML structure changes. The `data-test` attribute alone is sufficient and more maintainable.

### 4. Enhanced Debugging

Added comprehensive debugging when selector fails:

```javascript
try {
    await page.waitForSelector('input[data-test="username"]', { timeout: 30000, state: 'visible' });
    console.log('✅ Username input field found');
} catch (error) {
    console.error('❌ Could not find username input field');
    console.log('📝 Available inputs on page:');
    const inputs = await page.$$('input');
    for (let i = 0; i < inputs.length; i++) {
        const attrs = await inputs[i].evaluate(el => ({
            type: el.type,
            name: el.name,
            id: el.id,
            'data-test': el.getAttribute('data-test'),
            class: el.className
        }));
        console.log(`  Input ${i + 1}:`, JSON.stringify(attrs));
    }
    throw error;
}
```

**Why**: If the issue occurs again, we'll see exactly what input fields are present on the page.

### 5. Improved Error Screenshot

```javascript
// Before: Created a new blank page
const errorPage = await browser.newPage();
await errorPage.screenshot({ path: '/tmp/screenshot_error.png', fullPage: true });

// After: Capture the actual page where error occurred
const pages = await browser.pages();
if (pages.length > 0) {
    const currentPage = pages[0];
    await currentPage.screenshot({ path: '/tmp/screenshot_error.png', fullPage: true });
    
    // Log current URL and page title for debugging
    const currentUrl = currentPage.url();
    const title = await currentPage.title();
    console.log(`📍 Error occurred on page: ${currentUrl}`);
    console.log(`📄 Page title: ${title}`);
}
```

**Why**: This captures the actual state of the page when the error occurred, making debugging much easier.

## Changes Summary

**File Modified:** `.github/scripts/take-authenticated-screenshots.js`

### Key Changes:
- ✅ Added 3-second wait after page load for Vue.js rendering
- ✅ Increased timeout from 10s to 30s
- ✅ Removed `.uk-card` qualifier from selectors
- ✅ Added `state: 'visible'` to waitForSelector
- ✅ Added comprehensive debug logging for failed selectors
- ✅ Improved error screenshot to capture actual page state
- ✅ Added verbose logging throughout login process

## Testing

The fix should be verified by:

1. Running the integration test workflow on the `copilot/fix-screenshot-timeout-error` branch
2. Checking that the login process completes successfully
3. Verifying that all 14 screenshots are captured
4. If it still fails, the enhanced debugging output will help identify the issue

## Expected Outcome

The screenshot step should now:
1. ✅ Successfully wait for Vue.js to render
2. ✅ Find and interact with login form elements
3. ✅ Complete authentication
4. ✅ Capture all 14 screenshots of C6Admin pages

If the issue persists, the enhanced debugging output will provide:
- List of all input fields available on the page
- Current URL where error occurred
- Page title at time of error
- Screenshot of actual page state (not blank page)

## Related Files

- `.github/scripts/take-authenticated-screenshots.js` - The main screenshot script
- `.github/workflows/integration-test.yml` - The workflow that runs this script

## Commit

**Commit Hash:** 20e881a
**Commit Message:** Fix screenshot timeout by improving login form detection

## Next Steps

1. Monitor the GitHub Actions workflow run for the branch
2. If successful, merge the fix to main
3. If still failing, review enhanced debug output to determine next steps
