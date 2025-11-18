# Quick Reference: User Detail Button Testing

## TL;DR

Test all buttons on `/c6/admin/users/1` with screenshots:

```bash
# Option 1: Dedicated button testing (detailed)
node test-user-detail-buttons.js http://localhost:8080 admin admin123 1

# Option 2: As part of full screenshot suite (automatic)
node take-authenticated-screenshots.js http://localhost:8080 admin admin123
```

## What Gets Tested

### Buttons Automatically Discovered and Tested:
- ✅ Edit/View button
- ✅ Reset Password button
- ✅ Disable/Enable button
- ✅ Delete button (safely cancelled)
- ✅ Any other action buttons on the page

### Screenshots Generated:
- Before clicking each button
- Modal/dialog that appears (if any)
- After clicking each button
- Initial and final page states

## Output Locations

All files in `/tmp/`:
- `screenshot_user_1_initial.png` - Initial page state
- `screenshot_button_Edit_before.png` - Before edit click
- `screenshot_button_Edit_modal.png` - Edit modal
- `screenshot_button_Edit_after.png` - After edit
- `screenshot_button_Reset_Password_*.png` - Password button screenshots
- `screenshot_button_Disable_*.png` - Disable/Enable button screenshots
- `screenshot_button_Delete_*.png` - Delete button screenshots
- `user_detail_button_test_results.json` - JSON test results

## Quick Test Commands

```bash
# Validate scripts are correct
.github/scripts/validate-scripts.sh

# Test a specific user
node test-user-detail-buttons.js http://localhost:8080 admin admin123 2

# View test results
cat /tmp/user_detail_button_test_results.json | jq .

# Count screenshots
ls -1 /tmp/screenshot_button_*.png | wc -l
```

## Integration in CI/CD

Add to GitHub Actions workflow:

```yaml
- name: Test user buttons
  run: |
    node test-user-detail-buttons.js http://localhost:8080 admin admin123 1

- name: Upload screenshots
  uses: actions/upload-artifact@v4
  with:
    name: button-tests
    path: /tmp/screenshot_button_*.png
```

## Key Features

| Feature | test-user-detail-buttons.js | take-authenticated-screenshots.js |
|---------|----------------------------|-----------------------------------|
| Button discovery | ✅ Automatic | ✅ Automatic |
| Screenshot before/after | ✅ Yes | ✅ Yes |
| Modal detection | ✅ Yes | ✅ Yes |
| JSON report | ✅ Yes | ❌ No |
| Full page suite | ❌ No | ✅ Yes (12 pages) |
| Test duration | ~30 seconds | ~2 minutes |

## Troubleshooting

**No buttons found?**
- Increase wait timeout in script
- Check user has permissions
- Verify CRUD6 page loaded correctly

**Modal won't close?**
- Check screenshot to see modal content
- Modal may use different close button text
- Adjust modal closing logic if needed

**Delete didn't work?**
- This is intentional - delete is cancelled to preserve data
- Check modal screenshot to verify delete confirmation appeared

## Documentation

Full documentation:
- **User Guide**: `.github/scripts/USER_DETAIL_BUTTON_TESTING.md`
- **Implementation**: `docs/USER_DETAIL_BUTTON_TESTING_SUMMARY.md`

## Examples

### Example 1: Test default user
```bash
node .github/scripts/test-user-detail-buttons.js http://localhost:8080 admin admin123 1
```

### Example 2: Test with custom user
```bash
node .github/scripts/test-user-detail-buttons.js http://localhost:8080 myuser mypass 5
```

### Example 3: Validate scripts
```bash
chmod +x .github/scripts/validate-scripts.sh
.github/scripts/validate-scripts.sh
```

### Example 4: View screenshots in CI
Download artifacts from GitHub Actions:
1. Go to workflow run
2. Scroll to "Artifacts" section
3. Download "user-detail-button-tests"
4. Extract ZIP and view PNG files

## Success Criteria

✅ Script completes without errors
✅ Screenshots generated for each button
✅ Modals detected and captured
✅ Delete action cancelled successfully
✅ JSON report shows all tests passed

## Scripts Location

```
.github/
├── scripts/
│   ├── test-user-detail-buttons.js       ← Dedicated testing
│   ├── validate-scripts.sh               ← Validation
│   └── USER_DETAIL_BUTTON_TESTING.md     ← Full documentation
└── backup/
    └── take-authenticated-screenshots.js ← Full suite + buttons
```
