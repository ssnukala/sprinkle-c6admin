# C6Admin Testing Methodology

## Overview

C6Admin's testing methodology is replicated from the CRUD6 sprinkle, following the same pass/fail/warn pattern and comprehensive API integration testing approach. This ensures consistency across UserFrosting 6 sprinkles while accommodating C6Admin-specific requirements.

## Test Structure

### Integration Tests

Located in `app/tests/Integration/`, these tests exercise the actual API endpoints to ensure they work correctly:

- **SchemaBasedApiTest.php**: Main integration test that dynamically tests all CRUD6 API endpoints based on JSON schema configuration

### Testing Infrastructure

Located in `app/src/Testing/`:

- **ApiCallTracker.php**: Tracks API calls during tests to detect redundant/duplicate calls
- **TracksApiCalls.php**: Trait providing API call tracking functionality to integration tests

## Key Differences from CRUD6

While the testing methodology is replicated from CRUD6, there are important C6Admin-specific differences:

### 1. Namespace
- **CRUD6**: `UserFrosting\Sprinkle\CRUD6\Tests`
- **C6Admin**: `UserFrosting\Sprinkle\C6Admin\Tests`

### 2. Base Test Class
- **CRUD6**: Extends `AdminTestCase` (from CRUD6)
- **C6Admin**: Extends `C6AdminTestCase` (from C6Admin)

### 3. Authentication Response Codes
**CRITICAL DIFFERENCE**: C6Admin accepts both 401 and 403 for unauthenticated requests:

- **401 Unauthorized**: No authentication provided
- **403 Forbidden**: Authenticated but lacking required permissions

This is different from CRUD6 which may only expect 401 for unauthenticated requests.

**Implementation**:
```php
// CRUD6 pattern (expects only 401):
$this->assertResponseStatus(401, $response);

// C6Admin pattern (accepts 401 OR 403):
$status = $response->getStatusCode();
$this->assertContains($status, [401, 403], 
    "Unauthenticated request should be rejected with 401 or 403");
```

### 4. Schema Location
- **CRUD6**: Uses schemas from `examples/schema/` (copied to `app/schema/crud6/`)
- **C6Admin**: Uses schemas directly from `app/schema/crud6/` (no copying needed)

## Test Coverage

The integration tests cover:

1. **Security Middleware**
   - AuthGuard enforcement (401/403 for unauthenticated)
   - Permission checks (403 for unauthorized)
   - CSRF protection (handled by testing framework)

2. **CRUD Endpoints**
   - Schema: `GET /api/crud6/{model}/schema`
   - List: `GET /api/crud6/{model}`
   - Create: `POST /api/crud6/{model}`
   - Read: `GET /api/crud6/{model}/{id}`
   - Update: `PUT /api/crud6/{model}/{id}`
   - Delete: `DELETE /api/crud6/{model}/{id}`

3. **Field Updates**
   - Field update: `PUT /api/crud6/{model}/{id}/{field}`
   - Toggle actions from schema

4. **Custom Actions**
   - Custom actions: `POST /api/crud6/{model}/{id}/a/{actionKey}`

5. **Relationships**
   - Attach: `POST /api/crud6/{model}/{id}/{relation}`
   - Detach: `DELETE /api/crud6/{model}/{id}/{relation}`

## Pass/Fail/Warn Pattern

Following CRUD6's pattern:

### Pass (✓)
Tests that should succeed with proper authentication and permissions:
- Authenticated user with correct permissions
- Valid request payload
- Expected response status (200)
- Correct database state

### Fail (Expected)
Tests that should fail and return error codes:
- Unauthenticated requests (401 or 403)
- Authenticated but missing permissions (403)
- Invalid payload (400/422)

### Warn (Informational)
Tests that exercise endpoints but may not be fully implemented:
- Custom actions that may return 404 or 500
- Relationship endpoints that may not be configured
- Status codes: 200, 403, 404, 500 all considered valid for informational purposes

## Running Tests

### Run All Tests
```bash
vendor/bin/phpunit
```

### Run Integration Tests Only
```bash
vendor/bin/phpunit app/tests/Integration/
```

### Run Specific Test
```bash
vendor/bin/phpunit app/tests/Integration/SchemaBasedApiTest.php
```

### Run with Verbose Output
```bash
vendor/bin/phpunit --testdox app/tests/Integration/
```

## Test Models

The integration tests use C6Admin schemas from `app/schema/crud6/`:

- **users.json**: User model with relationships, actions, and field toggles
- **roles.json**: Role model with many-to-many relationships
- **groups.json**: Group model with simple CRUD operations
- **permissions.json**: Permission model with nested relationships
- **activities.json**: Activity model

## API Call Tracking

The testing infrastructure includes API call tracking to detect redundant calls:

### Using API Call Tracker

```php
class MyIntegrationTest extends C6AdminTestCase
{
    use TracksApiCalls;
    
    public function setUp(): void
    {
        parent::setUp();
        $this->startApiTracking();
    }
    
    public function testSomething(): void
    {
        // Make API calls
        $request = $this->createJsonRequest('GET', '/api/crud6/users');
        $response = $this->handleRequestWithTracking($request);
        
        // Verify no redundant calls
        $this->assertNoRedundantApiCalls();
    }
    
    public function tearDown(): void
    {
        $this->tearDownApiTracking();
        parent::tearDown();
    }
}
```

### Available Assertions

- `assertNoRedundantApiCalls()`: Ensures no endpoint is called multiple times
- `assertNoRedundantSchemaApiCalls()`: Ensures schema endpoints aren't called redundantly
- `assertApiCallCount($uri, $count)`: Verifies specific number of calls to an endpoint

## Authentication in Tests

Following UserFrosting 6 patterns:

```php
// Create authenticated user
/** @var User */
$user = User::factory()->create();

// Act as user without permissions
$this->actAsUser($user);

// Act as user with specific permissions
$this->actAsUser($user, permissions: ['uri_users', 'create_user']);
```

## Best Practices

1. **Always test authentication first**: Verify unauthenticated requests are rejected
2. **Test permissions separately**: Verify authenticated but unauthorized requests fail
3. **Test happy path last**: Verify successful requests with proper auth and permissions
4. **Use schema-driven tests**: Let the schema define what to test
5. **Track API calls**: Use the tracking infrastructure to detect redundant calls
6. **Accept flexible status codes**: For informational tests, accept ranges like [200, 403, 404, 500]

## References

- CRUD6 SchemaBasedApiTest: [sprinkle-crud6/app/tests/Integration/SchemaBasedApiTest.php](https://github.com/ssnukala/sprinkle-crud6/blob/main/app/tests/Integration/SchemaBasedApiTest.php)
- UserFrosting Testing: [framework/testing](https://github.com/userfrosting/framework/tree/6.0/src/Testing)
- WithTestUser trait: [sprinkle-account/Testing/WithTestUser.php](https://github.com/userfrosting/sprinkle-account/blob/6.0/app/src/Testing/WithTestUser.php)
