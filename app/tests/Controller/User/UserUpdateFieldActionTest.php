<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Controller\User;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests for UserUpdateFieldAction
 * 
 * This tests the field update functionality that allows updating individual
 * fields on a user record, particularly boolean toggle fields like flag_enabled
 * and flag_verified.
 */
class UserUpdateFieldActionTest extends C6AdminTestCase
{
    use RefreshDatabase;
    use WithTestUser;
    use MockeryPHPUnitIntegration;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testUpdateFieldForGuestUser(): void
    {
        // Create a test user to target
        /** @var User */
        $targetUser = User::factory()->create();

        // Create request without authentication
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_enabled', [
            'flag_enabled' => false
        ]);
        $response = $this->handleRequest($request);

        // Assert unauthorized response
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testUpdateFieldWithoutPermission(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user); // No permissions

        /** @var User */
        $targetUser = User::factory()->create([
            'flag_enabled' => true,
        ]);

        // Create request to update field
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_enabled', [
            'flag_enabled' => false
        ]);
        $response = $this->handleRequest($request);

        // Assert forbidden response
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testUpdateBooleanFieldSuccess(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        /** @var User */
        $targetUser = User::factory()->create([
            'user_name'    => 'testuser',
            'flag_enabled' => true,
        ]);

        // Verify initial state
        $this->assertTrue($targetUser->flag_enabled);

        // Create request to disable user
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_enabled', [
            'flag_enabled' => false
        ]);
        $response = $this->handleRequest($request);

        // Assert success response
        $this->assertResponseStatus(200, $response);

        // Verify field was updated
        $targetUser->refresh();
        $this->assertFalse($targetUser->flag_enabled);
    }

    public function testToggleBooleanFieldWithoutValue(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        /** @var User */
        $targetUser = User::factory()->create([
            'user_name'    => 'testuser',
            'flag_enabled' => true,
        ]);

        // Verify initial state
        $this->assertTrue($targetUser->flag_enabled);

        // Create request to toggle field without explicit value
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_enabled', [
            'toggle' => true
        ]);
        $response = $this->handleRequest($request);

        // Assert success response
        $this->assertResponseStatus(200, $response);

        // Verify field was toggled (true -> false)
        $targetUser->refresh();
        $this->assertFalse($targetUser->flag_enabled);
    }

    public function testToggleBooleanFieldBackAndForth(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        /** @var User */
        $targetUser = User::factory()->create([
            'user_name'       => 'testuser',
            'flag_verified' => true,
        ]);

        // Initial state: verified = true
        $this->assertTrue($targetUser->flag_verified);

        // Toggle to false
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_verified', [
            'toggle' => true
        ]);
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(200, $response);
        
        $targetUser->refresh();
        $this->assertFalse($targetUser->flag_verified);

        // Toggle back to true
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_verified', [
            'toggle' => true
        ]);
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(200, $response);
        
        $targetUser->refresh();
        $this->assertTrue($targetUser->flag_verified);
    }

    public function testUpdateBooleanFieldWithStringValues(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        /** @var User */
        $targetUser = User::factory()->create([
            'user_name'    => 'testuser',
            'flag_enabled' => true,
        ]);

        // Test with string 'false'
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_enabled', [
            'flag_enabled' => 'false'
        ]);
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(200, $response);
        
        $targetUser->refresh();
        $this->assertFalse($targetUser->flag_enabled);

        // Test with string 'true'
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_enabled', [
            'flag_enabled' => 'true'
        ]);
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(200, $response);
        
        $targetUser->refresh();
        $this->assertTrue($targetUser->flag_enabled);
    }

    public function testUpdateBooleanFieldWithNumericValues(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['update_user_field']);

        /** @var User */
        $targetUser = User::factory()->create([
            'user_name'    => 'testuser',
            'flag_enabled' => true,
        ]);

        // Test with numeric 0
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_enabled', [
            'flag_enabled' => 0
        ]);
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(200, $response);
        
        $targetUser->refresh();
        $this->assertFalse($targetUser->flag_enabled);

        // Test with numeric 1
        $request = $this->createJsonRequest('PUT', '/api/crud6/users/' . $targetUser->id . '/flag_enabled', [
            'flag_enabled' => 1
        ]);
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(200, $response);
        
        $targetUser->refresh();
        $this->assertTrue($targetUser->flag_enabled);
    }
}
