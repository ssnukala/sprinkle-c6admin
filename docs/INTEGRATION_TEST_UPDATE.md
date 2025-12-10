# Integration Testing Update - Following CRUD6 Approach

## Problem Statement
The C6Admin integration tests were failing at the login step. The tests in CRUD6 were working correctly with successful authentication, so we needed to update C6Admin to follow the same testing approach.

## Analysis

### Key Differences Between CRUD6 and C6Admin
1. **Screenshot Script**:
   - CRUD6 uses `take-screenshots-with-tracking.js` - comprehensive script with network tracking
   - C6Admin was using `take-screenshots-modular.js` - simpler screenshot-only script

2. **Login Handling**:
   - CRUD6 has robust login with multiple selector fallbacks
   - C6Admin had basic login with single selector approach

3. **Network Tracking**:
   - CRUD6 tracks all network requests during page loads
   - CRUD6 detects redundant API calls
   - CRUD6 reports CRUD6 and schema API call statistics
   - C6Admin had no network tracking

4. **Error Logging**:
   - CRUD6 captures browser console errors
   - CRUD6 captures PHP error logs
   - C6Admin had limited error logging

## Solution Implemented

### 1. Adopted CRUD6's Screenshot Script
**File**: `.github/scripts/take-screenshots-with-tracking.js`

This enhanced script provides:
- **Robust Login Handling**: Multiple selector fallbacks for username, password, and submit button
  ```javascript
  const selectors = [
      '.uk-card input[data-test="username"]',
      'input[data-test="username"]',
      'input[name="username"]',
  ];
  ```
- **Network Request Tracking**: Monitors all HTTP requests during page loads
- **Redundant Call Detection**: Identifies duplicate API calls
- **CRUD6 API Filtering**: Focuses on CRUD6-specific API calls in reports
- **Error Notification Detection**: Fails if error alerts are found on pages
- **Browser Console Tracking**: Captures all console errors and warnings

### 2. Added PHP Error Log Capture
**File**: `.github/scripts/capture-php-error-logs.sh`

This script:
- Checks multiple log locations (system and UserFrosting-specific)
- Displays the last 50 lines of each log file
- Provides file size information
- Checks UserFrosting directories: `app/logs/` and `app/storage/logs/`

### 3. Updated Workflow
**File**: `.github/workflows/integration-test-modular.yml`

Changes made:
- Updated screenshot step to use `take-screenshots-with-tracking.js`
- Added network request summary artifact upload
- Added browser console errors artifact upload
- Added PHP error log capture and display step
- Added PHP error logs artifact upload
- Updated summary to reflect new features and artifacts

## Benefits

### 1. More Robust Authentication
The multiple selector fallback approach ensures login works even if:
- Page structure varies slightly
- UIkit classes change
- Data attributes are modified

### 2. Better Debugging
With the new artifacts, developers can:
- See exactly which API calls are being made
- Identify redundant calls that impact performance
- Review browser console errors for frontend issues
- Check PHP error logs for backend issues

### 3. Network Performance Insights
The network tracking provides:
- Per-page breakdown of API calls
- CRUD6 vs non-CRUD6 request separation
- Redundant call detection and reporting
- Chronological timeline of requests

### 4. Artifact Availability
All artifacts are retained for 30 days:
1. `integration-test-screenshots-c6admin` - Page screenshots
2. `network-requests-summary` - CRUD6 API call analysis
3. `browser-console-errors` - Frontend error logs
4. `php-error-logs` - Backend error logs
5. `user-detail-button-test-screenshots` - Button interaction tests
6. `user-detail-button-test-results` - Test results JSON

## Implementation Notes

### Schema Files
Unlike CRUD6 which copies schema files from examples, C6Admin already has schemas in the sprinkle:
```
vendor/ssnukala/sprinkle-c6admin/app/schema/crud6/
├── users.json
├── groups.json
├── roles.json
├── permissions.json
└── activities.json
```

No copying step needed for C6Admin.

### Locale Files
Similarly, C6Admin already has locale files in:
```
vendor/ssnukala/sprinkle-c6admin/app/locale/en_US/
vendor/ssnukala/sprinkle-c6admin/app/locale/fr_FR/
```

No merging step needed for C6Admin.

### Modular Configuration
Both CRUD6 and C6Admin use the modular testing framework:
- **Configuration**: `.github/config/integration-test-paths.json`
- **Screenshot definition**: Defined in the config JSON
- **Reusable scripts**: Same scripts work for both sprinkles

## Testing Approach Alignment

### CRUD6 Flow
1. Navigate to login page
2. Check for login form with multiple selector fallbacks
3. Fill credentials and submit
4. Take screenshots of authenticated pages
5. Track network requests during page loads
6. Test authenticated API endpoints
7. Capture error logs

### C6Admin Flow (Updated)
1. Navigate to login page
2. Check for login form with multiple selector fallbacks (✅ NEW)
3. Fill credentials and submit
4. Take screenshots of authenticated pages
5. Track network requests during page loads (✅ NEW)
6. Test user detail page buttons
7. Capture error logs (✅ NEW)

## Expected Outcomes

### Login Issues Should Be Resolved
The multiple selector fallback approach from CRUD6 should resolve the login failures that C6Admin was experiencing.

### Better Test Visibility
The enhanced logging and artifact collection will make it easier to:
- Diagnose test failures
- Identify performance issues
- Debug frontend and backend problems

### Consistent Testing Approach
Both CRUD6 and C6Admin now use the same robust testing methodology, making maintenance and debugging easier across both sprinkles.

## Next Steps

1. Monitor the next workflow run to verify login succeeds
2. Review network request summary for any redundant calls
3. Check browser console errors for any frontend issues
4. Review PHP error logs for any backend warnings

## References

- CRUD6 Integration Test: https://github.com/ssnukala/sprinkle-crud6/blob/main/.github/workflows/integration-test.yml
- CRUD6 Screenshot Script: https://github.com/ssnukala/sprinkle-crud6/blob/main/.github/scripts/take-screenshots-with-tracking.js
- C6Admin Integration Test: `.github/workflows/integration-test-modular.yml`
- Modular Testing README: `.github/MODULAR_TESTING_README.md`
