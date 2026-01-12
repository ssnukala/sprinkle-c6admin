# C6Admin Integration Tests

This directory contains integration tests for C6Admin that exercise the actual CRUD6 API endpoints.

## Overview

The integration tests in this directory are based on the CRUD6 testing methodology and follow the same pass/fail/warn pattern. They test the actual HTTP endpoints that the frontend Vue.js components would call.

## Schema Configuration

C6Admin supports configurable schema directories for integration tests, aligned with sprinkle-crud6 PR #363.

### Configuration Methods

#### 1. Environment Variable (phpunit.xml)

The default configuration is set in `phpunit.xml`:

```xml
<php>
    <env name="TEST_SCHEMA_DIRS" value="app/schema/crud6"/>
</php>
```

#### 2. Override in Test Class

You can override the schema directories in a specific test class:

```php
protected function getTestSchemaDirs(): array
{
    return [
        __DIR__ . '/../../schema/crud6',
        __DIR__ . '/../../vendor/other-sprinkle/schema',
    ];
}
```

#### 3. Multiple Schema Directories

For testing multiple sprinkles, specify comma-separated paths:

```xml
<env name="TEST_SCHEMA_DIRS" value="app/schema/crud6,vendor/my-sprinkle/schema"/>
```

### Default Behavior

If no configuration is provided:
- Falls back to `app/schema/crud6` directory
- Only existing directories are used
- Relative paths are resolved from project root

## Test Files

### SchemaBasedApiTest.php

The main integration test that dynamically tests all CRUD6 API endpoints based on JSON schema configuration. It tests:

- **Security**: Authentication and permission enforcement
- **CRUD Operations**: List, Create, Read, Update, Delete
- **Field Updates**: Toggle actions and field-specific updates
- **Custom Actions**: Schema-defined custom actions
- **Relationships**: Many-to-many relationships (attach/detach)

### Key Features

1. **Schema-Driven Testing**: Tests are generated based on schemas in `app/schema/crud6/`
2. **Pass/Fail/Warn Pattern**: Following CRUD6's testing approach
3. **API Call Tracking**: Detects redundant API calls
4. **C6Admin-Specific**: Accepts both 401 and 403 for unauthenticated requests
5. **Configurable Schema Paths**: Supports multiple schema directories (PR #363)

## Running Tests

```bash
# Run all integration tests
vendor/bin/phpunit app/tests/Integration/

# Run specific test class
vendor/bin/phpunit app/tests/Integration/SchemaBasedApiTest.php

# Run with verbose output
vendor/bin/phpunit --testdox app/tests/Integration/
```

## Test Models

The tests cover C6Admin schemas:

- **users**: Complete user management with relationships
- **groups**: Group management with CRUD operations
- **roles**: Role management (tested in SchemaBasedApiTest)
- **permissions**: Permission management (tested in SchemaBasedApiTest)
- **activities**: Activity tracking (tested in SchemaBasedApiTest)

## Authentication Patterns

### Unauthenticated Requests (401 or 403)
```php
$request = $this->createJsonRequest('GET', '/api/crud6/users');
$response = $this->handleRequestWithTracking($request);
$status = $response->getStatusCode();
$this->assertContains($status, [401, 403]);
```

### Authenticated Without Permission (403)
```php
$user = User::factory()->create();
$this->actAsUser($user); // No permissions
$request = $this->createJsonRequest('GET', '/api/crud6/users');
$response = $this->handleRequestWithTracking($request);
$this->assertResponseStatus(403, $response);
```

### Authenticated With Permission (200)
```php
$user = User::factory()->create();
$this->actAsUser($user, permissions: ['uri_users']);
$request = $this->createJsonRequest('GET', '/api/crud6/users');
$response = $this->handleRequestWithTracking($request);
$this->assertResponseStatus(200, $response);
```

## Documentation

For more detailed information about the testing methodology, see:
- [docs/TESTING_METHODOLOGY.md](../../docs/TESTING_METHODOLOGY.md)

## References

- CRUD6 SchemaBasedApiTest: [sprinkle-crud6/app/tests/Integration/SchemaBasedApiTest.php](https://github.com/ssnukala/sprinkle-crud6/blob/main/app/tests/Integration/SchemaBasedApiTest.php)
- C6Admin Testing Infrastructure: [app/src/Testing/](../../src/Testing/)
