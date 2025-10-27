<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Exceptions;

use UserFrosting\Sprinkle\C6Admin\Exceptions\AccountNotFoundException;
use UserFrosting\Sprinkle\C6Admin\Exceptions\GroupException;
use UserFrosting\Sprinkle\C6Admin\Exceptions\GroupNotFoundException;
use UserFrosting\Sprinkle\C6Admin\Exceptions\MissingRequiredParamException;
use UserFrosting\Sprinkle\C6Admin\Exceptions\PermissionNotFoundException;
use UserFrosting\Sprinkle\C6Admin\Exceptions\RoleException;
use UserFrosting\Sprinkle\C6Admin\Exceptions\RoleNotFoundException;
use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;

/**
 * Test for all exception classes.
 */
class ExceptionTest extends C6AdminTestCase
{
    public function testAccountNotFoundException(): void
    {
        $exception = new AccountNotFoundException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertStringContainsString('account', strtolower($exception->getMessage()));
    }

    public function testGroupException(): void
    {
        $exception = new GroupException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertStringContainsString('group', strtolower($exception->getMessage()));
    }

    public function testGroupNotFoundException(): void
    {
        $exception = new GroupNotFoundException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertStringContainsString('group', strtolower($exception->getMessage()));
        $this->assertStringContainsString('not found', strtolower($exception->getMessage()));
    }

    public function testMissingRequiredParamException(): void
    {
        $exception = new MissingRequiredParamException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertStringContainsString('required', strtolower($exception->getMessage()));
    }

    public function testPermissionNotFoundException(): void
    {
        $exception = new PermissionNotFoundException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertStringContainsString('permission', strtolower($exception->getMessage()));
        $this->assertStringContainsString('not found', strtolower($exception->getMessage()));
    }

    public function testRoleException(): void
    {
        $exception = new RoleException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertStringContainsString('role', strtolower($exception->getMessage()));
    }

    public function testRoleNotFoundException(): void
    {
        $exception = new RoleNotFoundException();
        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertStringContainsString('role', strtolower($exception->getMessage()));
        $this->assertStringContainsString('not found', strtolower($exception->getMessage()));
    }
}
