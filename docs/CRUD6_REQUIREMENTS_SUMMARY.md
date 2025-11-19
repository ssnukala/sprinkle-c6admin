# CRUD6 Requirements Summary for C6Admin Detail Pages

**Date:** 2025-11-19  
**Related Issue:** Role and Permission detail pages returning 500 errors  
**Full Analysis:** See [ROLE_PERMISSION_DETAIL_ERRORS.md](./ROLE_PERMISSION_DETAIL_ERRORS.md)

---

## Quick Summary

C6Admin detail pages fail because CRUD6 doesn't support loading relationship data defined in the schema's `details` section. The frontend expects detail responses to include related model data, but CRUD6 currently returns only the base model.

**Failed Endpoints:**
- `GET /api/crud6/roles/1` → 500 error
- `GET /api/crud6/permissions/1` → 500 error

---

## Required CRUD6 Changes

### 1. Detail Endpoint Enhancement

**Current:** `GET /api/crud6/{model}/{id}` returns only base model data

**Required:** Return base model data + related data from `details` section

**Example Response Structure:**
```json
{
    "id": 1,
    "slug": "site-admin",
    "name": "Site Administrator",
    "description": "...",
    "created_at": "...",
    "updated_at": "...",
    "details": {
        "users": {
            "title": "ROLE.USERS",
            "rows": [...],
            "count": 5
        },
        "permissions": {
            "title": "ROLE.PERMISSIONS",
            "rows": [...],
            "count": 10
        }
    }
}
```

### 2. Schema Details Section Support

**Schema Structure (roles.json):**
```json
{
    "details": [
        {
            "model": "users",
            "list_fields": ["user_name", "first_name", "last_name", "email", "flag_enabled"],
            "title": "ROLE.USERS"
        },
        {
            "model": "permissions",
            "list_fields": ["slug", "name", "description"],
            "title": "ROLE.PERMISSIONS"
        }
    ]
}
```

**Processing Required:**
1. Parse `details` array from schema
2. For each detail entry:
   - Query the relationship (using relationship name = model name)
   - Select only fields specified in `list_fields`
   - Return as array under detail model name
3. Include title and count for each detail section

### 3. Relationship Type Support

**Many-to-Many (roles ↔ permissions, roles ↔ users):**
```json
{
    "name": "permissions",
    "type": "many_to_many",
    "pivot_table": "permission_roles",
    "foreign_key": "role_id",
    "related_key": "permission_id"
}
```

**Belongs to Many Through (permissions → users through roles):**
```json
{
    "name": "users",
    "type": "belongs_to_many_through",
    "through": "roles",
    "first_pivot_table": "permission_roles",
    "first_foreign_key": "permission_id",
    "first_related_key": "role_id",
    "second_pivot_table": "role_users",
    "second_foreign_key": "role_id",
    "second_related_key": "user_id"
}
```

### 4. Additional Endpoint

**Endpoint:** `GET /api/crud6/roles/{id}/permissions`

**Purpose:** Get all permissions for a specific role (used by permission assignment UI)

**Response Format:** Sprunje format with pagination
```json
{
    "rows": [
        {
            "id": 1,
            "slug": "uri_users",
            "name": "View user list",
            "description": "..."
        }
    ],
    "count": 10,
    "count_filtered": 10
}
```

---

## Implementation Checklist

### Core Functionality
- [ ] Parse `details` section from schema
- [ ] Query relationships defined in `details`
- [ ] Apply `list_fields` filtering
- [ ] Format response with `details` object
- [ ] Support many_to_many relationships
- [ ] Support belongs_to_many_through relationships

### Relationship Endpoints
- [ ] Implement `GET /api/crud6/{model}/{id}/{relationship}` pattern
- [ ] Return Sprunje-formatted responses
- [ ] Support pagination, sorting, filtering

### Error Handling
- [ ] Handle missing relationship definitions
- [ ] Handle invalid model references
- [ ] Handle invalid field names in list_fields
- [ ] Provide clear error messages

### Testing
- [ ] Unit tests for schema parsing
- [ ] Unit tests for relationship queries
- [ ] Integration tests for role detail endpoint
- [ ] Integration tests for permission detail endpoint
- [ ] Integration tests for role permissions endpoint

---

## Schema Files Reference

C6Admin provides these schemas with `details` sections:

1. **roles.json** - Details: users, permissions
2. **permissions.json** - Details: users (through roles), roles
3. **groups.json** - Details: users (working)
4. **users.json** - Details: groups, roles, permissions (working)

**Note:** Groups and users detail pages work correctly, suggesting basic relationship support exists. The issue is specific to roles and permissions, likely due to the complex relationship types or the `belongs_to_many_through` implementation.

---

## Expected Timeline

### Phase 1 (Immediate - Fix 500 Errors)
- Implement basic detail endpoint support
- Add many_to_many relationship queries
- Fix role and permission detail pages

### Phase 2 (Short-term)
- Add belongs_to_many_through support
- Implement relationship endpoints
- Add comprehensive error handling

### Phase 3 (Future)
- Performance optimization (eager loading)
- Support for additional relationship types
- Advanced querying and filtering

---

## Testing Criteria

A successful implementation will:

1. ✅ `GET /api/crud6/roles/1` returns 200 with details
2. ✅ `GET /api/crud6/permissions/1` returns 200 with details
3. ✅ Detail sections include correct related data
4. ✅ Field filtering works (only list_fields returned)
5. ✅ Relationship counts are accurate
6. ✅ `GET /api/crud6/roles/1/permissions` returns paginated data
7. ✅ Screenshots show working detail pages
8. ✅ No console errors in browser

---

## Contact & Questions

For questions about C6Admin's requirements or schema structure, please refer to:
- Full analysis: [ROLE_PERMISSION_DETAIL_ERRORS.md](./ROLE_PERMISSION_DETAIL_ERRORS.md)
- Schema files: `app/schema/crud6/roles.json`, `app/schema/crud6/permissions.json`
- Composables: `app/assets/composables/useRoleApi.ts`, `app/assets/composables/usePermissionApi.ts`
- GitHub Actions log: https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19486530369/job/55769907883
