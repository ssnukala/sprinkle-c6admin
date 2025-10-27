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
 * Tests for CacheApiAction
 * 
 * This tests the cache clearing API that allows administrators
 * to clear the application cache.
 */
class CacheApiActionTest extends C6AdminTestCase
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

    public function testCacheClearForGuestUser(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/cache');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testCacheClearForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/cache');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testCacheClearSuccess(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['clear_cache']);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('DELETE', '/api/cache');
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(200, $response);

        // Parse JSON response
        $body = (string) $response->getBody();
        $data = json_decode($body, true);

        // Assert success message
        $this->assertArrayHasKey('title', $data);
        $this->assertStringContainsString('cache', strtolower($data['title']));
    }
}
