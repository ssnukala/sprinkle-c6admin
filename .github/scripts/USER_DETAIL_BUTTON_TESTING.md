# User Detail Button Testing Scripts

This directory contains scripts for **full automation testing** of button functionality on the C6Admin user detail page (`/c6/admin/users/{id}`).

## Overview

The user detail page in C6Admin (powered by CRUD6) contains various action buttons. This script provides **complete form automation and functional testing**:
- **Edit/View**: Fills and submits the user edit form with test data
- **Reset Password**: Fills and submits the password reset form with a test password
- **Disable/Enable**: Toggles the user's active status and verifies the change
- **Delete**: Opens confirmation dialog but cancels to preserve test data

These scripts don't just click buttons - they **fully test the functionality** by filling forms, submitting data, and verifying results.

## Scripts

### 1. test-user-detail-buttons.js

A dedicated script for **comprehensive functional testing** of buttons on the user detail page.

**Features:**
- Automatically discovers all buttons on the page
- **FULLY TESTS** each button by:
  - **Edit**: Modifies first name and last name, submits the form
  - **Password**: Fills in new password fields and submits
  - **Disable/Enable**: Clicks button, confirms action, verifies status change
  - **Delete**: Opens confirmation but cancels (preserves test data)
- Captures screenshots at each stage (before, modal, filled form, after)
- Detects modals, dialogs, and navigation changes
- Generates a detailed JSON report of all test results

**Usage:**
```bash
node test-user-detail-buttons.js <base_url> <username> <password> [user_id]
```

**Example:**
```bash
node test-user-detail-buttons.js http://localhost:8080 admin admin123 1
```

**Output:**
- Screenshots: `/tmp/screenshot_user_{id}_*.png`
- Test results: `/tmp/user_detail_button_test_results.json`
- Before/after screenshots for each button: `/tmp/screenshot_button_*_before.png` and `/tmp/screenshot_button_*_after.png`
- Modal screenshots: `/tmp/screenshot_button_*_modal.png`
- **Filled form screenshots**: `/tmp/screenshot_button_*_filled.png`

**Example Output:**
```
========================================
Testing User Detail Page Buttons
========================================
Base URL: http://localhost:8080
Username: admin
User ID to test: 1

📍 Step 1: Navigating to login page...
✅ Login page loaded
🔐 Logging in...
✅ Logged in successfully

📍 Step 2: Navigating to user detail page: /c6/admin/users/1
✅ User detail page loaded: http://localhost:8080/c6/admin/users/1
📸 Initial screenshot saved: /tmp/screenshot_user_1_initial.png

📍 Step 3: Discovering buttons and interactive elements...
   Found 12 button elements
   Button 1: "Edit" (class: uk-button uk-button-primary, data-test: edit-user)
   Button 2: "Reset Password" (class: uk-button uk-button-default, data-test: reset-password)
   Button 3: "Disable" (class: uk-button uk-button-danger, data-test: toggle-user)
   Button 4: "Delete" (class: uk-button uk-button-danger, data-test: delete-user)

📍 Step 4: Testing specific button actions...

🔘 Testing Edit/View button with form submission: "Edit"
   📸 Before screenshot: /tmp/screenshot_button_Edit_before.png
   🖱️  Clicking Edit button...
   ℹ️  Edit form modal detected
   📸 Modal screenshot: /tmp/screenshot_button_Edit_modal.png
   ✏️  Filling edit form...
   ✅ Modified first name: "Admin" → "TestFirstName_1234567890"
   ✅ Modified last name: "User" → "TestLastName_1234567890"
   📸 Form filled screenshot: /tmp/screenshot_button_Edit_filled.png
   🖱️  Clicking Submit button: "Save"
   ✅ Edit form submitted successfully
   📸 After screenshot: /tmp/screenshot_button_Edit_after.png

🔘 Testing Password button with form submission: "Reset Password"
   📸 Before screenshot: /tmp/screenshot_button_Reset_Password_before.png
   🖱️  Clicking Password Reset button...
   ℹ️  Password reset form modal detected
   📸 Modal screenshot: /tmp/screenshot_button_Reset_Password_modal.png
   ✏️  Filling password reset form...
   ✅ Filled password field 1
   ✅ Filled password field 2 (confirmation)
   📸 Form filled screenshot: /tmp/screenshot_button_Reset_Password_filled.png
   🖱️  Clicking Submit button: "Update Password"
   ✅ Password reset form submitted successfully
   📸 After screenshot: /tmp/screenshot_button_Reset_Password_after.png

🔘 Testing Disable/Enable button with status verification: "Disable"
   📸 Before screenshot: /tmp/screenshot_button_Disable_before.png
   ℹ️  Initial button state: "Disable"
   🖱️  Clicking Disable button...
   ℹ️  Confirmation modal detected
   📸 Modal screenshot: /tmp/screenshot_button_Disable_modal.png
   🖱️  Clicking Confirm button: "Yes"
   🔄 Reloading page to verify status change...
   ℹ️  Button state after action: "Enable"
   ✅ User disabled successfully (button changed from "Disable" to "Enable")
   📸 After screenshot: /tmp/screenshot_button_Disable_after.png

========================================
Test Summary
========================================
Total buttons tested: 4
Successful tests: 4
Failed tests: 0
========================================
✅ Edit: Edit form submitted successfully
✅ Reset Password: Password reset form submitted successfully
✅ Disable: User disabled successfully (button changed from "Disable" to "Enable")
✅ Delete: Button clicked, modal/dialog appeared

✅ User detail button testing completed
```

