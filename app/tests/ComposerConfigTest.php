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

use PHPUnit\Framework\TestCase;

/**
 * Test composer.json configuration
 */
class ComposerConfigTest extends TestCase
{
    /**
     * Test that sprinkle-crud6 dependency uses dev-main branch
     */
    public function testCrud6DependencyUsesDevMain(): void
    {
        $composerJson = json_decode(file_get_contents(__DIR__ . '/../../composer.json'), true);
        
        $this->assertIsArray($composerJson, 'composer.json should be valid JSON');
        $this->assertArrayHasKey('require', $composerJson, 'composer.json should have require section');
        $this->assertArrayHasKey('ssnukala/sprinkle-crud6', $composerJson['require'], 'sprinkle-crud6 should be in requirements');
        
        // Verify that it uses dev-main, not a tagged version
        $this->assertEquals(
            'dev-main',
            $composerJson['require']['ssnukala/sprinkle-crud6'],
            'sprinkle-crud6 should use dev-main branch instead of tagged version'
        );
    }
}
