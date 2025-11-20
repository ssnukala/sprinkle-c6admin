# UFAlert and UFModal Detection Enhancements

## Overview

Enhanced integration testing scripts to detect UFAlert components and UFModal dialogs, following the approach used in sprinkle-crud6 PR #197. Additionally addresses the "navbar in middle" symptom when modals fail to render content.

## The "Navbar in Middle" Issue

### Symptom
Screenshots show the navbar moved to the middle of the screen instead of showing the expected modal dialog.

### Root Cause
This occurs when:
1. A modal is triggered (backdrop appears, pushing content down)
2. The modal dialog element exists in DOM
3. **But the modal content fails to render** (Vue component doesn't mount)

The backdrop shifts the page layout (moving navbar to middle), but without the dialog content, users see a broken state.

### Detection
The enhanced scripts now:
- Wait for modal **dialog content** to render, not just the modal element
- Check if modal dialog has child elements (actual content)
- Log warnings when backdrop is visible but content is missing
- Distinguish between "modal exists" and "modal has content"

## Changes Made

### 1. Browser Configuration Enhancements

**Disabled Security Features for Testing:**
```javascript
const browser = await chromium.launch({
    headless: true,
    args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-web-security',  // Allow cross-origin requests
        '--disable-features=IsolateOrigins,site-per-process'
    ]
});

const context = await browser.newContext({
    viewport: { width: 1280, height: 720 },
    ignoreHTTPSErrors: true,
    javaScriptEnabled: true,  // Explicitly enable
    bypassCSP: true  // Bypass Content Security Policy
});
```

**Why:** Some security policies can block Vue.js from loading or executing properly in test environments.

### 2. Vue.js App Detection

Added helper function to verify Vue.js is actually mounted:

```javascript
async function waitForVueApp(page, timeout = 10000) {
    await page.waitForFunction(() => {
        const app = document.querySelector('#app, [data-v-app]');
        if (!app) return false;
        
        // Check for Vue 3
        if (window.__VUE_DEVTOOLS_GLOBAL_HOOK__) return true;
        
        // Check for Vue 2
        if (window.Vue) return true;
        
        // Check if content is rendered
        const hasContent = app.querySelector('.uk-card, .card, main');
        return hasContent !== null;
    }, { timeout });
}
```

### 3. JavaScript Status Checking

Added comprehensive JavaScript environment check:

```javascript
async function checkJavaScriptStatus(page) {
    const status = await page.evaluate(() => ({
        jsEnabled: typeof window !== 'undefined',
        hasVue: !!(window.Vue || window.__VUE_DEVTOOLS_GLOBAL_HOOK__),
        hasVueRouter: !!(window.VueRouter || ...),
        appMounted: appElement && appElement.children.length > 0,
        appChildrenCount: appElement?.children.length || 0
    }));
    
    console.log(`JavaScript enabled: ${status.jsEnabled ? '✅' : '❌'}`);
    console.log(`Vue.js detected: ${status.hasVue ? '✅' : '❌'}`);
    // ... more logging
}
```

### 4. Enhanced Modal Content Detection

**Before (Insufficient):**
```javascript
// Only checked if modal element exists
const modals = document.querySelectorAll('.uk-modal.uk-open');
```

**After (Content Verification):**
```javascript
// Wait for modal backdrop
const modalBackdrop = await page.evaluate(() => {
    return document.querySelector('.uk-modal-page, .modal-backdrop') !== null;
});

if (modalBackdrop) {
    // Wait for actual dialog content to render
    await page.waitForSelector(
        '[role="dialog"] .uk-modal-dialog',
        { state: 'visible', timeout: 5000 }
    );
    
    // Additional wait for Vue to populate content
    await page.waitForTimeout(2000);
}

// Verify modal has content
const modals = await page.evaluate(() => {
    const dialog = el.querySelector('.uk-modal-dialog, .modal-dialog');
    const hasContent = dialog && dialog.children.length > 0;
    
    return {
        visible: true,
        hasContent,  // ← Key addition
        contentChildCount: dialog?.children.length || 0
    };
});
```

### 5. Console Logging Enhancements

**All browser console messages now logged:**
```javascript
page.on('console', msg => {
    console.log(`[Browser ${msg.type().toUpperCase()}]:`, msg.text());
});

page.on('requestfailed', request => {
    console.error(`[Request Failed]: ${request.url()}`);
});
```

This helps identify JavaScript loading errors, network failures, and Vue mounting issues.

## Screenshot Script Enhancements

**UFAlert Detection:**
- Automatically detects alert components on every page
- Identifies error alerts (danger/error type)
- **Fails the test** if error alerts are found
- Logs all alerts with their type and content
- Takes error screenshots for debugging

**UFModal Detection:**
- Detects open modals using multiple selectors:
  - `[role="dialog"]`
  - `.uk-modal.uk-open`
  - `.modal.show`
  - `.uf-modal`
- Verifies modal visibility (not just presence in DOM)
- Extracts and logs modal titles
- Takes separate screenshots when modals are present (`*_with_modal.png`)

**Alert Detection Logic:**
```javascript
const alerts = await page.evaluate(() => {
    const alertElements = document.querySelectorAll('[data-alert], .uf-alert, .alert');
    return Array.from(alertElements).map(el => ({
        text: el.textContent?.trim() || '',
        isError: el.classList.contains('alert-danger') || 
                el.classList.contains('uk-alert-danger') ||
                el.getAttribute('data-alert-type') === 'error',
        isWarning: el.classList.contains('alert-warning') || 
                  el.classList.contains('uk-alert-warning')
    }));
});

// Fail if error alerts found
const errorAlerts = alerts.filter(a => a.isError);
if (errorAlerts.length > 0) {
    console.error(`❌ FAIL: Found ${errorAlerts.length} error alert(s)!`);
    // Take error screenshot and fail
}
```

**Modal Detection Logic:**
```javascript
const modals = await page.evaluate(() => {
    const modalSelectors = [
        '[role="dialog"]',
        '.uk-modal.uk-open',
        '.modal.show',
        '.uf-modal'
    ];
    
    const foundModals = [];
    modalSelectors.forEach(selector => {
        const elements = document.querySelectorAll(selector);
        elements.forEach(el => {
            // Verify visibility
            if (el.offsetParent !== null || getComputedStyle(el).display !== 'none') {
                foundModals.push({
                    selector,
                    visible: true,
                    title: el.querySelector('.modal-title, .uk-modal-title')?.textContent
                });
            }
        });
    });
    
    return foundModals;
});
```

### 2. Button Testing Script (`test-user-detail-buttons.js`)

**Enhanced Modal Verification:**
- Verifies modal visibility (not just DOM presence)
- Detects multiple modal types
- Extracts and logs modal titles
- Confirms modal is actually displayed before taking screenshots

**Alert Detection After Form Submission:**
- Checks for alerts after submitting forms
- Distinguishes between success and error alerts
- **Marks test as failed** if error alerts appear
- Logs alert content for debugging

**Example Output:**
```
🔍 Checking for UFModal components...
⏳ Modal backdrop detected - waiting for modal content to render...
✅ Modal dialog content appeared
ℹ️  Found 1 open modal(s):
   1. [role="dialog"] - "Edit User" [✅ HAS CONTENT]
      Dialog found: Yes, Children: 3
📸 Modal screenshot: /tmp/screenshot_edit_button_modal.png

✏️  Filling edit form...
✅ Modified first name: "John" → "TestFirstName_1234567890"

🔍 Checking for alerts after submission...
ℹ️  Found 1 alert(s):
   1. [SUCCESS] User updated successfully
✅ Edit form submitted successfully
```

## Diagnostic Output

### Normal Modal (Working)
```
🔍 Checking for UFModal components...
⏳ Modal backdrop detected - waiting for modal content to render...
✅ Modal dialog content appeared
ℹ️  Found 1 open modal(s):
   1. [role="dialog"] - "Edit Role" [✅ HAS CONTENT]
      Dialog found: Yes, Children: 5
📸 Modal screenshot saved: screenshot_roles_detail_with_modal.png
```

### "Navbar in Middle" Issue (Broken)
```
🔍 Checking for UFModal components...
⏳ Modal backdrop detected - waiting for modal content to render...
⚠️  Modal dialog content did not appear within 5 seconds
⚠️  This may explain "navbar in middle" symptom - backdrop visible but no dialog
ℹ️  Found 1 open modal(s):
   1. .uk-modal.uk-open - "" [⚠️  NO CONTENT]
      Dialog found: Yes, Children: 0
⚠️  Modal detected but NO CONTENT rendered!
⚠️  This explains "navbar in middle" - backdrop visible, dialog empty
📸 Modal screenshot saved: screenshot_roles_detail_with_modal.png
```

### Vue.js Not Loaded (Critical)
```
🔍 Checking JavaScript status...
   JavaScript enabled: ✅
   Vue.js detected: ❌  <-- Problem!
   Vue Router detected: ❌
   App element found: ✅
   App mounted: ❌ (0 children)
⏳ Waiting for Vue.js app to mount...
⚠️  Vue.js app mount timeout: Waiting for function failed
```

## Test Failure Scenarios

### Error Alerts Detected
If error alerts are found on a page, the test will:
1. Log all error alerts with their content
2. Take an error screenshot (`*_error.png`)
3. Increment the fail count
4. Skip to the next test

**Example Failure:**
```
❌ FAIL: Found 2 error alert(s) on page!
   1. Invalid email address
   2. Username already exists
📸 Error screenshot saved: /tmp/screenshot_users_list_error.png
```

### Modal Not Displayed
If a modal is expected but not visible:
1. Log warning about modal not appearing
2. Mark test as failed
3. Continue with other tests

## Screenshot Files Generated

### Regular Screenshots
- `screenshot_{name}.png` - Standard page screenshot

### Modal Screenshots  
- `screenshot_{name}_with_modal.png` - Page with visible modal

### Error Screenshots
- `screenshot_{name}_error.png` - Page with error alerts

### Debug Screenshots
- `screenshot_{name}_debug.png` - Authentication or navigation failures

### Button Test Screenshots
- `screenshot_button_{action}_modal.png` - Modal opened by button
- `screenshot_button_{action}_filled.png` - Form filled in
- `screenshot_button_{action}_after.png` - After action completed

## Alert Types Detected

### Error Alerts (Test Fails)
- `.alert-danger`
- `.uk-alert-danger`
- `[data-alert-type="error"]`
- `[data-alert-type="danger"]`

### Warning Alerts (Logged Only)
- `.alert-warning`
- `.uk-alert-warning`
- `[data-alert-type="warning"]`

### Success Alerts (Logged Only)
- `.alert-success`
- `.uk-alert-success`
- `[data-alert-type="success"]`

## Modal Selectors

The scripts detect modals using these selectors:
1. `[role="dialog"]` - ARIA dialog role
2. `.uk-modal.uk-open` - UIkit modals (open state)
3. `.modal.show` - Bootstrap modals (shown state)
4. `.uf-modal` - UserFrosting custom modals

## Benefits

### 1. Early Error Detection
- Catches errors before manual review
- Prevents false positives in CI/CD
- Provides immediate feedback

### 2. Better Debugging
- Error screenshots show exact state when errors occur
- Alert content logged for analysis
- Modal verification confirms UI interactions

### 3. Modal Verification
- Confirms modals actually display
- Verifies modal visibility (not just DOM presence)
- Captures modal state in screenshots

### 4. Consistent Testing
- Same approach as sprinkle-crud6
- Standardized alert detection
- Reliable modal verification

## Integration with CI/CD

The enhanced scripts integrate seamlessly with existing GitHub Actions workflows:

```yaml
- name: Take screenshots of C6Admin pages (Modular)
  run: |
    cd userfrosting
    node take-screenshots-modular.js integration-test-paths.json 2>&1 | tee /tmp/screenshot.log
    SCREENSHOT_EXIT_CODE=${PIPESTATUS[0]}
    
    # Exit code 0 = all screenshots successful, no error alerts
    # Exit code > 0 = failures or error alerts detected
    exit $SCREENSHOT_EXIT_CODE
```

## References

- sprinkle-crud6 PR #197: UFAlert detection implementation
- UserFrosting theme-pink-cupcake UFModal component: Modal structure reference
- Integration test workflow: `.github/workflows/integration-test-modular.yml`

## Testing the Enhancements

To test locally:

```bash
# Test screenshots with alert detection
node .github/scripts/take-screenshots-modular.js \
  .github/config/integration-test-paths.json \
  http://localhost:8080 admin admin123

# Test button interactions with modal verification
node .github/scripts/test-user-detail-buttons.js \
  http://localhost:8080 admin admin123 2
```

Look for:
- ✅ Alert detection messages
- ✅ Modal verification messages
- ❌ Error alerts causing test failures
- 📸 Modal screenshots with `_with_modal.png` suffix
