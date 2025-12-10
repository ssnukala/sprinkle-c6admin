#!/usr/bin/env node

/**
 * Generate Integration Test Paths from CRUD6 Schemas
 * 
 * This script reads JSON schemas from app/schema/crud6/ and generates
 * authenticated and unauthenticated API test paths based on the schema
 * structure, permissions, and actions defined in each schema.
 * 
 * Usage: node generate-test-paths-from-schemas.js <schema_dir> <output_file>
 * Example: node generate-test-paths-from-schemas.js app/schema/crud6 .github/config/integration-test-paths.json
 */

import { readFileSync, writeFileSync, readdirSync } from 'fs';
import { join, basename } from 'path';

// Parse command line arguments
const schemaDir = process.argv[2];
const outputFile = process.argv[3];

if (!schemaDir || !outputFile) {
    console.error('Usage: node generate-test-paths-from-schemas.js <schema_dir> <output_file>');
    console.error('Example: node generate-test-paths-from-schemas.js app/schema/crud6 .github/config/integration-test-paths.json');
    process.exit(1);
}

console.log('================================================================================');
console.log('Generating Integration Test Paths from CRUD6 Schemas');
console.log('================================================================================');
console.log(`Schema directory: ${schemaDir}`);
console.log(`Output file: ${outputFile}`);
console.log('');

// Read all schema files
const schemaFiles = readdirSync(schemaDir).filter(file => file.endsWith('.json'));
console.log(`Found ${schemaFiles.length} schema files:`);
schemaFiles.forEach(file => console.log(`  - ${file}`));
console.log('');

// Base configuration
const config = {
    description: "Integration test paths configuration for C6Admin sprinkle - Auto-generated from CRUD6 schemas",
    generated_at: new Date().toISOString(),
    generator_script: "generate-test-paths-from-schemas.js",
    config: {
        base_url: "http://localhost:8080",
        auth: {
            username: "admin",
            password: "admin123"
        }
    },
    paths: {
        authenticated: {
            api: {},
            frontend: {}
        },
        unauthenticated: {
            api: {},
            frontend: {}
        }
    }
};

// Add C6Admin dashboard API endpoint
config.paths.unauthenticated.api.c6_dashboard = {
    method: "GET",
    path: "/api/c6/dashboard",
    description: "Attempt to access C6Admin dashboard without authentication",
    expected_status: 401,
    validation: {
        type: "status_only"
    }
};

config.paths.authenticated.api.c6_dashboard = {
    method: "GET",
    path: "/api/c6/dashboard",
    description: "Get C6Admin dashboard statistics",
    expected_status: 200,
    validation: {
        type: "json",
        contains: ["users", "groups", "roles", "permissions"]
    }
};

