# Button Testing Flow Diagram

This document provides a visual flow of how the button testing scripts work.

## test-user-detail-buttons.js Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                     START SCRIPT                                 │
│  node test-user-detail-buttons.js http://localhost:8080 admin admin123 1│
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: LOGIN                                                   │
│  - Navigate to /account/sign-in                                  │
│  - Fill username and password                                    │
│  - Click submit button                                           │
│  - Wait for authentication                                       │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: NAVIGATE TO USER DETAIL                                │
│  - Go to /c6/admin/users/1                                       │
│  - Wait for page load                                            │
│  - Take initial screenshot                                       │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: DISCOVER BUTTONS                                        │
│  - Find all <button> elements                                    │
│  - Filter for visible buttons with text                          │
│  - Collect button information:                                   │
│    • Text content                                                │
│    • ID and class attributes                                     │
│    • data-test attribute                                         │
│    • Enabled/disabled state                                      │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 4: TEST SPECIFIC BUTTONS                                   │
│                                                                   │
│  FOR EACH ACTION (Edit, Password, Disable, Delete):              │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │ Find button matching keywords                              │  │
│  │    ↓                                                        │  │
│  │ Take "before" screenshot                                   │  │
│  │    ↓                                                        │  │
│  │ Click the button                                           │  │
│  │    ↓                                                        │  │
│  │ Wait for response (2 seconds)                              │  │
│  │    ↓                                                        │  │
│  │ Check for modal/dialog                                     │  │
│  │    ├─── Yes ──→ Take modal screenshot                      │  │
│  │    │             Close modal (cancel for delete)           │  │
│  │    └─── No ───→ Check URL changed                          │  │
│  │                  Navigate back if needed                   │  │
│  │    ↓                                                        │  │
│  │ Take "after" screenshot                                    │  │
│  │    ↓                                                        │  │
│  │ Record test result                                         │  │
│  └───────────────────────────────────────────────────────────┘  │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 5: TEST REMAINING BUTTONS                                  │
│  - Find untested buttons                                         │
│  - Test up to 10 total buttons                                   │
│  - Same process as Step 4                                        │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 6: GENERATE RESULTS                                        │
│  - Take final screenshot                                         │
│  - Save JSON test results                                        │
│  - Print summary to console                                      │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                     END SCRIPT                                   │
│  Exit code: 0 (success) or 1 (failure)                           │
└─────────────────────────────────────────────────────────────────┘
```

## take-authenticated-screenshots.js Flow (Enhanced)

```
┌─────────────────────────────────────────────────────────────────┐
│                     START SCRIPT                                 │
│  node take-authenticated-screenshots.js http://localhost:8080 admin admin123│
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1-4: LOGIN & SCREENSHOTS                                   │
│  - Login (same as dedicated script)                              │
│  - Dashboard screenshot                                          │
│  - Users list screenshot                                         │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 5: USER DETAIL SCREENSHOT                                  │
│  - Navigate to /c6/admin/users/1                                 │
│  - Take initial screenshot                                       │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  *** NEW: STEP 5a - BUTTON TESTING ***                          │
│                                                                   │
│  Discover all buttons:                                           │
│    - Find all <button> elements                                  │
│    - Filter visible buttons with text                            │
│                                                                   │
│  Test 4 specific button types:                                   │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │ 1. Edit/View Button                                        │  │
│  │    • Find button with "edit" or "view" text                │  │
│  │    • Screenshot before → Click → Modal screenshot → After │  │
│  │    • Close modal after testing                             │  │
│  │                                                             │  │
│  │ 2. Password Reset Button                                   │  │
│  │    • Find button with "password" or "reset" text           │  │
│  │    • Screenshot before → Click → Modal screenshot → After │  │
│  │    • Close modal after testing                             │  │
│  │                                                             │  │
│  │ 3. Disable/Enable Button                                   │  │
│  │    • Find button with "disable" or "enable" text           │  │
│  │    • Screenshot before → Click → Check result → After     │  │
│  │                                                             │  │
│  │ 4. Delete Button                                           │  │
│  │    • Find button with "delete" or "remove" text            │  │
│  │    • Screenshot before → Click → Modal screenshot         │  │
│  │    • ⚠️  CANCEL delete to preserve test data               │  │
│  │    • Screenshot after cancellation                         │  │
│  └───────────────────────────────────────────────────────────┘  │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 6-14: REMAINING SCREENSHOTS                                │
│  - Groups list & detail                                          │
│  - Roles list & detail                                           │
│  - Permissions list & detail                                     │
│  - Activities page                                               │
│  - Config info & cache pages                                     │
└────────────────────────┬────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────────┐
│                     END SCRIPT                                   │
│  Total screenshots: 12+ pages + button tests                     │
└─────────────────────────────────────────────────────────────────┘
```

## Button Discovery Algorithm

```
┌─────────────────────────────────────────┐
│  Find all <button> elements on page     │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  FOR EACH button element:                │
│  ┌─────────────────────────────────────┐│
│  │ Extract properties:                 ││
│  │ • text = textContent.trim()         ││
│  │ • id = element.id                   ││
│  │ • class = element.className         ││
│  │ • data-test = getAttribute()        ││
│  │ • disabled = element.disabled       ││
│  │ • visible = check offsetParent      ││
│  └─────────────────────────────────────┘│
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  Filter buttons:                         │
│  - Must be visible (not hidden)          │
│  - Must have text content                │
│  - Store with index for reference        │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  Identify action buttons by keywords:    │
│                                          │
│  Edit/View:    "edit", "view"            │
│  Password:     "password", "reset"       │
│  Disable:      "disable", "enable"       │
│  Delete:       "delete", "remove"        │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│  Return list of buttons to test          │
└─────────────────────────────────────────┘
```

## Modal Handling

```
                    Click Button
                         │
                         ▼
            ┌────────────────────────┐
            │ Wait 2 seconds         │
            └────────┬───────────────┘
                     │
                     ▼
    ┌────────────────────────────────┐
    │ Search for modal selectors:    │
    │ • [role="dialog"]              │
    │ • .uk-modal                    │
    │ • .modal                       │
    └────────┬───────────────────────┘
             │
             ▼
    ┌────────────────┐
    │  Modal found?  │
    └────┬───────┬───┘
         │       │
    YES  │       │  NO
         │       │
         ▼       ▼
    ┌────────┐  ┌──────────────┐
    │ Modal  │  │ Check URL    │
    │ Path   │  │ Changed?     │
    └────┬───┘  └───┬──────────┘
         │          │
         ▼          ▼
    Take Modal    Navigate
    Screenshot    Back if
         │        needed
         ▼
    Find Cancel
    Button
         │
         ▼
    Click Cancel
         │
         ▼
    Take After
    Screenshot