### 2. take-authenticated-screenshots.js (Enhanced)

The original authenticated screenshots script has been enhanced to include button testing on the user detail page.

**Features:**
- Takes screenshots of all C6Admin pages
- **NEW**: Tests buttons on the user detail page
- **NEW**: Captures before/after screenshots for each button
- **NEW**: Detects and screenshots modals/dialogs

**Usage:**
```bash
node take-authenticated-screenshots.js <base_url> <username> <password>
```

**Example:**
```bash
node take-authenticated-screenshots.js http://localhost:8080 admin admin123
```

**Button Testing Section:**
After taking the initial screenshot of `/c6/admin/users/1`, the script:
1. Discovers all visible buttons on the page
2. Tests the following actions (if buttons are found):
   - Edit/View
   - Reset Password
   - Disable/Enable
   - Delete (cancelled to preserve data)
3. Takes screenshots before and after each button click
4. Handles modals by taking screenshots and closing them

## Integration with CI/CD

### GitHub Actions Workflow

To integrate button testing into your CI/CD pipeline:

```yaml
- name: Test user detail page buttons
  run: |
    cd userfrosting
    cp ../sprinkle-c6admin/.github/scripts/test-user-detail-buttons.js .
    
    # Test buttons on user detail page
    node test-user-detail-buttons.js http://localhost:8080 admin admin123 1
    
    # Check test results
    if [ -f /tmp/user_detail_button_test_results.json ]; then
      echo "Test results:"
      cat /tmp/user_detail_button_test_results.json
    fi

- name: Upload button test screenshots
  if: always()
  uses: actions/upload-artifact@v4
  with:
    name: user-detail-button-tests
    path: /tmp/screenshot_button_*.png
    if-no-files-found: ignore
    retention-days: 30

- name: Upload test results
  if: always()
  uses: actions/upload-artifact@v4
  with:
    name: button-test-results
    path: /tmp/user_detail_button_test_results.json
    if-no-files-found: ignore
    retention-days: 30
```

### Using Enhanced Authenticated Screenshots

The enhanced `take-authenticated-screenshots.js` script is already integrated into the workflow and will automatically test buttons on the user detail page as part of the normal screenshot capture process.

## Test Results JSON Format

The `test-user-detail-buttons.js` script generates a JSON file with the following structure:

```json
{
  "timestamp": "2024-11-18T17:30:00.000Z",
  "baseUrl": "http://localhost:8080",
  "userId": "1",
  "tests": [
    {
      "buttonText": "Edit",
      "elementType": "button",
      "index": 0,
      "success": true,
      "message": "Button clicked, modal/dialog appeared",
      "screenshotPath": "/tmp/screenshot_button_Edit_before.png",
      "timestamp": "2024-11-18T17:30:15.000Z"
    },
    {
      "buttonText": "Reset Password",
      "elementType": "button",
      "index": 2,
      "success": true,
      "message": "Button clicked, modal/dialog appeared",
      "screenshotPath": "/tmp/screenshot_button_Reset_Password_before.png",
      "timestamp": "2024-11-18T17:30:20.000Z"
    }
  ]
}
```

## Troubleshooting

### No buttons found

If the script reports "No buttons found", this could mean:
1. The page hasn't loaded completely - increase the wait timeout
2. The user detail page uses a different UI framework
3. The buttons are hidden or disabled for the test user

### Buttons not responding

If buttons don't respond to clicks:
1. Check if the buttons require specific permissions
2. Verify that the test user (admin) has the necessary permissions
3. Check browser console logs for JavaScript errors

### Modal not closing

If modals don't close automatically:
1. The script looks for buttons with "cancel" or "close" text
2. You may need to adjust the modal closing logic for your specific UI
3. Check the screenshot to see what the modal looks like

## Best Practices

1. **Test User**: Always use a test user account (not user ID 1 in production)
2. **Data Preservation**: Delete actions are automatically cancelled to preserve test data
3. **Screenshots**: Review screenshots to verify button behavior visually
4. **Timeouts**: Adjust timeout values based on your application's performance
5. **Permissions**: Ensure the test user has permissions to access all buttons

## Future Enhancements

Potential improvements for these scripts:

1. **Configurable Actions**: Define button tests in JSON configuration
2. **Form Validation**: Test form submissions after clicking buttons
3. **API Verification**: Verify API calls after button actions
4. **Performance Metrics**: Measure response times for button actions
5. **Accessibility Testing**: Verify keyboard navigation and ARIA labels
6. **Mobile Testing**: Test button functionality on mobile viewports

## Related Documentation

- [Integration Testing Guide](../../docs/INTEGRATION_TESTING.md) - Overall integration testing approach
- [Modular Testing README](../MODULAR_TESTING_README.md) - Configuration-driven testing framework
- [CRUD6 Documentation](https://github.com/ssnukala/sprinkle-crud6) - CRUD6 component documentation
