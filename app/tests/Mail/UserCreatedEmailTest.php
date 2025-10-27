<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Mail;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\C6Admin\Mail\UserCreatedEmail;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;

/**
 * Test for UserCreatedEmail.
 */
class UserCreatedEmailTest extends C6AdminTestCase
{
    public function testUserCreatedEmailCreation(): void
    {
        /** @var User */
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'User',
        ]);

        $email = new UserCreatedEmail($user, 'test-password');

        $this->assertInstanceOf(UserCreatedEmail::class, $email);
        $this->assertEquals([$user->email => $user->full_name], $email->getTo());
    }

    public function testUserCreatedEmailHasSubject(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $email = new UserCreatedEmail($user, 'test-password');

        $subject = $email->getSubject();
        $this->assertNotEmpty($subject);
        $this->assertIsString($subject);
    }

    public function testUserCreatedEmailWithDifferentUsers(): void
    {
        /** @var User */
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        /** @var User */
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        $email1 = new UserCreatedEmail($user1, 'password1');
        $email2 = new UserCreatedEmail($user2, 'password2');

        $this->assertNotEquals($email1->getTo(), $email2->getTo());
    }
}
