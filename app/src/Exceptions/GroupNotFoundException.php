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
 * Group not found exception.
 */
final class GroupNotFoundException extends NotFoundException
{
    protected string|UserMessage $description = 'GROUP.NOT_FOUND';
}