```

## Output Files Structure

```
/tmp/
├── screenshot_user_1_initial.png                  ← Initial page state
├── screenshot_user_1_final.png                    ← Final page state
│
├── screenshot_button_Edit_before.png              ← Edit button testing
├── screenshot_button_Edit_modal.png               │
├── screenshot_button_Edit_after.png               │
│
├── screenshot_button_Reset_Password_before.png    ← Password button
├── screenshot_button_Reset_Password_modal.png     │
├── screenshot_button_Reset_Password_after.png     │
│
├── screenshot_button_Disable_before.png           ← Disable button
├── screenshot_button_Disable_after.png            │
│
├── screenshot_button_Delete_before.png            ← Delete button
├── screenshot_button_Delete_modal.png             │
└── screenshot_button_Delete_after.png             │

└── user_detail_button_test_results.json           ← Test results
```

## Test Results JSON Structure

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

## Error Handling Flow

```
                    Any Error Occurs
                         │
                         ▼
            ┌────────────────────────┐
            │ Log error to console   │
            └────────┬───────────────┘
                     │
                     ▼
            ┌────────────────────────┐
            │ Take error screenshot  │
            └────────┬───────────────┘
                     │
                     ▼
            ┌────────────────────────┐
            │ Save to:               │
            │ /tmp/screenshot_       │
            │   button_test_error.png│
            └────────┬───────────────┘
                     │
                     ▼
            ┌────────────────────────┐
            │ Log page URL & title   │
            └────────┬───────────────┘
                     │
                     ▼
            ┌────────────────────────┐
            │ Exit with code 1       │
            └────────────────────────┘
```

## Success Criteria

```
✅ Script completes without errors
✅ All expected buttons discovered
✅ Screenshots generated for each button:
   • Before screenshot exists
   • Modal screenshot (if modal appeared)
   • After screenshot exists
✅ Modals detected and closed properly
✅ Delete action cancelled successfully
✅ Navigation restored after URL changes
✅ JSON report generated with all tests
✅ All tests marked as successful
```

## Integration Points

### CI/CD Workflow
```yaml
jobs:
  test:
    steps:
      - name: Test buttons
        run: node test-user-detail-buttons.js $URL $USER $PASS 1
        
      - name: Upload screenshots
        uses: actions/upload-artifact@v4
        with:
          path: /tmp/screenshot_button_*.png
```

### Local Testing
```bash
# Terminal 1: Start servers
php bakery serve &
php bakery assets:vite &

# Terminal 2: Run tests
node test-user-detail-buttons.js http://localhost:8080 admin admin123 1

# View results
ls -lh /tmp/screenshot_button_*.png
cat /tmp/user_detail_button_test_results.json | jq .
```
