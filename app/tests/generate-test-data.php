<?php

declare(strict_types=1);

/*
 * UserFrosting C6Admin Sprinkle (https://github.com/ssnukala/sprinkle-c6admin)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2024 Srinivas Nukala
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

/**
 * Dynamic Test Data SQL Generator
 * 
 * This script generates SQL INSERT statements dynamically based on:
 * 1. JSON schema definitions (app/schema/crud6/*.json)
 * 2. Test data configuration (test-data-config.json)
 * 
 * Benefits:
 * - Schema-driven: Adapts automatically to schema changes
 * - Flexible: Easy to add/modify test data via JSON configuration
 * - Reusable: Works with any model that has a JSON schema
 * - Type-safe: Generates correct SQL based on field types
 * 
 * Usage:
 *   php generate-test-data.php > test-data.sql
 *   php generate-test-data.php --execute  # Execute directly against database
 */

// Configuration
$schemaDir = __DIR__ . '/../schema/crud6';
$configFile = __DIR__ . '/test-data-config.json';

// Load test data configuration
if (!file_exists($configFile)) {
    fwrite(STDERR, "Error: Configuration file not found: {$configFile}\n");
    fwrite(STDERR, "Please create test-data-config.json with your test data definitions.\n");
    exit(1);
}

$config = json_decode(file_get_contents($configFile), true);
if (!$config) {
    fwrite(STDERR, "Error: Failed to parse configuration file: {$configFile}\n");
    exit(1);
}

/**
 * Load schema for a model
 */
function loadSchema(string $model, string $schemaDir): ?array
{
    $schemaFile = "{$schemaDir}/{$model}.json";
    if (!file_exists($schemaFile)) {
        fwrite(STDERR, "Warning: Schema file not found: {$schemaFile}\n");
        return null;
    }
    
    $schema = json_decode(file_get_contents($schemaFile), true);
    if (!$schema) {
        fwrite(STDERR, "Warning: Failed to parse schema file: {$schemaFile}\n");
        return null;
    }
    
    return $schema;
}

/**
 * Get SQL value for a field based on type
 */
function getSqlValue(mixed $value, array $fieldDef): string
{
    if ($value === null) {
        return 'NULL';
    }
    
    $type = $fieldDef['type'] ?? 'string';
    
    switch ($type) {
        case 'integer':
        case 'int':
            return (string) (int) $value;
            
        case 'boolean':
        case 'bool':
            return $value ? '1' : '0';
            
        case 'float':
        case 'decimal':
        case 'double':
            return (string) (float) $value;
            
        case 'date':
        case 'datetime':
        case 'timestamp':
            if ($value === 'now' || $value === 'CURRENT_TIMESTAMP') {
                return 'CURRENT_TIMESTAMP';
            }
            return "'" . addslashes($value) . "'";
            
        case 'json':
            if (is_array($value)) {
                $value = json_encode($value);
            }
            return "'" . addslashes($value) . "'";
            
        case 'string':
        case 'text':
        default:
            return "'" . addslashes($value) . "'";
    }
}

/**
 * Generate INSERT statement for a model
 */
function generateInsert(string $model, array $data, array $schema): string
{
    $table = $schema['table'] ?? $model;
    $fields = $schema['fields'] ?? [];
    
    // Check if we need to handle group_id lookup via _group_slug
    $groupSlug = $data['_group_slug'] ?? null;
    $hasGroupLookup = ($groupSlug !== null);
    
    // Filter out fields that shouldn't be inserted
    $insertFields = [];
    $insertValues = [];
    
    foreach ($data as $field => $value) {
        // Skip metadata fields (start with _)
        if (str_starts_with($field, '_')) {
            continue;
        }
        
        // Skip if field not in schema
        if (!isset($fields[$field])) {
            continue;
        }
        
        $fieldDef = $fields[$field];
        
        // Skip auto_increment and readonly fields unless explicitly provided
        if (($fieldDef['auto_increment'] ?? false) && $value === null) {
            continue;
        }
        
        // Allow explicit ID values even if readonly
        if (($fieldDef['readonly'] ?? false) && $field !== 'id' && !in_array($field, ['created_at', 'updated_at'])) {
            continue;
        }
        
        $insertFields[] = "`{$field}`";
        $insertValues[] = getSqlValue($value, $fieldDef);
    }
    
    // If we have a group lookup, add group_id field
    if ($hasGroupLookup && !in_array('`group_id`', $insertFields)) {
        $insertFields[] = "`group_id`";
    }
    
    if (empty($insertFields)) {
        return '';
    }
    
    // Build the INSERT statement
    if ($hasGroupLookup) {
        // Use SELECT to lookup group_id by slug
        $sql = "INSERT INTO `{$table}` (" . implode(', ', $insertFields) . ")\n";
        $sql .= "SELECT " . implode(', ', $insertValues) . ", `groups`.`id`\n";
        $sql .= "FROM `groups`\n";
        $sql .= "WHERE `groups`.`slug` = '" . addslashes($groupSlug) . "'";
    } else {
        $sql = "INSERT INTO `{$table}` (" . implode(', ', $insertFields) . ")\n";
        $sql .= "VALUES (" . implode(', ', $insertValues) . ")";
    }
    
    // Add ON DUPLICATE KEY UPDATE clause for idempotency
    $updateClauses = [];
    foreach ($insertFields as $i => $field) {
        // Clean field name (remove backticks)
        $cleanField = str_replace('`', '', $field);
        // Update all fields except primary key 'id'
        if ($cleanField !== 'id') {
            $updateClauses[] = "{$field} = VALUES({$field})";
        }
    }
    
    if (!empty($updateClauses)) {
        $sql .= "\nON DUPLICATE KEY UPDATE\n    " . implode(",\n    ", $updateClauses);
    }
    
    return $sql . ";\n";
}

