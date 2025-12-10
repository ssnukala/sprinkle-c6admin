<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\C6Admin\Tests\Schema;

use UserFrosting\Sprinkle\C6Admin\Tests\C6AdminTestCase;

/**
 * Test for JSON schemas.
 * 
 * Validates that all schemas are properly formatted and contain required fields.
 */
class SchemaValidationTest extends C6AdminTestCase
{
    /** @var string Base path for schema files */
    protected const SCHEMA_BASE_PATH = __DIR__ . '/../../../../schema/crud6';

    /** @var string Base path for locale files */
    protected const LOCALE_BASE_PATH = __DIR__ . '/../../../../locale';

    protected array $schemas = [
        'users',
        'roles',
        'groups',
        'permissions',
        'activities',
    ];

    /**
     * Get the full path to a schema file.
     */
    protected function getSchemaPath(string $schema): string
    {
        return self::SCHEMA_BASE_PATH . "/{$schema}.json";
    }

    /**
     * Get the full path to a locale file.
     */
    protected function getLocalePath(string $locale): string
    {
        return self::LOCALE_BASE_PATH . "/{$locale}/messages.php";
    }

    public function testAllSchemasExist(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $this->assertFileExists($path, "Schema file {$schema}.json not found");
        }
    }

    public function testAllSchemasAreValidJson(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $this->assertNotFalse($content, "Could not read {$schema}.json");
            
            $decoded = json_decode($content, true);
            $this->assertNotNull($decoded, "Invalid JSON in {$schema}.json: " . json_last_error_msg());
        }
    }

    public function testSchemasHaveRequiredFields(): void
    {
        $requiredFields = ['model', 'table', 'fields', 'primary_key'];

        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($requiredFields as $field) {
                $this->assertArrayHasKey($field, $decoded, "Schema {$schema}.json missing required field: {$field}");
            }
        }
    }

    public function testSchemasHaveValidFields(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            $this->assertIsArray($decoded['fields'], "Schema {$schema}.json fields must be an array");
            $this->assertNotEmpty($decoded['fields'], "Schema {$schema}.json must have at least one field");

            foreach ($decoded['fields'] as $field => $config) {
                $this->assertIsArray($config, "Field {$field} in {$schema}.json must be an array");
            }
        }
    }

    public function testUserSchemaHasIdField(): void
    {
        $path = $this->getSchemaPath('users');
        $content = file_get_contents($path);
        $decoded = json_decode($content, true);

        $this->assertArrayHasKey('id', $decoded['fields'], "users.json must have 'id' field");
    }

    public function testSchemasUseIdAsPrimaryKey(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            // Check if schema has a 'primary_key' field and it's set to 'id'
            $this->assertArrayHasKey('primary_key', $decoded, "Schema {$schema}.json must have 'primary_key' field");
            $this->assertEquals('id', $decoded['primary_key'], "Schema {$schema}.json should use 'id' as primary_key");

            // Also check that 'id' field exists
            $this->assertArrayHasKey('id', $decoded['fields'], "Schema {$schema}.json must have 'id' field");
        }
    }

    /**
     * Test that searchable attribute does not exist in any field.
     * According to cleanup rules, searchable should be removed and only filterable should be used.
     */
    public function testSchemasDoNotHaveSearchableAttribute(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($decoded['fields'] as $fieldName => $field) {
                $this->assertArrayNotHasKey(
                    'searchable',
                    $field,
                    "Field '{$fieldName}' in {$schema}.json should not have 'searchable' attribute - use 'filterable' instead"
                );
            }
        }
    }

    /**
     * Test that sortable attribute only exists when listable is true.
     * If a field is not listable, it should not have sortable attribute.
     */
    public function testSortableOnlyExistsWhenListable(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($decoded['fields'] as $fieldName => $field) {
                $listable = $field['listable'] ?? false;
                
                if (!$listable && isset($field['sortable'])) {
                    $this->fail(
                        "Field '{$fieldName}' in {$schema}.json has 'sortable' attribute but listable is false. " .
                        "Sortable should only exist when listable is true."
                    );
                }
            }
        }
    }

    /**
     * Test that filterable attribute only exists when listable is true.
     * If a field is not listable, it should not have filterable attribute.
     */
    public function testFilterableOnlyExistsWhenListable(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            foreach ($decoded['fields'] as $fieldName => $field) {
                $listable = $field['listable'] ?? false;
                
                if (!$listable && isset($field['filterable'])) {
                    $this->fail(
                        "Field '{$fieldName}' in {$schema}.json has 'filterable' attribute but listable is false. " .
                        "Filterable should only exist when listable is true."
                    );
                }
            }
        }
    }

    /**
     * Test that default_sort fields are both listable and sortable.
     * According to UserFrosting 6 Sprunje conventions, the sort field needs to be sortable,
     * and if it's used as the default sort field, it should also be visible in the table (listable).
     */
    public function testDefaultSortFieldsAreListableAndSortable(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            // Skip if no default_sort is defined
            if (!isset($decoded['default_sort'])) {
                continue;
            }

            foreach ($decoded['default_sort'] as $fieldName => $direction) {
                $this->assertArrayHasKey(
                    $fieldName,
                    $decoded['fields'],
                    "Default sort field '{$fieldName}' in {$schema}.json must exist in fields"
                );

                $field = $decoded['fields'][$fieldName];
                $listable = $field['listable'] ?? false;
                $sortable = $field['sortable'] ?? false;

                $this->assertTrue(
                    $listable,
                    "Default sort field '{$fieldName}' in {$schema}.json must be listable " .
                    "(UserFrosting 6 Sprunje convention)"
                );

                $this->assertTrue(
                    $sortable,
                    "Default sort field '{$fieldName}' in {$schema}.json must be sortable " .
                    "(UserFrosting 6 Sprunje convention)"
                );
            }
        }
    }

    /**
     * Test that all description fields in schemas use translation keys (not English text).
     * All descriptions should start with "CRUD6." to ensure they are translatable.
     */
    public function testDescriptionsUseTranslationKeys(): void
    {
        foreach ($this->schemas as $schema) {
            $path = $this->getSchemaPath($schema);
            $content = file_get_contents($path);
            $decoded = json_decode($content, true);

            // Check top-level description
            if (isset($decoded['description'])) {
                $this->assertStringStartsWith(
                    'CRUD6.',
                    $decoded['description'],
                    "Schema {$schema}.json top-level description must use translation key starting with 'CRUD6.'"
                );
            }

            // Check field descriptions
            foreach ($decoded['fields'] as $fieldName => $field) {
                if (isset($field['description'])) {
                    $this->assertStringStartsWith(
                        'CRUD6.',
                        $field['description'],
                        "Field '{$fieldName}' description in {$schema}.json must use translation key starting with 'CRUD6.'"
                    );
                }
            }

            // Check relationship action descriptions
            if (isset($decoded['relationships'])) {
                foreach ($decoded['relationships'] as $relIndex => $relationship) {
                    if (isset($relationship['actions'])) {
                        foreach ($relationship['actions'] as $actionType => $action) {
                            if (isset($action['description'])) {
                                $this->assertStringStartsWith(
                                    'CRUD6.',
                                    $action['description'],
                                    "Relationship action '{$actionType}' description in {$schema}.json must use translation key starting with 'CRUD6.'"
                                );
                            }
                            // Check nested attach actions
                            if ($actionType === 'on_create' && isset($action['attach'])) {
                                foreach ($action['attach'] as $attachIndex => $attachAction) {
                                    if (isset($attachAction['description'])) {
                                        $this->assertStringStartsWith(
                                            'CRUD6.',
                                            $attachAction['description'],
                                            "Relationship attach action description in {$schema}.json must use translation key starting with 'CRUD6.'"
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Test that translation keys used in schemas exist in locale files.
     */
    public function testTranslationKeysExistInLocaleFiles(): void
    {
        $localeFiles = [
            'en_US' => $this->getLocalePath('en_US'),
            'fr_FR' => $this->getLocalePath('fr_FR'),
        ];

        // Keys that should exist based on the schemas
        $expectedKeys = [
            'CRUD6.USER.VERIFIED_DESCRIPTION',
            'CRUD6.USER.ENABLED_DESCRIPTION',
            'CRUD6.USER.ROLE_IDS_DESCRIPTION',
            'CRUD6.USER.ASSIGN_DEFAULT_ROLE_DESCRIPTION',
            'CRUD6.USER.SYNC_ROLES_DESCRIPTION',
            'CRUD6.USER.DETACH_ROLES_DESCRIPTION',
            'CRUD6.ROLE.PERMISSION_IDS_DESCRIPTION',
            'CRUD6.ROLE.SYNC_PERMISSIONS_DESCRIPTION',
            'CRUD6.ROLE.DETACH_PERMISSIONS_DESCRIPTION',
            'CRUD6.ROLE.DETACH_USERS_DESCRIPTION',
            'CRUD6.PERMISSION.ROLE_IDS_DESCRIPTION',
            'CRUD6.PERMISSION.SYNC_ROLES_DESCRIPTION',
            'CRUD6.PERMISSION.DETACH_ROLES_DESCRIPTION',
        ];

        foreach ($localeFiles as $locale => $path) {
            $this->assertFileExists($path, "Locale file for {$locale} not found");
            $messages = include $path;
            
            foreach ($expectedKeys as $key) {
                $keyParts = explode('.', $key);
                $current = $messages;
                
                foreach ($keyParts as $part) {
                    $this->assertArrayHasKey(
                        $part,
                        $current,
                        "Translation key '{$key}' not found in {$locale} locale"
                    );
                    $current = $current[$part];
                }
                
                // Verify the final value is a non-empty string
                $this->assertIsString($current, "Translation for '{$key}' in {$locale} must be a string");
                $this->assertNotEmpty($current, "Translation for '{$key}' in {$locale} must not be empty");
            }
        }
    }
}
