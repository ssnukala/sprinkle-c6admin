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

use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Role-related exception.
 * 
 * General exception for role-related operations that fail.
 * The specific description should be set by the controller when throwing this exception.
 * Used for business logic errors related to roles (e.g., validation failures, state errors).
 */
final class RoleException extends UserFacingException
{
    protected string $title = 'ROLE.EXCEPTION';
    protected string|UserMessage $description = 'ROLE.EXCEPTION';
}
