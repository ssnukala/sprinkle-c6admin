# Configurable Schema Paths Update

## Overview

This document describes the update to C6Admin's integration testing to support configurable schema paths, aligned with sprinkle-crud6 PR #363.

## Background

Previously, CRUD6's testing framework was hardcoded to use the `examples/schema` directory for schema discovery. This made it difficult for other sprinkles like C6Admin to leverage CRUD6's testing infrastructure with their own schema files.

**sprinkle-crud6 PR #363** introduced configurable schema directories, allowing sprinkles to specify their own schema paths for testing.

## Changes Made to C6Admin

### 1. Updated `phpunit.xml`

Added configuration for test schema directories:

```xml
<php>
    <!-- Configure test schema directories for CRUD6 integration tests -->
    <env name="TEST_SCHEMA_DIRS" value="app/schema/crud6"/>
</php>
```

**Purpose**: Tells the testing framework where to find C6Admin's JSON schemas.

### 2. Enhanced `C6AdminTestCase`

Added two new methods to support configurable schema paths:

#### `getTestSchemaDirs()`

Returns an array of directories to search for schemas:

```php
protected function getTestSchemaDirs(): array
{
    // Check environment variable first
    $envDirs = getenv('TEST_SCHEMA_DIRS');
    if ($envDirs !== false && $envDirs !== '') {
        $dirs = array_map('trim', explode(',', $envDirs));
        return $this->normalizeTestSchemaDirs($dirs);
    }

    // Default to C6Admin schema directory
    return $this->normalizeTestSchemaDirs([
        __DIR__ . '/../schema/crud6',
    ]);
}
```

**Priority Order**:
1. Environment variable `TEST_SCHEMA_DIRS` (comma-separated)
2. Method override in child classes
3. Default: `app/schema/crud6`

#### `normalizeTestSchemaDirs()`

Normalizes and validates schema directories:

```php
protected function normalizeTestSchemaDirs(array $dirs): array
{
    $normalized = [];
    $baseDir = dirname(__DIR__, 2); // Project root

    foreach ($dirs as $dir) {
        // Convert relative paths to absolute
        if (!str_starts_with($dir, '/')) {
            $dir = $baseDir . '/' . $dir;
        }

        // Only include existing directories
        if (is_dir($dir)) {
            $normalized[] = realpath($dir);
        }
    }

    return array_unique($normalized);
}
```

**Features**:
- Converts relative paths to absolute
- Filters out non-existent directories
- Removes duplicates
- Returns validated, normalized paths

### 3. Updated Documentation

Updated `app/tests/Integration/README.md` to document:
- Configuration methods (environment variable, override)
- Multiple schema directory support
- Default behavior
- Examples

## Configuration Options

### Option 1: Environment Variable (Recommended)

Configure in `phpunit.xml`:

```xml
<php>
    <env name="TEST_SCHEMA_DIRS" value="app/schema/crud6"/>
</php>
```

**For multiple directories**:
```xml
<env name="TEST_SCHEMA_DIRS" value="app/schema/crud6,vendor/my-sprinkle/schema"/>
```

### Option 2: Override in Test Class

Create a custom test class:

```php
class CustomSchemaTest extends C6AdminTestCase
{
    protected function getTestSchemaDirs(): array
    {
        return [
            __DIR__ . '/../../schema/crud6',
            __DIR__ . '/../../vendor/other-sprinkle/schema',
        ];
    }
}
```

### Option 3: Command Line

Set environment variable when running tests:

```bash
TEST_SCHEMA_DIRS="app/schema/crud6,vendor/my-sprinkle/schema" vendor/bin/phpunit
```

## Benefits

1. **Flexibility**: Can test schemas from multiple sprinkles simultaneously
2. **CI/CD Friendly**: Easy to configure in GitHub Actions workflows
3. **Future-Proof**: Aligns with CRUD6's new configurable approach
4. **Maintainability**: Clear configuration in one place (phpunit.xml)
5. **Backward Compatible**: Defaults to existing behavior if not configured

## Use Cases

### Single Sprinkle Testing (Default)

```xml
<env name="TEST_SCHEMA_DIRS" value="app/schema/crud6"/>
```

Tests only C6Admin schemas.

### Multi-Sprinkle Testing

```xml
<env name="TEST_SCHEMA_DIRS" value="app/schema/crud6,vendor/sprinkle-inventory/schema,vendor/sprinkle-crm/schema"/>
```

Tests schemas from multiple sprinkles in one test run.

### Custom Schema Location

```xml
<env name="TEST_SCHEMA_DIRS" value="tests/fixtures/schemas"/>
```

Tests schemas from a custom location (useful for test-specific schemas).

## Integration with CRUD6

C6Admin's `SchemaBasedApiTest` currently uses the `SchemaService` directly to load schemas, which automatically discovers schemas from the configured paths. The `getTestSchemaDirs()` methods provide the foundation for future enhancements where:

1. **Data Providers**: Could be used to automatically generate test cases for all schemas
2. **CI Workflows**: GitHub Actions can dynamically configure schema paths
3. **Cross-Sprinkle Testing**: Test multiple sprinkles' schemas in a single test suite

## Migration Path

### Before (Hardcoded)

```php
// SchemaService automatically used app/schema/crud6
$schema = $schemaService->getSchema('users');
```

### After (Configurable)

```xml
<!-- phpunit.xml -->
<env name="TEST_SCHEMA_DIRS" value="app/schema/crud6"/>
```

```php
// SchemaService uses configured directories
$schema = $schemaService->getSchema('users');
// Can also specify additional directories in test classes
```

**No changes required in existing tests** - they continue to work as before.

## Future Enhancements

The configurable schema paths enable future testing improvements:

1. **Automated Test Generation**: Use `schemaProvider()` to generate tests for all schemas
2. **Schema Validation Tests**: Validate schema structure across all configured directories
3. **Cross-Sprinkle Integration**: Test interactions between multiple sprinkles
4. **Dynamic Model Discovery**: Automatically discover and test all available models

## References

- **sprinkle-crud6 PR #363**: [Make Testing Framework Schema Folders Configurable](https://github.com/ssnukala/sprinkle-crud6/pull/363)
- **C6Admin Integration Tests**: `app/tests/Integration/README.md`
- **PHPUnit Configuration**: `phpunit.xml`
- **Test Base Class**: `app/tests/C6AdminTestCase.php`

## Testing

To verify the configuration:

```bash
# Run integration tests
vendor/bin/phpunit app/tests/Integration/

# Run with custom schema directory
TEST_SCHEMA_DIRS="app/schema/crud6" vendor/bin/phpunit app/tests/Integration/

# Verify schema discovery
php -r "var_dump(getenv('TEST_SCHEMA_DIRS'));"
```

## Conclusion

This update brings C6Admin's testing infrastructure in line with CRUD6's new configurable approach, providing flexibility for current and future testing needs while maintaining full backward compatibility.