// Process each schema file
schemaFiles.forEach(schemaFile => {
    const schemaPath = join(schemaDir, schemaFile);
    const schema = JSON.parse(readFileSync(schemaPath, 'utf8'));
    const model = schema.model;
    const modelSingular = model.endsWith('s') ? model.slice(0, -1) : model;
    
    console.log(`Processing schema: ${model}`);
    
    // Generate unauthenticated API paths
    console.log(`  Generating unauthenticated API paths for ${model}...`);
    
    // Schema endpoint
    config.paths.unauthenticated.api[`${model}_schema`] = {
        method: "GET",
        path: `/api/crud6/${model}/schema`,
        description: `Attempt to access ${model} schema without authentication`,
        expected_status: 401,
        validation: {
            type: "status_only"
        }
    };
    
    // List endpoint
    config.paths.unauthenticated.api[`${model}_list`] = {
        method: "GET",
        path: `/api/crud6/${model}`,
        description: `Attempt to access ${model} list without authentication`,
        expected_status: 401,
        validation: {
            type: "status_only"
        }
    };
    
    // Single endpoint
    const singleId = (model === 'users' || model === 'groups') ? '2' : '1';
    config.paths.unauthenticated.api[`${model}_single`] = {
        method: "GET",
        path: `/api/crud6/${model}/${singleId}`,
        description: `Attempt to access single ${modelSingular} without authentication`,
        expected_status: 401,
        validation: {
            type: "status_only"
        }
    };
    
    // Create endpoint (only for certain models)
    if (model === 'users') {
        config.paths.unauthenticated.api[`${model}_create`] = {
            method: "POST",
            path: `/api/crud6/${model}`,
            description: `Attempt to create ${modelSingular} without authentication`,
            expected_status: 401,
            validation: {
                type: "status_only"
            }
        };
        
        config.paths.unauthenticated.api[`${model}_update`] = {
            method: "PUT",
            path: `/api/crud6/${model}/2`,
            description: `Attempt to update ${modelSingular} without authentication`,
            expected_status: 401,
            validation: {
                type: "status_only"
            }
        };
        
        config.paths.unauthenticated.api[`${model}_delete`] = {
            method: "DELETE",
            path: `/api/crud6/${model}/2`,
            description: `Attempt to delete ${modelSingular} without authentication`,
            expected_status: 401,
            validation: {
                type: "status_only"
            }
        };
    }
    
    // Generate authenticated API paths
    console.log(`  Generating authenticated API paths for ${model}...`);
    
    // Schema endpoint
    config.paths.authenticated.api[`${model}_schema`] = {
        method: "GET",
        path: `/api/crud6/${model}/schema`,
        description: `Get ${model} schema definition`,
        expected_status: 200,
        validation: {
            type: "json",
            contains: ["model", "fields"]
        }
    };
    
    // List endpoint
    config.paths.authenticated.api[`${model}_list`] = {
        method: "GET",
        path: `/api/crud6/${model}`,
        description: `Get list of ${model} via CRUD6 API`,
        expected_status: 200,
        validation: {
            type: "json",
            contains: ["rows"]
        }
    };
    
    // Single endpoint
    config.paths.authenticated.api[`${model}_single`] = {
        method: "GET",
        path: `/api/crud6/${model}/${singleId}`,
        description: `Get single ${modelSingular} by ID via CRUD6 API`,
        expected_status: 200,
        validation: {
            type: "json",
            contains: ["id"]
        }
    };
    
    // Create endpoint (for all models that support it)
    if (schema.permissions?.create) {
        config.paths.authenticated.api[`${model}_create`] = {
            method: "POST",
            path: `/api/crud6/${model}`,
            description: `Create new ${modelSingular} via CRUD6 API`,
            expected_status: 201,
            validation: {
                type: "json",
                contains: ["data", "id"]
            },
            requires_permission: schema.permissions.create
        };
    }
    
    // Update endpoint (for all models that support it)
    if (schema.permissions?.update) {
        config.paths.authenticated.api[`${model}_update`] = {
            method: "PUT",
            path: `/api/crud6/${model}/${singleId}`,
            description: `Update ${modelSingular} via CRUD6 API`,
            expected_status: 200,
            validation: {
                type: "json"
            },
            requires_permission: schema.permissions.update
        };
    }
    
    // Delete endpoint (for all models that support it)
    if (schema.permissions?.delete) {
        config.paths.authenticated.api[`${model}_delete`] = {
            method: "DELETE",
            path: `/api/crud6/${model}/${singleId}`,
            description: `Delete ${modelSingular} via CRUD6 API`,
            expected_status: 200,
            validation: {
                type: "json"
            },
            requires_permission: schema.permissions.delete,
            note: `Uses ID ${singleId} for testing`
        };
    }
    
    // Add action-based endpoints from schema
    if (schema.actions && Array.isArray(schema.actions)) {
        console.log(`  Found ${schema.actions.length} actions in ${model} schema`);
        schema.actions.forEach(action => {
            if (action.type === 'field_update' && action.field) {
                // Field update action
                const actionKey = `${model}_update_${action.field}`;
                config.paths.authenticated.api[actionKey] = {
                    method: "PUT",
                    path: `/api/crud6/${model}/${singleId}/${action.field}`,
                    description: `Update ${action.field} field for ${modelSingular}`,
                    expected_status: 200,
                    validation: {
                        type: "json"
                    },
                    requires_permission: action.permission || schema.permissions?.update || "update_" + modelSingular + "_field"
                };
            } else if (action.type === 'api_call' && action.method === 'POST') {
                // Custom action
                const actionKey = `${model}_action_${action.key}`;
                config.paths.authenticated.api[actionKey] = {
                    method: "POST",
                    path: `/api/crud6/${model}/${singleId}/a/${action.key}`,
                    description: `Execute ${action.key} action on ${modelSingular}`,
                    expected_status: 200,
                    validation: {
                        type: "status_any",
                        acceptable_statuses: [200, 404, 500]
                    },
                    requires_permission: action.permission || schema.permissions?.update
                };
            }
        });
    }
    
    // Add relationship endpoints from schema
    if (schema.relationships && Array.isArray(schema.relationships)) {
        console.log(`  Found ${schema.relationships.length} relationships in ${model} schema`);
        schema.relationships.forEach(rel => {
            if (rel.type === 'many_to_many') {
                // Attach relationship
                const attachKey = `${model}_relationship_attach_${rel.name}`;
                config.paths.authenticated.api[attachKey] = {
                    method: "POST",
                    path: `/api/crud6/${model}/${singleId}/${rel.name}`,
                    description: `Attach ${rel.name} to ${modelSingular} (many-to-many relationship)`,
                    expected_status: 200,
                    validation: {
                        type: "status_any",
                        acceptable_statuses: [200, 403]
                    }
                };
                
                // Detach relationship
                const detachKey = `${model}_relationship_detach_${rel.name}`;
                config.paths.authenticated.api[detachKey] = {
                    method: "DELETE",
                    path: `/api/crud6/${model}/${singleId}/${rel.name}`,
                    description: `Detach ${rel.name} from ${modelSingular} (many-to-many relationship)`,
                    expected_status: 200,
                    validation: {
                        type: "status_any",
                        acceptable_statuses: [200, 403]
                    }
                };
            }
        });
    }
    
    // Generate frontend paths
    console.log(`  Generating frontend paths for ${model}...`);
    
    // List page
    config.paths.authenticated.frontend[`c6_${model}_list`] = {
        path: `/c6/admin/${model}`,
        description: `C6Admin ${model} list page`,
        screenshot: true,
        screenshot_name: `c6admin_${model}_list`
    };
    
    // Detail page
    config.paths.authenticated.frontend[`c6_${modelSingular}_detail`] = {
        path: `/c6/admin/${model}/${singleId}`,
        description: `C6Admin ${modelSingular} detail page`,
        screenshot: true,
        screenshot_name: `c6admin_${modelSingular}_detail`
    };
    
    console.log(`  ✓ Completed ${model}`);
    console.log('');
});

