<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-c6admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Exceptions;

use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Group related exceptions. The description is expected to be set by the controller.
 */
final class GroupException extends UserFacingException
{
    protected string $title = 'GROUP.EXCEPTION';
    protected string|UserMessage $description = 'GROUP.EXCEPTION';
}
