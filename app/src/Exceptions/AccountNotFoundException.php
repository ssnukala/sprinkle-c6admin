<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Exceptions;

use UserFrosting\Sprinkle\Core\Exceptions\NotFoundException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Account not found exception.
 * 
 * Thrown when an account (user, group, role, or permission) cannot be found in the database.
 * This is a generic exception for account-related "not found" scenarios.
 */
final class AccountNotFoundException extends NotFoundException
{
    protected string $title = 'ACCOUNT.EXCEPTION.NOT_FOUND.TITLE';
    protected string|UserMessage $description = 'ACCOUNT.EXCEPTION.NOT_FOUND.DESCRIPTION';
}
