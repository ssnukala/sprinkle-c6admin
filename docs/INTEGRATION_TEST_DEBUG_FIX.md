# Integration Test Debug Enhancement - Fix Summary

**Date**: 2025-11-18  
**Issue**: Integration test fails waiting for the login page, suggesting Vue error  
**PR/Branch**: copilot/update-dockerfile-for-vue

## Problem Statement

The integration test was failing with the following issues:

1. **Vue Error**: Tests were failing while waiting for the login page
2. **Missing Docker Configuration**: No `docker/vue/Dockerfile` existed with proper Vite configuration
3. **AWK Script Issue**: The router configuration awk script wasn't working correctly
4. **Insufficient Debug Logs**: Not enough visibility into what was causing the failures

## Solution Overview

### 1. Docker Configuration Created

#### New Files
- **docker/vue/Dockerfile**: Vite development server with `--host --force` flags
- **docker-compose.yml**: Local development environment setup
- **.dockerignore**: Optimized Docker build context
- **docker/README.md**: Comprehensive documentation

#### Dockerfile Details
```dockerfile
CMD ["npx", "vite", "dev", "--host", "--force"]
```

**Rationale**:
- `--host`: Required for Docker networking (allows external connections)
- `--force`: Forces dependency optimization even if cached

### 2. Enhanced Debug Logging in Screenshot Script

#### Changes to `.github/scripts/take-screenshots-modular.js`

**Before**: Minimal logging with basic error messages

**After**: Comprehensive debug output including:

1. **Browser Event Listeners**:
   ```javascript
   page.on('console', msg => {
       const type = msg.type();
       if (type === 'error' || type === 'warning') {
           console.log(`   [Browser ${type.toUpperCase()}]:`, msg.text());
       }
   });
   
   page.on('pageerror', error => {
       console.error(`   [Browser Error]:`, error.message);
   });
   ```

2. **Login Flow Logging**:
   - URL before navigation
   - Current URL after each step
   - Username field detection status
   - Password field status
   - Navigation events
   - Final URL after login

3. **Screenshot Loop Debugging**:
   - Navigation target URL
   - Current URL after navigation
   - Page stabilization status
   - Debug screenshots on failures
   - Page title and content on errors
   - Full stack traces

4. **Error Context**:
   ```javascript
   try {
       const pageContent = await page.content();
       console.error(`   Page title: ${await page.title()}`);
       console.error(`   Page URL: ${page.url()}`);
   } catch (e) {
       console.error(`   Could not retrieve page details: ${e.message}`);
   }
   ```

### 3. Fixed AWK Script in Integration Test Workflow

#### Changes to `.github/workflows/integration-test-modular.yml`

**Before**: Single-line awk command with potential issues
```bash
awk '/title: .ADMIN_PANEL./ {found=1} found && /^        },/ && !done {print; print "..."; done=1; next} {print}' ...
```

**After**: Multi-line awk script with validation and fallback

```bash
awk '
/title: .ADMIN_PANEL./ { found=1 }
found && /^        },/ && !done {
  print
  print "        // C6Admin routes with their own layout"
  print "        ...createC6AdminRoutes({ layoutComponent: LayoutDashboard }),"
  done=1
  next
}
{ print }
' app/assets/router/index.ts > app/assets/router/index.ts.tmp

# Verify the tmp file was created successfully
if [ ! -f app/assets/router/index.ts.tmp ]; then
  echo "❌ Error: Failed to create router/index.ts.tmp"
  exit 1
fi

# Check if the C6Admin routes were added
if grep -q "createC6AdminRoutes" app/assets/router/index.ts.tmp; then
  echo "✅ C6Admin routes added successfully"
  mv app/assets/router/index.ts.tmp app/assets/router/index.ts
else
  echo "⚠️  Warning: C6Admin routes may not have been added correctly"
  echo "Attempting alternative method..."
  # Alternative: Add at the end of the children array, before the closing bracket
  sed -i '/^    \]/i\        // C6Admin routes...' app/assets/router/index.ts
  rm -f app/assets/router/index.ts.tmp
fi

# Show the result
cat app/assets/router/index.ts
```

**Improvements**:
- Multi-line format for better readability
- Temporary file validation
- Grep check to verify routes were added
- Fallback sed method if awk fails
- Full output via `cat` to verify configuration

### 4. Enhanced Vite Server Debugging in Workflow

**Before**: Basic Vite startup with minimal logging

**After**: Comprehensive Vite server debugging

```bash
# Start Vite server with log capture
php bakery assets:vite > /tmp/vite.log 2>&1 &
VITE_PID=$!
echo $VITE_PID > /tmp/vite.pid
echo "Vite server PID: $VITE_PID"

# Wait and check process health
sleep 10
if ps -p $VITE_PID > /dev/null; then
  echo "✅ Vite server process is running (PID: $VITE_PID)"
else
  echo "❌ Vite server process died!"
  echo "Vite log output:"
  cat /tmp/vite.log || echo "No log file found"
  exit 1
fi

# Show first 20 lines of output
echo "Vite server output (first 20 lines):"
head -20 /tmp/vite.log || echo "No log output yet"

# Test Vite server directly on port 5173
echo "Testing Vite server directly..."
VITE_RESPONSE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:5173 2>&1 || echo "failed")
if [ "$VITE_RESPONSE" = "200" ] || [ "$VITE_RESPONSE" = "302" ]; then
  echo "✅ Vite server responding on port 5173"
else
  echo "⚠️ Vite server not responding on port 5173 (response: $VITE_RESPONSE)"
fi
```

