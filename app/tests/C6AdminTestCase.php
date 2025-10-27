<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests;

use UserFrosting\Sprinkle\C6Admin\C6Admin;
use UserFrosting\Testing\TestCase;

/**
 * Test case with C6Admin as main sprinkle
 */
class C6AdminTestCase extends TestCase
{
    protected string $mainSprinkle = C6Admin::class;
}
