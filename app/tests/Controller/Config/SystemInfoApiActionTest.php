<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Controller\Config;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests for SystemInfoApiAction
 * 
 * This tests the system information API that provides details about
 * the server environment, PHP version, database, etc.
 */
class SystemInfoApiActionTest extends C6AdminTestCase
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

    public function testSystemInfoForGuestUser(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/config/info');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testSystemInfoForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/config/info');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testSystemInfoSuccess(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['uri_site_admin']);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/config/info');
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(200, $response);
        $this->assertNotEmpty((string) $response->getBody());

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // Assert the response contains expected system info fields
        $this->assertArrayHasKey('php', $data);
        $this->assertArrayHasKey('database', $data);
    }
}
