# PHP Error Log Artifact Upload - Implementation Verification

## New Requirement Acknowledgment
**Requirement**: We need to copy the UserFrosting logs to the artifacts as well as CRUD6 is doing.

**Status**: ✅ **ALREADY IMPLEMENTED** - The C6Admin workflow already includes PHP error log capture and upload that exactly matches CRUD6's implementation.

## Side-by-Side Comparison

### CRUD6 Implementation

**Capture Step** (from CRUD6's integration-test.yml):
```yaml
- name: Capture and display PHP error logs
  if: always()
  run: |
    cd userfrosting

    # Copy PHP error log capture script from sprinkle
    cp ../sprinkle-crud6/.github/scripts/capture-php-error-logs.sh .
    chmod +x capture-php-error-logs.sh

    # Run the script to capture and display error logs
    ./capture-php-error-logs.sh || echo "Error log capture script failed, but continuing..."
```

**Upload Step** (from CRUD6's integration-test.yml):
```yaml
- name: Upload PHP error logs as artifacts
  if: always()
  uses: actions/upload-artifact@v4
  with:
    name: php-error-logs
    path: |
      userfrosting/app/logs/*.log
      userfrosting/app/storage/logs/*.log
    if-no-files-found: ignore
    retention-days: 30
```

### C6Admin Implementation

**Capture Step** (from C6Admin's integration-test-modular.yml):
```yaml
- name: Capture and display PHP error logs
  if: always()
  run: |
    cd userfrosting

    # Copy PHP error log capture script from sprinkle
    cp ../sprinkle-c6admin/.github/scripts/capture-php-error-logs.sh .
    chmod +x capture-php-error-logs.sh

    # Run the script to capture and display error logs
    ./capture-php-error-logs.sh || echo "Error log capture script failed, but continuing..."
```

**Upload Step** (from C6Admin's integration-test-modular.yml):
```yaml
- name: Upload PHP error logs as artifacts
  if: always()
  uses: actions/upload-artifact@v4
  with:
    name: php-error-logs
    path: |
      userfrosting/app/logs/*.log
      userfrosting/app/storage/logs/*.log
    if-no-files-found: ignore
    retention-days: 30
```

## Verification

### ✅ Identical Implementation
The implementations are **100% identical** except for the sprinkle name:
- CRUD6 uses: `cp ../sprinkle-crud6/.github/scripts/capture-php-error-logs.sh .`
- C6Admin uses: `cp ../sprinkle-c6admin/.github/scripts/capture-php-error-logs.sh .`

### ✅ Script is Present
The `capture-php-error-logs.sh` script was copied from CRUD6 to C6Admin:
```bash
$ ls -la .github/scripts/capture-php-error-logs.sh
-rwxrwxr-x 1 runner runner  3816 Dec 10 15:37 capture-php-error-logs.sh
```

### ✅ Paths Are Correct
Both implementations upload logs from the same locations:
- `userfrosting/app/logs/*.log` - Main UserFrosting log directory
- `userfrosting/app/storage/logs/*.log` - Storage log directory

The paths include "userfrosting/" because the artifact upload step runs from the repository root, while the capture script runs from inside the userfrosting directory.

## What Gets Captured

The `capture-php-error-logs.sh` script captures:

1. **Standard PHP Error Log Locations**:
   - `/tmp/php_errors.log`
   - `/var/log/php_errors.log`
   - `/var/log/php/error.log`

2. **UserFrosting Log Files**:
   - `app/logs/userfrosting.log`
   - `app/logs/errors.log`
   - `app/storage/logs/userfrosting.log`
   - `app/storage/logs/errors.log`
   - All other `*.log` files in these directories

3. **Display Output**:
   - Last 50 lines of each log file
   - File size information
   - Status indicators for found/empty files

## Artifact Details

When the workflow runs, it creates a `php-error-logs` artifact containing:
- All `.log` files from `userfrosting/app/logs/`
- All `.log` files from `userfrosting/app/storage/logs/`
- Retained for 30 days
- Available even if tests fail (`if: always()`)

## Conclusion

✅ **The requirement is already fulfilled.** The C6Admin integration test workflow includes:
1. PHP error log capture script (identical to CRUD6)
2. PHP error log display during workflow run (last 50 lines shown)
3. PHP error log artifact upload (all log files from both directories)
4. Identical configuration to CRUD6

No additional changes are needed.
