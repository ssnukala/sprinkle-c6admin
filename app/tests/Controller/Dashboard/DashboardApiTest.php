<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Controller\Dashboard;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests for DashboardApi
 * 
 * This tests the dashboard API that provides statistics about users, roles, and groups.
 */
class DashboardApiTest extends C6AdminTestCase
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

    public function testDashboardForGuestUser(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/dashboard');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testDashboardForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/dashboard');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testDashboardSuccess(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['uri_dashboard']);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/dashboard');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertNotEmpty((string) $response->getBody());

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // Assert the response contains expected fields
        $this->assertArrayHasKey('users', $data);
        $this->assertArrayHasKey('roles', $data);
        $this->assertArrayHasKey('groups', $data);
    }

    public function testDashboardWithData(): void
    {
        // Create multiple users
        User::factory()->count(5)->create();

        /** @var User */
        $admin = User::factory()->create();
        $this->actAsUser($admin, permissions: ['uri_dashboard']);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/dashboard');
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // Assert user count includes the created users plus admin
        $this->assertGreaterThanOrEqual(6, $data['users']);
    }
}
