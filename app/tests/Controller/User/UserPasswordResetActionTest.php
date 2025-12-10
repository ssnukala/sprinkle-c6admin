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
 * Tests for UserPasswordResetAction
 * 
 * This tests the admin password reset functionality that forces a user
 * to reset their password on next login by expiring their current password.
 */
class UserPasswordResetActionTest extends C6AdminTestCase
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

    public function testPasswordResetForGuestUser(): void
    {
        // Create request with method and url and fetch response
        // Using user ID 2 (not 1, which is reserved for superadmin)
        $request = $this->createJsonRequest('POST', '/api/users/2/password-reset');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPasswordResetForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/api/users/' . $user->id . '/password-reset');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPasswordResetSuccess(): void
    {
        /** @var User */
        $user = User::factory()->create([
            'first_name' => 'Kelly',
            'last_name'  => 'Reilly',
            'password_last_set' => new \DateTime('now'),
        ]);
        $this->actAsUser($user, permissions: ['update_user_field']);

        // Verify password_last_set is not null initially
        $this->assertNotNull($user->password_last_set);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/api/users/' . $user->id . '/password-reset');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => '<strong>Kelly Reilly</strong>\'s password has been reset.',
            'description' => '',
        ], $response);

        // Verify password_last_set has been set to null
        $user->refresh();
        $this->assertNull($user->password_last_set);
    }

    public function testPasswordResetWithDifferentUser(): void
    {
        /** @var User */
        $admin = User::factory()->create();
        $this->actAsUser($admin, permissions: ['update_user_field']);

        /** @var User */
        $targetUser = User::factory()->create([
            'first_name' => 'Target',
            'last_name'  => 'User',
            'password_last_set' => new \DateTime('now'),
        ]);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/api/users/' . $targetUser->id . '/password-reset');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);

        // Verify target user's password_last_set has been set to null
        $targetUser->refresh();
        $this->assertNull($targetUser->password_last_set);
    }
}
