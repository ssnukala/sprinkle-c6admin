# Quick Start: Adding Integration Tests

This guide shows how to add new integration tests for C6Admin models following the CRUD6 methodology.

## Basic Integration Test Template

```php
<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\C6Admin\Tests\Integration;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;
use UserFrosting\Sprinkle\C6Admin\Testing\TracksApiCalls;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\CRUD6\ServicesProvider\SchemaService;

/**
 * Integration test for MyModel API endpoints
 */
class MyModelApiTest extends C6AdminTestCase
{
    use RefreshDatabase;
    use WithTestUser;
    use MockeryPHPUnitIntegration;
    use TracksApiCalls;

    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
        $this->startApiTracking();
    }

    public function tearDown(): void
    {
        $this->tearDownApiTracking();
        parent::tearDown();
    }

    public function testMyModelCompleteApiIntegration(): void
    {
        echo "\n[TEST] Testing mymodel API endpoints\n";

        // Get schema
        /** @var SchemaService */
        $schemaService = $this->ci->get(SchemaService::class);
        $schema = $schemaService->getSchema('mymodel');
        
        $this->assertNotNull($schema, 'Schema should exist');

        // Test endpoints...
    }
}
```

## Testing Unauthenticated Requests

**IMPORTANT**: C6Admin accepts both 401 and 403 for unauthenticated requests.

```php
public function testUnauthenticatedRequest(): void
{
    $request = $this->createJsonRequest('GET', '/api/crud6/mymodel');
    $response = $this->handleRequestWithTracking($request);
    
    // Accept both 401 and 403
    $status = $response->getStatusCode();
    $this->assertContains($status, [401, 403], 
        "Unauthenticated request should return 401 or 403, got {$status}");
}
```

## Testing Authenticated Without Permission

```php
public function testAuthenticatedWithoutPermission(): void
{
    // Create user without permissions
    /** @var User */
    $user = User::factory()->create();
    $this->actAsUser($user);
    
    $request = $this->createJsonRequest('GET', '/api/crud6/mymodel');
    $response = $this->handleRequestWithTracking($request);
    
    // Should return 403 Forbidden
    $this->assertResponseStatus(403, $response);
}
```

## Testing Authenticated With Permission

```php
public function testAuthenticatedWithPermission(): void
{
    // Create user with permission
    /** @var User */
    $user = User::factory()->create();
    $this->actAsUser($user, permissions: ['uri_mymodel']);
    
    $request = $this->createJsonRequest('GET', '/api/crud6/mymodel');
    $response = $this->handleRequestWithTracking($request);
    
    // Should return 200 OK
    $this->assertResponseStatus(200, $response);
}
```

## Testing CRUD Operations

### List (GET)
```php
public function testListEndpoint(): void
{
    $user = User::factory()->create();
    $this->actAsUser($user, permissions: ['uri_mymodel']);
    
    $request = $this->createJsonRequest('GET', '/api/crud6/mymodel');
    $response = $this->handleRequestWithTracking($request);
    
    $this->assertResponseStatus(200, $response);
    $this->assertJson((string) $response->getBody());
}
```

### Create (POST)
```php
public function testCreateEndpoint(): void
{
    $user = User::factory()->create();
    $this->actAsUser($user, permissions: ['create_mymodel']);
    
    $data = [
        'field1' => 'value1',
        'field2' => 'value2',
    ];
    
    $request = $this->createJsonRequest('POST', '/api/crud6/mymodel', $data);
    $response = $this->handleRequestWithTracking($request);
    
    $this->assertResponseStatus(200, $response);
    
    $body = json_decode((string) $response->getBody(), true);
    $this->assertArrayHasKey('data', $body);
    $this->assertArrayHasKey('id', $body['data']);
}
```

### Read (GET)
```php
public function testReadEndpoint(): void
{
    $user = User::factory()->create();
    $this->actAsUser($user, permissions: ['uri_mymodel']);
    
    // Assume $model is the created model instance
    $request = $this->createJsonRequest('GET', "/api/crud6/mymodel/{$model->id}");
    $response = $this->handleRequestWithTracking($request);
    
    $this->assertResponseStatus(200, $response);
    
    $body = json_decode((string) $response->getBody(), true);
    $this->assertEquals($model->id, $body['id']);
}
```

### Update (PUT)
```php
public function testUpdateEndpoint(): void
{
    $user = User::factory()->create();
    $this->actAsUser($user, permissions: ['update_mymodel']);
    
    $updateData = ['field1' => 'new value'];
    
    $request = $this->createJsonRequest('PUT', "/api/crud6/mymodel/{$model->id}", $updateData);
    $response = $this->handleRequestWithTracking($request);
    
    $this->assertResponseStatus(200, $response);
    
    // Verify database
    $model->refresh();
    $this->assertEquals('new value', $model->field1);
}
```