/**
 * Generate relationship INSERT (for pivot tables)
 */
function generateRelationshipInsert(
    string $pivotTable,
    string $foreignKey,
    string $relatedKey,
    array $data
): string {
    $sql = "INSERT INTO `{$pivotTable}` (`{$foreignKey}`, `{$relatedKey}`)\n";
    
    // Handle both direct values and SELECT statements
    if (isset($data['select'])) {
        $sql .= $data['select'];
    } else {
        $foreignId = $data[$foreignKey] ?? 'NULL';
        $relatedId = $data[$relatedKey] ?? 'NULL';
        $sql .= "VALUES ({$foreignId}, {$relatedId})";
    }
    
    $sql .= "\nON DUPLICATE KEY UPDATE `{$foreignKey}` = VALUES(`{$foreignKey}`)";
    
    return $sql . ";\n";
}

// ========================================
// Generate SQL Header
// ========================================
echo "-- ========================================\n";
echo "-- Dynamically Generated Test Data SQL\n";
echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
echo "-- Source: {$configFile}\n";
echo "-- ========================================\n\n";

// ========================================
// Process Each Model in Configuration
// ========================================
foreach ($config['models'] ?? [] as $modelConfig) {
    $model = $modelConfig['model'];
    $schema = loadSchema($model, $schemaDir);
    
    if (!$schema) {
        fwrite(STDERR, "Skipping model '{$model}' due to schema loading failure\n");
        continue;
    }
    
    echo "-- ========================================\n";
    echo "-- {$modelConfig['description']}\n";
    echo "-- Model: {$model}\n";
    echo "-- Table: {$schema['table']}\n";
    echo "-- ========================================\n\n";
    
    // Generate INSERT for each record
    foreach ($modelConfig['records'] ?? [] as $record) {
        $comment = $record['_comment'] ?? '';
        if ($comment) {
            echo "-- {$comment}\n";
        }
        
        // Remove metadata fields
        $cleanRecord = $record;
        unset($cleanRecord['_comment'], $cleanRecord['_relationships']);
        
        $sql = generateInsert($model, $cleanRecord, $schema);
        if ($sql) {
            echo $sql . "\n";
        }
    }
    
    echo "\n";
}

// ========================================
// Process Relationships
// ========================================
if (isset($config['relationships'])) {
    echo "-- ========================================\n";
    echo "-- Relationship Assignments (Pivot Tables)\n";
    echo "-- ========================================\n\n";
    
    foreach ($config['relationships'] as $relConfig) {
        echo "-- {$relConfig['description']}\n";
        
        foreach ($relConfig['assignments'] ?? [] as $assignment) {
            $sql = generateRelationshipInsert(
                $relConfig['pivot_table'],
                $relConfig['foreign_key'],
                $relConfig['related_key'],
                $assignment
            );
            echo $sql . "\n";
        }
    }
    
    echo "\n";
}

// ========================================
// Generate Summary
// ========================================
echo "-- ========================================\n";
echo "-- Summary\n";
echo "-- ========================================\n";

$totalRecords = 0;
foreach ($config['models'] ?? [] as $modelConfig) {
    $count = count($modelConfig['records'] ?? []);
    $totalRecords += $count;
    echo "-- {$modelConfig['model']}: {$count} record(s)\n";
}

$totalRelationships = 0;
foreach ($config['relationships'] ?? [] as $relConfig) {
    $count = count($relConfig['assignments'] ?? []);
    $totalRelationships += $count;
    echo "-- {$relConfig['pivot_table']}: {$count} assignment(s)\n";
}

echo "-- Total: {$totalRecords} records, {$totalRelationships} relationships\n";
echo "-- ========================================\n";

fwrite(STDERR, "\n✅ SQL generation completed successfully\n");
fwrite(STDERR, "   - {$totalRecords} records\n");
fwrite(STDERR, "   - {$totalRelationships} relationship assignments\n");
fwrite(STDERR, "\nUsage:\n");
fwrite(STDERR, "   Save to file: php generate-test-data.php > test-data.sql\n");
fwrite(STDERR, "   Use in CI: mysql -h host -u user -p database < test-data.sql\n");
