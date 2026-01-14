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
 * Test schema directory configuration
 * 
 * Validates that the configurable schema path feature works correctly.
 * Based on sprinkle-crud6 PR #363.
 */
class ConfigurableSchemaPathTest extends TestCase
{
    /**
     * Test that default schema directory exists
     */
    public function testDefaultSchemaDirectoryExists(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $defaultSchemaDir = $baseDir . '/app/schema/crud6';
        
        $this->assertDirectoryExists($defaultSchemaDir, 'Default schema directory should exist');
        $this->assertDirectoryIsReadable($defaultSchemaDir, 'Default schema directory should be readable');
    }

    /**
     * Test that schema files exist in default directory
     */
    public function testSchemaFilesExist(): void
    {
        $baseDir = dirname(__DIR__, 2);
        $schemaDir = $baseDir . '/app/schema/crud6';
        
        $expectedSchemas = ['users', 'roles', 'groups', 'permissions', 'activities'];
        
        foreach ($expectedSchemas as $schema) {
            $schemaFile = $schemaDir . '/' . $schema . '.json';
            $this->assertFileExists($schemaFile, "Schema file {$schema}.json should exist");
            $this->assertFileIsReadable($schemaFile, "Schema file {$schema}.json should be readable");
            
            // Verify it's valid JSON
            $content = file_get_contents($schemaFile);
            $decoded = json_decode($content, true);
            $this->assertIsArray($decoded, "Schema file {$schema}.json should contain valid JSON");
            $this->assertArrayHasKey('model', $decoded, "Schema {$schema}.json should have 'model' key");
        }
    }

    /**
     * Test environment variable parsing
     */
    public function testEnvironmentVariableParsing(): void
    {
        // Save original value
        $original = getenv('TEST_SCHEMA_DIRS');
        
        // Test single directory
        putenv('TEST_SCHEMA_DIRS=app/schema/crud6');
        $envDirs = getenv('TEST_SCHEMA_DIRS');
        $this->assertEquals('app/schema/crud6', $envDirs);
        
        $dirs = array_map('trim', explode(',', $envDirs));
        $this->assertCount(1, $dirs);
        $this->assertEquals('app/schema/crud6', $dirs[0]);
        
        // Test multiple directories
        putenv('TEST_SCHEMA_DIRS=app/schema/crud6,vendor/other/schema,tests/fixtures');
        $envDirs = getenv('TEST_SCHEMA_DIRS');
        $dirs = array_map('trim', explode(',', $envDirs));
        $this->assertCount(3, $dirs);
        $this->assertEquals('app/schema/crud6', $dirs[0]);
        $this->assertEquals('vendor/other/schema', $dirs[1]);
        $this->assertEquals('tests/fixtures', $dirs[2]);
        
        // Restore original value
        if ($original !== false) {
            putenv("TEST_SCHEMA_DIRS={$original}");
        } else {
            putenv('TEST_SCHEMA_DIRS');
        }
    }

    /**
     * Test path normalization logic
     */
    public function testPathNormalization(): void
    {
        $baseDir = dirname(__DIR__, 2);
        
        // Test relative path conversion
        $relativePath = 'app/schema/crud6';
        $expected = $baseDir . '/' . $relativePath;
        $isAbsolute = str_starts_with($relativePath, '/');
        $this->assertFalse($isAbsolute, 'Relative path should not start with /');
        
        $normalized = $isAbsolute ? $relativePath : $baseDir . '/' . $relativePath;
        $this->assertEquals($expected, $normalized);
        
        // Test absolute path
        $absolutePath = '/absolute/path';
        $isAbsolute = str_starts_with($absolutePath, '/');
        $this->assertTrue($isAbsolute, 'Absolute path should start with /');
        
        $normalized = $isAbsolute ? $absolutePath : $baseDir . '/' . $absolutePath;
        $this->assertEquals($absolutePath, $normalized);
    }

    /**
     * Test that phpunit.xml configuration is set
     */
    public function testPhpunitXmlConfiguration(): void
    {
        $phpunitXml = dirname(__DIR__, 2) . '/phpunit.xml';
        
        $this->assertFileExists($phpunitXml, 'phpunit.xml should exist');
        
        $content = file_get_contents($phpunitXml);
        $this->assertStringContainsString('TEST_SCHEMA_DIRS', $content, 
            'phpunit.xml should contain TEST_SCHEMA_DIRS configuration');
        $this->assertStringContainsString('app/schema/crud6', $content,
            'phpunit.xml should configure app/schema/crud6 as schema directory');
    }

    /**
     * Test schema directory discovery
     * 
     * This test uses reflection to test the protected methods in C6AdminTestCase
     * to ensure they work correctly.
     */
    public function testSchemaDirectoryDiscovery(): void
    {
        // Create a test instance
        $testCase = new class extends C6AdminTestCase {
            public function testDummy(): void
            {
                $this->assertTrue(true);
            }
            
            // Expose protected methods for testing
            public function publicGetTestSchemaDirs(): array
            {
                return $this->getTestSchemaDirs();
            }
            
            public function publicNormalizeTestSchemaDirs(array $dirs): array
            {
                return $this->normalizeTestSchemaDirs($dirs);
            }
        };
        
        // Test normalization with existing directory
        $baseDir = dirname(__DIR__, 2);
        $existingDir = 'app/schema/crud6';
        $normalized = $testCase->publicNormalizeTestSchemaDirs([$existingDir]);
        
        $this->assertCount(1, $normalized, 'Should normalize one existing directory');
        $this->assertStringContainsString('app/schema/crud6', $normalized[0], 
            'Normalized path should contain schema directory');
        
        // Test normalization with non-existing directory (should be filtered out)
        $nonExistingDir = 'non/existing/path';
        $normalized = $testCase->publicNormalizeTestSchemaDirs([$existingDir, $nonExistingDir]);
        
        $this->assertCount(1, $normalized, 'Should filter out non-existing directory');
        
        // Test duplicate removal
        $normalized = $testCase->publicNormalizeTestSchemaDirs([$existingDir, $existingDir]);
        $this->assertCount(1, $normalized, 'Should remove duplicate directories');
    }
}
