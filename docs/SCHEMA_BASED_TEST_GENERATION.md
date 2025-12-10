# Schema-Based Test Path Generation

## Overview

C6Admin integration tests use **schema-based test path generation** to automatically create API and frontend test paths from CRUD6 JSON schemas. This ensures that any updates to schemas are automatically reflected in integration tests.

## How It Works

The `generate-test-paths-from-schemas.js` script:

1. **Reads schemas** from `app/schema/crud6/`
2. **Analyzes each schema** to extract:
   - Model name and permissions
   - Actions (field updates, custom actions)
   - Relationships (many-to-many, etc.)
3. **Generates test paths** for:
   - Unauthenticated API endpoints (should return 401)
   - Authenticated API endpoints (CRUD operations)
   - Frontend pages (list and detail views)

## Generated Paths

### Unauthenticated API Paths

For each model, generates:
- `GET /api/crud6/{model}/schema` → 401
- `GET /api/crud6/{model}` → 401
- `GET /api/crud6/{model}/{id}` → 401
- `POST /api/crud6/{model}` → 401 (for users only)
- `PUT /api/crud6/{model}/{id}` → 401 (for users only)
- `DELETE /api/crud6/{model}/{id}` → 401 (for users only)

### Authenticated API Paths

For each model with appropriate permissions, generates:
- `GET /api/crud6/{model}/schema` → 200
- `GET /api/crud6/{model}` → 200 (list)
- `GET /api/crud6/{model}/{id}` → 200 (single)
- `POST /api/crud6/{model}` → 201 (create)
- `PUT /api/crud6/{model}/{id}` → 200 (update)
- `DELETE /api/crud6/{model}/{id}` → 200 (delete)

### Action-Based Paths

From schema `actions` array:
- **Field updates**: `PUT /api/crud6/{model}/{id}/{field}`
- **Custom actions**: `POST /api/crud6/{model}/{id}/a/{actionKey}`

### Relationship Paths

From schema `relationships` array:
- **Attach**: `POST /api/crud6/{model}/{id}/{relation}`
- **Detach**: `DELETE /api/crud6/{model}/{id}/{relation}`

### Frontend Paths

For each model:
- List page: `/c6/admin/{model}`
- Detail page: `/c6/admin/{model}/{id}`

Plus C6Admin-specific pages:
- Dashboard: `/c6/admin/dashboard`
- Config: `/c6/admin/config`

## Usage

### Manual Generation

```bash
node .github/scripts/generate-test-paths-from-schemas.js \
  app/schema/crud6 \
  .github/config/integration-test-paths.json
```

### Automatic Generation (CI)

The integration test workflow automatically regenerates paths before running tests:

```yaml
- name: Generate test paths from schemas
  run: |
    cd userfrosting
    node ../sprinkle-c6admin/.github/scripts/generate-test-paths-from-schemas.js \
      vendor/ssnukala/sprinkle-c6admin/app/schema/crud6 \
      integration-test-paths.json
```

## Example Schema Processing

Given this schema snippet:

```json
{
  "model": "users",
  "permissions": {
    "read": "uri_users",
    "create": "create_user",
    "update": "update_user_field",
    "delete": "delete_user"
  },
  "actions": [
    {
      "key": "toggle_enabled",
      "type": "field_update",
      "field": "flag_enabled",
      "permission": "update_user_field"
    }
  ],
  "relationships": [
    {
      "name": "roles",
      "type": "many_to_many"
    }
  ]
}
```

The generator creates:

**Unauthenticated:**
- `users_schema`, `users_list`, `users_single`, `users_create`, `users_update`, `users_delete`

**Authenticated:**
- All CRUD operations (schema, list, single, create, update, delete)
- Field update: `users_update_flag_enabled`
- Relationships: `users_relationship_attach_roles`, `users_relationship_detach_roles`

## Benefits

✅ **Automatic Updates** - Schema changes automatically generate new test paths
✅ **Complete Coverage** - All endpoints from schemas are tested
✅ **Consistent** - Same generation logic for all models
✅ **Maintainable** - Update schema, not test config
✅ **Self-Documenting** - Generated paths include descriptions from schemas

## Customization

To add custom paths not in schemas, edit the generator script:

```javascript
// Add custom C6Admin paths
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
```

## Statistics

After generation, you'll see:

```
Path Generation Summary
================================================================================
Unauthenticated API paths: 19
Authenticated API paths: 43
Authenticated frontend paths: 12
Unauthenticated frontend paths: 1
```

This means:
- **19 unauthenticated tests** - All should return 401/403
- **43 authenticated tests** - All should return 200/201
- **12 frontend pages** - All should load successfully
- **1 redirect test** - Should redirect to login when unauthenticated

## Troubleshooting

**Missing paths?**
- Check that schema has the `permissions` object defined
- Verify `actions` and `relationships` arrays in schema
- Run generator with verbose output

**Wrong path format?**
- Check schema `model` name matches CRUD6 conventions
- Verify relationship names match Eloquent conventions

**Validation errors?**
- Ensure generated JSON is valid: `jq '.' integration-test-paths.json`
- Check that all required fields are present in schema
