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
 * Group-related exception.
 * 
 * General exception for group-related operations that fail.
 * The specific description should be set by the controller when throwing this exception.
 * Used for business logic errors related to groups (e.g., validation failures, state errors).
 */
final class GroupException extends UserFacingException
{
    protected string $title = 'GROUP.EXCEPTION';
    protected string|UserMessage $description = 'GROUP.EXCEPTION';
}