**Features**:
- Log file capture to /tmp/vite.log
- Process ID tracking
- Health check (process still running)
- First 20 lines of output shown
- Direct port 5173 testing
- Error handling with log display

### 5. Enhanced Screenshot Step Debugging

```bash
# Run with verbose output and capture to log
node take-screenshots-modular.js integration-test-paths.json 2>&1 | tee /tmp/screenshot.log

# Check exit code
SCREENSHOT_EXIT_CODE=${PIPESTATUS[0]}
echo "Screenshot script exit code: $SCREENSHOT_EXIT_CODE"

# If failed, show server logs
if [ $SCREENSHOT_EXIT_CODE -ne 0 ]; then
  echo "⚠️ Screenshot script failed - showing server logs"
  echo "Vite server log:"
  tail -50 /tmp/vite.log || echo "No Vite log available"
  echo "Running processes:"
  ps aux | grep -E "(php|vite|node)" | grep -v grep
fi
```

## Files Modified

### Created Files
1. `docker/vue/Dockerfile` - Vite dev server configuration
2. `docker-compose.yml` - Local development environment
3. `.dockerignore` - Docker build optimization
4. `docker/README.md` - Docker documentation

### Modified Files
1. `.github/scripts/take-screenshots-modular.js` - Enhanced debug logging
2. `.github/workflows/integration-test-modular.yml` - Fixed awk script and added debug output

## Testing Impact

### What You'll See in CI Runs

#### 1. Router Configuration Step
```
========================================
Configured router/index.ts with C6Admin routes
========================================
✅ C6Admin routes added successfully
[Full router/index.ts content displayed]
```

Or if fallback is used:
```
⚠️  Warning: C6Admin routes may not have been added correctly
Attempting alternative method...
[Full router/index.ts content displayed]
```

#### 2. Vite Server Start
```
========================================
Starting Vite development server
========================================
Vite server PID: 12345
Waiting for Vite server to initialize...
✅ Vite server process is running (PID: 12345)

Vite server output (first 20 lines):
[Vite output showing startup, port, etc.]

✅ Application is accessible
✅ Vite server responding on port 5173
```

#### 3. Screenshot Process
```
📍 Navigating to login page...
   URL: http://localhost:8080/account/sign-in
✅ Login page loaded
   Current URL: http://localhost:8080/account/sign-in
🔐 Logging in...
   Waiting for username input field...
   ✅ Username field found
   Filling username: admin
   Filling password: ********
   Clicking login button...
✅ Logged in successfully
   Current URL after login: http://localhost:8080/dashboard

📸 Taking screenshot: c6_dashboard
   Path: /c6/admin/dashboard
   Description: C6Admin Dashboard page
   Navigating to: http://localhost:8080/c6/admin/dashboard
   Waiting for page to stabilize...
   Current URL: http://localhost:8080/c6/admin/dashboard
   ✅ Page loaded successfully
   ✅ Screenshot saved: /tmp/screenshot_c6admin_dashboard.png
```

#### 4. On Failure
```
❌ Screenshot script failed - showing server logs
Vite server log:
[Last 50 lines of Vite log]

Running processes:
[List of php, vite, node processes]
```

## Benefits

### 1. Debugging Capabilities
- **Browser Errors**: All console errors/warnings are logged
- **Navigation Issues**: See exact URLs at each step
- **Authentication Problems**: Debug screenshots saved on login failures
- **Server Issues**: Vite logs captured and displayed on failures

### 2. Reliability
- **AWK Script**: Validation ensures routes are added, fallback method if not
- **Vite Server**: Health checks ensure server is running before tests
- **Error Context**: Full stack traces and page details on errors

### 3. Local Development
- **Docker**: Complete Docker setup for local testing
- **Documentation**: Comprehensive README for Docker usage
- **Compose**: Easy local environment with `docker-compose up`

## Validation Performed

1. ✅ JavaScript syntax validation (`node --check`)
2. ✅ YAML syntax validation (`yamllint`)
3. ✅ Dockerfile validation (command structure check)
4. ✅ All files committed and pushed successfully

## Next Steps

1. **Run Integration Test**: Trigger the workflow to see enhanced debug output
2. **Monitor Logs**: Check for:
   - Router configuration success message
   - Vite server health check results
   - Screenshot process detailed logging
   - Any browser errors captured
3. **Review Artifacts**: If tests fail, check:
   - Debug screenshots in /tmp/screenshot_*_debug.png
   - Error screenshot in /tmp/screenshot_error.png
   - Vite logs in workflow output

## References

- **Workflow File**: `.github/workflows/integration-test-modular.yml`
- **Screenshot Script**: `.github/scripts/take-screenshots-modular.js`
- **Docker Config**: `docker/vue/Dockerfile`, `docker-compose.yml`
- **Documentation**: `docker/README.md`

## Notes

- Docker configuration is for local development; CI uses `php bakery assets:vite`
- Debug logs will help identify if issue is:
  - Router configuration (awk script)
  - Vite server startup
  - Vue.js errors in browser
  - Authentication flow
  - Network/timing issues

The enhanced debugging should make it much easier to identify the root cause of the integration test failures.
