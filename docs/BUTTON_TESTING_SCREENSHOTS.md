# User Detail Button Testing Screenshots

## Overview

This document describes the screenshots that are automatically generated when the integration test workflow runs the **fully automated** user detail button testing script (`test-user-detail-buttons.js`).

The button testing script **fully tests functionality** by:
- **Filling and submitting forms** (Edit, Password Reset)
- **Verifying actions complete** (Disable/Enable status change)
- **Capturing screenshots at every stage** (before, modal, filled, after)

This provides complete **functional testing automation**, not just clicking buttons.

## Workflow Step

The GitHub Actions workflow includes a step called **"Test user detail page buttons"** that:

1. Copies the `test-user-detail-buttons.js` script to the test environment
2. Runs the script with: `node test-user-detail-buttons.js http://localhost:8080 admin admin123 1`
3. **Fills and submits forms** to test actual functionality
4. Captures screenshots of all button interactions (before, modal, filled, after)
5. Generates a JSON file with detailed test results
6. Uploads all artifacts

## Screenshots Generated

The script generates the following screenshots for the user detail page:

### Initial Page State

**File**: `/tmp/screenshot_user_1_initial.png`

- **Description**: Full-page screenshot of the user detail page before any interactions
- **Purpose**: Shows the initial state of the page with all buttons visible
- **When taken**: Immediately after navigating to `/c6/admin/users/1`

### Final Page State

**File**: `/tmp/screenshot_user_1_final.png`

- **Description**: Full-page screenshot of the user detail page after all button tests complete
- **Purpose**: Shows the final state of the page after all interactions
- **When taken**: After all button tests are completed

### Button Interaction Screenshots

For each button tested, the script captures three screenshots:

#### 1. Edit/View Button

**Before**: `/tmp/screenshot_button_Edit_before.png` or `/tmp/screenshot_button_View_before.png`
- Shows the page state before clicking the Edit/View button
- Captures the entire page with the button highlighted

**Modal**: `/tmp/screenshot_button_Edit_modal.png` or `/tmp/screenshot_button_View_modal.png`
- Shows the modal/dialog that appears after clicking the button
- Captures the edit form with original user data
- Only generated if a modal appears

**Filled**: `/tmp/screenshot_button_Edit_filled.png` ✨ **NEW**
- Shows the form after filling in test data
- Captures modified first name and last name fields
- Demonstrates form automation in action

**After**: `/tmp/screenshot_button_Edit_after.png` or `/tmp/screenshot_button_View_after.png`
- Shows the page state after submitting the form
- Confirms the changes were saved successfully

#### 2. Reset Password Button

**Before**: `/tmp/screenshot_button_Reset_Password_before.png`
- Shows the page state before clicking the Reset Password button

**Modal**: `/tmp/screenshot_button_Reset_Password_modal.png`
- Shows the password reset modal/dialog
- Captures the empty password reset form
- Only generated if a modal appears

**Filled**: `/tmp/screenshot_button_Reset_Password_filled.png` ✨ **NEW**
- Shows the form after filling in test password
- Captures both password and confirmation fields filled
- Demonstrates password form automation

**After**: `/tmp/screenshot_button_Reset_Password_after.png`
- Shows the page state after submitting the password reset
- Confirms the password was updated successfully

#### 3. Disable/Enable Button

**Before**: `/tmp/screenshot_button_Disable_before.png` or `/tmp/screenshot_button_Enable_before.png`
- Shows the page state before toggling the user's active status
- Button text depends on current user state (active or disabled)

**Modal**: `/tmp/screenshot_button_Disable_modal.png` or `/tmp/screenshot_button_Enable_modal.png`
- Shows the confirmation modal if one appears
- Only generated if a modal appears

**After**: `/tmp/screenshot_button_Disable_after.png` or `/tmp/screenshot_button_Enable_after.png`
- Shows the page state after the status change was confirmed
- **Verifies** the button text changed (e.g., "Disable" → "Enable")
- Demonstrates functional verification of status toggle

#### 4. Delete Button

**Before**: `/tmp/screenshot_button_Delete_before.png`
- Shows the page state before clicking the Delete button

**Modal**: `/tmp/screenshot_button_Delete_modal.png`
- Shows the delete confirmation modal
- Captures the "Are you sure?" dialog
- Only generated if a modal appears

**After**: `/tmp/screenshot_button_Delete_after.png`
- Shows the page state after canceling the delete operation
- Delete is always cancelled to preserve test data

## Test Results JSON

**File**: `/tmp/user_detail_button_test_results.json`