### Delete (DELETE)
```php
public function testDeleteEndpoint(): void
{
    $user = User::factory()->create();
    $this->actAsUser($user, permissions: ['delete_mymodel']);
    
    $modelId = $model->id;
    
    $request = $this->createJsonRequest('DELETE', "/api/crud6/mymodel/{$modelId}");
    $response = $this->handleRequestWithTracking($request);
    
    $this->assertResponseStatus(200, $response);
    
    // Verify deleted
    $this->assertNull(MyModel::find($modelId));
}
```

## Testing Field Updates

```php
public function testFieldUpdate(): void
{
    $user = User::factory()->create();
    $this->actAsUser($user, permissions: ['update_mymodel_field']);
    
    $field = 'status';
    $newValue = 'active';
    
    $request = $this->createJsonRequest('PUT', "/api/crud6/mymodel/{$model->id}/{$field}", [
        $field => $newValue,
    ]);
    $response = $this->handleRequestWithTracking($request);
    
    $this->assertResponseStatus(200, $response);
    
    $model->refresh();
    $this->assertEquals($newValue, $model->$field);
}
```

## Testing Relationships

```php
public function testRelationshipAttach(): void
{
    $user = User::factory()->create();
    $this->actAsUser($user, permissions: ['update_mymodel']);
    
    $relatedModel = RelatedModel::factory()->create();
    
    $request = $this->createJsonRequest('POST', "/api/crud6/mymodel/{$model->id}/related", [
        'related_ids' => [$relatedModel->id],
    ]);
    $response = $this->handleRequestWithTracking($request);
    
    $this->assertResponseStatus(200, $response);
    
    // Verify relationship
    $model->refresh();
    $this->assertTrue($model->related()->where('id', $relatedModel->id)->exists());
}
```

## Pass/Fail/Warn Pattern

### Pass (✓) - Should Succeed
```php
// Test should pass
$this->assertResponseStatus(200, $response);
```

### Fail (Expected) - Should Fail
```php
// Test expects failure
$this->assertResponseStatus(403, $response, 'Should fail without permission');
```

### Warn (Informational) - Accept Multiple Outcomes
```php
// Test is informational, accepts multiple statuses
$status = $response->getStatusCode();
$this->assertContains($status, [200, 403, 404, 500], 
    "Endpoint should be accessible (got {$status})");
```

## API Call Tracking

### Assert No Redundant Calls
```php
public function testNoRedundantCalls(): void
{
    // ... make API calls ...
    
    // Verify no redundant calls
    $this->assertNoRedundantApiCalls();
}
```

### Assert Specific Call Count
```php
public function testCallCount(): void
{
    // ... make API calls ...
    
    // Verify schema endpoint called exactly once
    $this->assertApiCallCount('/api/crud6/mymodel/schema', 1);
}
```

## Running Your Tests

```bash
# Run your specific test
vendor/bin/phpunit app/tests/Integration/MyModelApiTest.php

# Run all integration tests
vendor/bin/phpunit app/tests/Integration/

# Run with verbose output
vendor/bin/phpunit --testdox app/tests/Integration/MyModelApiTest.php
```

## Common Patterns

### Create Test User with Multiple Permissions
```php
$user = User::factory()->create();
$this->actAsUser($user, permissions: [
    'uri_mymodel',
    'create_mymodel',
    'update_mymodel_field',
    'delete_mymodel',
]);
```

### Test Permission Hierarchy
```php
// Test 1: No permission
$noPermUser = User::factory()->create();
$this->actAsUser($noPermUser);
// ... expect 403 ...

// Test 2: Basic permission
$basicUser = User::factory()->create();
$this->actAsUser($basicUser, permissions: ['uri_mymodel']);
// ... expect 200 for read, 403 for write ...

// Test 3: Full permission
$adminUser = User::factory()->create();
$this->actAsUser($adminUser, permissions: ['uri_mymodel', 'create_mymodel']);
// ... expect 200 for all ...
```

## Tips

1. **Always test authentication first**: Verify unauthenticated requests fail
2. **Test permission levels**: Test without permission, with read, with write
3. **Verify database state**: Check that operations actually modified the database
4. **Use factories**: Create test data with model factories
5. **Track API calls**: Use `handleRequestWithTracking()` to detect redundancy
6. **Accept flexible responses**: For exploratory tests, accept multiple status codes
7. **Echo progress**: Use `echo` statements to show test progress

## References

- [SchemaBasedApiTest.php](../Integration/SchemaBasedApiTest.php) - Full example
- [TESTING_METHODOLOGY.md](../../docs/TESTING_METHODOLOGY.md) - Detailed methodology
- [CRUD6 SchemaBasedApiTest](https://github.com/ssnukala/sprinkle-crud6/blob/main/app/tests/Integration/SchemaBasedApiTest.php) - Original