// Add dashboard and config frontend pages
console.log('Adding C6Admin specific frontend pages...');
config.paths.authenticated.frontend.c6_dashboard = {
    path: "/c6/admin/dashboard",
    description: "C6Admin Dashboard page",
    screenshot: true,
    screenshot_name: "c6admin_dashboard"
};

config.paths.authenticated.frontend.c6_config = {
    path: "/c6/admin/config",
    description: "C6Admin Config page",
    screenshot: true,
    screenshot_name: "c6admin_config"
};

// Add unauthenticated frontend redirect test
config.paths.unauthenticated.frontend.redirect_to_login = {
    path: "/c6/admin/dashboard",
    description: "Should redirect to login when not authenticated",
    expected_redirect: "/account/sign-in"
};

console.log('');
console.log('================================================================================');
console.log('Path Generation Summary');
console.log('================================================================================');
console.log(`Unauthenticated API paths: ${Object.keys(config.paths.unauthenticated.api).length}`);
console.log(`Authenticated API paths: ${Object.keys(config.paths.authenticated.api).length}`);
console.log(`Authenticated frontend paths: ${Object.keys(config.paths.authenticated.frontend).length}`);
console.log(`Unauthenticated frontend paths: ${Object.keys(config.paths.unauthenticated.frontend).length}`);
console.log('');

// Write output file
console.log(`Writing to ${outputFile}...`);
writeFileSync(outputFile, JSON.stringify(config, null, 2));
console.log('✓ Done!');
console.log('');
console.log('================================================================================');
console.log('Next Steps:');
console.log('================================================================================');
console.log('1. Review the generated paths in ' + outputFile);
console.log('2. Run integration tests with: php test-paths.php ' + basename(outputFile) + ' unauth');
console.log('3. Take screenshots with: node take-screenshots-with-tracking.js ' + basename(outputFile));
console.log('================================================================================');
