# UFAlert and UFModal Detection Enhancements

## Overview

Enhanced integration testing scripts to detect UFAlert components and UFModal dialogs, following the approach used in sprinkle-crud6 PR #197.

## Changes Made

### 1. Screenshot Script (`take-screenshots-modular.js`)

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
ℹ️  Found 1 open modal(s):
   1. [role="dialog"] - "Edit User"
📸 Modal screenshot: /tmp/screenshot_edit_button_modal.png

✏️  Filling edit form...
✅ Modified first name: "John" → "TestFirstName_1234567890"

🔍 Checking for alerts after submission...
ℹ️  Found 1 alert(s):
   1. [SUCCESS] User updated successfully
✅ Edit form submitted successfully
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