The script also generates a JSON file with detailed test results:

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
      "message": "Edit form submitted successfully",
      "screenshotPath": "/tmp/screenshot_button_Edit_before.png",
      "timestamp": "2024-11-18T17:30:15.000Z"
    },
    {
      "buttonText": "Reset Password",
      "elementType": "button",
      "index": 2,
      "success": true,
      "message": "Password reset form submitted successfully",
      "screenshotPath": "/tmp/screenshot_button_Reset_Password_before.png",
      "timestamp": "2024-11-18T17:30:20.000Z"
    },
    {
      "buttonText": "Disable",
      "elementType": "button",
      "index": 4,
      "success": true,
      "message": "User disabled successfully (button changed from \"Disable\" to \"Enable\")",
      "screenshotPath": "/tmp/screenshot_button_Disable_before.png",
      "timestamp": "2024-11-18T17:30:25.000Z"
    },
    {
      "buttonText": "Delete",
      "elementType": "button",
      "index": 6,
      "success": true,
      "message": "Button clicked, modal/dialog appeared",
      "screenshotPath": "/tmp/screenshot_button_Delete_before.png",
      "timestamp": "2024-11-18T17:30:30.000Z"
    }
  ]
}
```

## Artifacts Available for Download

After the workflow completes, three artifacts are available for download:

### 1. integration-test-screenshots-c6admin

Contains screenshots of all C6Admin pages:
- Dashboard
- Users list
- User detail
- Groups list
- Group detail
- Roles list
- Role detail
- Permissions list
- Permission detail
- Activities page
- Config pages (System Info, Cache Management)

### 2. user-detail-button-test-screenshots

Contains all button interaction screenshots:
- Initial and final page states
- Before/modal/filled/after screenshots for each button
- **Filled form screenshots** showing test data entered
- Typical count: 12-18 screenshots depending on buttons found (more screenshots due to filled form captures)

### 3. user-detail-button-test-results

Contains the JSON test results file:
- Detailed results for each button test
- Success/failure status
- Error messages if any
- Screenshot paths
- Timestamps

## Viewing the Screenshots

To view the screenshots from a workflow run:

1. Go to the [GitHub Actions page](https://github.com/ssnukala/sprinkle-c6admin/actions)
2. Click on the most recent workflow run
3. Scroll to the bottom of the page
4. Look for the **Artifacts** section
5. Download the artifacts you want to review:
   - **integration-test-screenshots-c6admin** - All page screenshots
   - **user-detail-button-test-screenshots** - Button interaction screenshots
   - **user-detail-button-test-results** - Test results JSON
6. Extract the ZIP file(s) to view the screenshots

## Screenshot Retention

All screenshots and test results are retained for **30 days** after the workflow run completes.

## Expected Screenshot Examples

### Edit Button Interaction (Full Form Testing)

1. **Before**: Shows user detail page with "Edit" button visible
2. **Modal**: Shows edit form modal with original user fields (username, email, first name, last name, etc.)
3. **Filled**: Shows edit form with modified test data (first name and last name changed) ✨ **NEW**
4. **After**: Shows user detail page after submitting the form with updated data

### Reset Password Interaction (Full Form Testing)

1. **Before**: Shows user detail page with "Reset Password" button visible
2. **Modal**: Shows password reset modal with empty password fields
3. **Filled**: Shows password reset form with test password filled in both fields ✨ **NEW**
4. **After**: Shows user detail page after submitting the password reset

### Disable/Enable Interaction (Status Verification)

1. **Before**: Shows user detail page with current status (active/disabled) and corresponding button
2. **Modal**: Shows confirmation dialog (if implemented)
3. **After**: Shows user detail page with **verified** updated status and button text changed ✨ **ENHANCED**

### Delete Interaction

1. **Before**: Shows user detail page with "Delete" button visible
2. **Modal**: Shows delete confirmation dialog asking "Are you sure?"
3. **After**: Shows user detail page after canceling delete (preserved test data)

## Console Output

The workflow also displays button testing progress in the console:

```
=========================================
Testing User Detail Page Buttons
=========================================
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

🔘 Testing Edit/View button: "Edit"
   📸 Before screenshot: /tmp/screenshot_button_Edit_before.png
   🖱️  Clicking button...
   ℹ️  Modal/dialog detected after click
   📸 Modal screenshot: /tmp/screenshot_button_Edit_modal.png
   🖱️  Clicking Cancel/Close button to dismiss modal
   📸 After screenshot: /tmp/screenshot_button_Edit_after.png
   ✅ Test completed: Button clicked, modal/dialog appeared

🔘 Testing Password button: "Reset Password"
   📸 Before screenshot: /tmp/screenshot_button_Reset_Password_before.png
   🖱️  Clicking button...
   ℹ️  Modal/dialog detected after click
   📸 Modal screenshot: /tmp/screenshot_button_Reset_Password_modal.png
   📸 After screenshot: /tmp/screenshot_button_Reset_Password_after.png
   ✅ Test completed: Button clicked, modal/dialog appeared

========================================
Test Summary
========================================
Total buttons tested: 4
Successful tests: 4
Failed tests: 0
========================================
✅ Edit: Button clicked, modal/dialog appeared
✅ Reset Password: Button clicked, modal/dialog appeared
✅ Disable: Button clicked, page updated
✅ Delete: Button clicked, modal/dialog appeared

✅ User detail button testing completed
```

## Troubleshooting

### No Screenshots Generated

If no screenshots are generated:
1. Check that Playwright is installed: `npm install playwright`
2. Check that Chromium is installed: `npx playwright install chromium --with-deps`
3. Review the workflow logs for error messages

### Missing Button Screenshots

If some button screenshots are missing:
1. Check that the buttons exist on the page
2. Verify the test user has permissions to see the buttons
3. Check the test results JSON for specific button errors

### Modal Screenshots Not Captured

If modal screenshots are missing:
1. The button might not trigger a modal
2. The modal might appear too quickly/slowly (timing issue)
3. Check the modal selector in the script (looks for `[role="dialog"]`, `.uk-modal`, `.modal`)

## Related Documentation

- [User Detail Button Testing Script Documentation](.github/scripts/USER_DETAIL_BUTTON_TESTING.md)
- [User Detail Button Testing Implementation Summary](./USER_DETAIL_BUTTON_TESTING_SUMMARY.md)
- [Integration Test Workflow](.github/workflows/integration-test-modular.yml)
- [Modular Testing Framework](./MODULAR_TESTING_MIGRATION.md)
