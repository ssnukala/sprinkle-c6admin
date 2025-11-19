# Role and Permission Detail Page Errors - Analysis and Resolution

**Date:** 2025-11-19  
**Resolution Date:** 2025-11-19  
**Status:** ✅ **RESOLVED** - Implemented in [sprinkle-crud6 PR #187](https://github.com/ssnukala/sprinkle-crud6/pull/187)  
**GitHub Actions Run:** [#19486530369](https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19486530369/job/55769907883)  
**Issue:** PHP 500 errors on role and permission detail pages

---

## Resolution

The CRUD6 team has successfully implemented support for the `details` section feature in [PR #187](https://github.com/ssnukala/sprinkle-crud6/pull/187). The API endpoints now properly load relationship data as documented in the requirements below.

---

## Executive Summary

C6Admin detail pages for roles (`/c6/admin/roles/1`) and permissions (`/c6/admin/permissions/1`) were returning HTTP 500 errors. The root cause was that CRUD6 API endpoints (`/api/crud6/roles/{id}` and `/api/crud6/permissions/{id}`) did not support loading relationship data required by the `details` section in the JSON schemas.

**Impact:** ~~Users cannot view role or permission details, including associated users and permissions/roles.~~ **NOW RESOLVED**

---

## Error Details

### 1. Role Detail Page Error

**Browser Request:**
- **URL:** `http://localhost:8080/c6/admin/roles/1`
- **API Endpoint:** `GET /api/crud6/roles/1`
- **Timestamp:** 2025-11-19T01:34:06.1815063Z
- **HTTP Status:** 500 Internal Server Error

**Error Message from Logs:**
```
[Browser ERROR]: Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

**Frontend Composable:**
- File: `app/assets/composables/useRoleApi.ts`
- Line: 60
- Code: `axios.get<RoleResponse>('/api/crud6/roles/' + toValue(id))`

### 2. Permission Detail Page Error

**Browser Request:**
- **URL:** `http://localhost:8080/c6/admin/permissions/1`
- **API Endpoint:** `GET /api/crud6/permissions/1`
- **Timestamp:** 2025-11-19T01:34:14.1880631Z
- **HTTP Status:** 500 Internal Server Error

**Error Message from Logs:**
```
[Browser ERROR]: Failed to load resource: the server responded with a status of 500 (Internal Server Error)
```

**Frontend Composable:**
- File: `app/assets/composables/usePermissionApi.ts`
- Line: 41
- Code: `axios.get<PermissionResponse>('/api/crud6/permissions/' + toValue(id))`

---

## Root Cause Analysis

### Schema Configuration

Both schemas define `details` sections that require loading related model data:

#### Roles Schema (`app/schema/crud6/roles.json`)

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
    ],
    "relationships": [
        {
            "name": "permissions",
            "type": "many_to_many",
            "pivot_table": "permission_roles",
            "foreign_key": "role_id",
            "related_key": "permission_id"
        },
        {
            "name": "users",
            "type": "many_to_many",
            "pivot_table": "role_users",
            "foreign_key": "role_id",
            "related_key": "user_id"
        }
    ]
}
```

#### Permissions Schema (`app/schema/crud6/permissions.json`)

```json
{
    "details": [
        {
            "model": "users",
            "list_fields": ["user_name", "first_name", "last_name", "email"],
            "title": "PERMISSION.USERS"
        },
        {
            "model": "roles",
            "list_fields": ["name", "slug", "description"],
            "title": "ROLE.2"
        }
    ],
    "relationships": [
        {
            "name": "roles",
            "type": "many_to_many",
            "pivot_table": "permission_roles",
            "foreign_key": "permission_id",
            "related_key": "role_id"
        },
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
    ]
}
```

### Why It Fails

The CRUD6 detail endpoint (`GET /api/crud6/{model}/{id}`) is expected to:

1. Load the primary model record (role or permission)
2. Parse the `details` section from the schema
3. For each detail entry:
   - Query the related model using the relationship definition
   - Filter fields based on `list_fields`
   - Return the related data
4. Return a complete response with main data + detail sections

**Current State:** CRUD6 likely returns only the base model data without processing the `details` section or relationships, causing the frontend to fail or the backend to throw errors when attempting to load relationships.

---

## Expected API Response Format

### Role Detail Response (Expected)

```json
{
    "id": 1,
    "slug": "site-admin",
    "name": "Site Administrator",
    "description": "This role provides administrative access to the entire system.",
    "created_at": "2025-11-19 00:00:00",
    "updated_at": "2025-11-19 00:00:00",
    "details": {
        "users": {
            "title": "ROLE.USERS",
            "rows": [
                {
                    "id": 1,
                    "user_name": "admin",
                    "first_name": "Admin",
                    "last_name": "User",
                    "email": "admin@example.com",
                    "flag_enabled": 1
                }
            ],
            "count": 1
        },
        "permissions": {
            "title": "ROLE.PERMISSIONS",
            "rows": [
                {
                    "id": 1,
                    "slug": "uri_users",
                    "name": "View user list",
                    "description": "View a list of all users in the system."
                },
                {
                    "id": 2,
                    "slug": "create_user",
                    "name": "Create user",
                    "description": "Create a new user."
                }
            ],
            "count": 2
        }
    }
}
```

### Permission Detail Response (Expected)

```json
{
    "id": 1,
    "slug": "uri_users",
    "name": "View user list",
    "conditions": "always()",
    "description": "View a list of all users in the system.",
    "created_at": "2025-11-19 00:00:00",
    "updated_at": "2025-11-19 00:00:00",
    "details": {
        "users": {
            "title": "PERMISSION.USERS",
            "rows": [
                {
                    "id": 1,
                    "user_name": "admin",
                    "first_name": "Admin",
                    "last_name": "User",
                    "email": "admin@example.com"
                }
            ],
            "count": 1
        },
        "roles": {
            "title": "ROLE.2",
            "rows": [
                {
                    "id": 1,
                    "name": "Site Administrator",
                    "slug": "site-admin",
                    "description": "This role provides administrative access to the entire system."
                }
            ],
            "count": 1
        }
    }
}
```

---

## Required CRUD6 Changes

### 1. Detail Endpoint Enhancement

**Endpoint:** `GET /api/crud6/{model}/{id}`

**Required Implementation:**
- Load main model record by ID
- Load and parse schema for the model
- Process `details` section if present
- For each detail entry:
  - Load relationship data
  - Apply field filtering
  - Format response
- Return complete detail response

**Pseudocode:**
```php
public function getDetail($model, $id) {
    // Load schema
    $schema = $this->schemaLoader->load($model);
    
    // Load main record
    $record = Model::find($id);
    
    // Process details section
    $details = [];
    if (isset($schema['details'])) {
        foreach ($schema['details'] as $detail) {
            $relationName = $detail['model'];
            $listFields = $detail['list_fields'];
            $title = $detail['title'];
            
            // Load relationship data
            $relatedData = $record->$relationName()
                ->select($listFields)
                ->get();
            
            $details[$relationName] = [
                'title' => $title,
                'rows' => $relatedData,
                'count' => $relatedData->count()
            ];
        }
    }
    
    // Return combined response
    return [
        ...$record->toArray(),
        'details' => $details
    ];
}
```

### 2. Relationship Query Support

**Many-to-Many (e.g., roles ↔ permissions):**
```php
// Schema definition
{
    "name": "permissions",
    "type": "many_to_many",
    "pivot_table": "permission_roles",
    "foreign_key": "role_id",
    "related_key": "permission_id"
}

// Query implementation
$role->permissions()->select($listFields)->get();
```

**Belongs to Many Through (e.g., permissions → users through roles):**
```php
// Schema definition
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

// Query implementation (using hasManyThrough or custom query)
$permission->users()->select($listFields)->get();
```

### 3. Schema Validation

**Validate `details` Section:**
- Ensure referenced models exist
- Validate relationship definitions
- Check that list_fields are valid for the related model
- Verify relationship types are supported

### 4. Error Handling

**Scenarios to Handle:**
- Missing relationship definition
- Invalid relationship type
- Invalid list_fields
- Related model not found
- Relationship not defined on Eloquent model

**Error Response Format:**
```json
{
    "title": "Error Loading Details",
    "description": "Unable to load relationship 'users' for model 'roles'",
    "status": 500
}
```

---

## Additional API Endpoints Used by C6Admin

### Role Permissions Endpoint

**Endpoint:** `GET /api/crud6/roles/{id}/permissions`

**Used By:** `app/assets/composables/useRolePermissionsApi.ts` (Line 42)

**Purpose:** Load all permissions associated with a specific role

**Expected Response:**
```json
{
    "rows": [
        {
            "id": 1,
            "slug": "uri_users",
            "name": "View user list",
            "description": "View a list of all users in the system."
        }
    ],
    "count": 1,
    "count_filtered": 1
}
```

**Implementation Notes:**
- This endpoint should return role permissions in Sprunje format
- Must support pagination (size, page)
- Must support sorting and filtering
- Should use the role's permission relationship

---

## Testing Requirements

### Unit Tests

1. **Schema Processing Tests:**
   - Test parsing of `details` section
   - Test validation of relationship definitions
   - Test field filtering

2. **Relationship Query Tests:**
   - Test many_to_many relationships
   - Test belongs_to_many_through relationships
   - Test field selection
   - Test empty relationships

3. **Error Handling Tests:**
   - Test missing relationship
   - Test invalid model reference
   - Test invalid field names

### Integration Tests

1. **Role Detail Endpoint:**
   - Test `GET /api/crud6/roles/1`
   - Verify main data loaded
   - Verify users detail loaded
   - Verify permissions detail loaded

2. **Permission Detail Endpoint:**
   - Test `GET /api/crud6/permissions/1`
   - Verify main data loaded
   - Verify users detail loaded (through roles)
   - Verify roles detail loaded

3. **Role Permissions Endpoint:**
   - Test `GET /api/crud6/roles/1/permissions`
   - Verify Sprunje format
   - Test pagination
   - Test sorting

---

## Migration Path

### Phase 1: Basic Detail Support
- Implement detail endpoint with relationship loading
- Support many_to_many relationships
- Basic field filtering

### Phase 2: Advanced Relationships
- Support belongs_to_many_through
- Support other relationship types (has_many, belongs_to, etc.)
- Advanced filtering and transformation

### Phase 3: Optimization
- Eager loading optimization
- Caching strategies
- Query performance tuning

---

## Summary for sprinkle-crud6

### Critical Issues to Fix

1. **Detail Endpoint Missing Relationship Support**
   - `GET /api/crud6/roles/{id}` returns 500 error
   - `GET /api/crud6/permissions/{id}` returns 500 error
   - Need to process `details` section from schema
   - Need to load and format relationship data

2. **Relationship Query Implementation**
   - Support many_to_many (roles ↔ permissions, roles ↔ users)
   - Support belongs_to_many_through (permissions → users through roles)
   - Apply field filtering based on list_fields
   - Return formatted detail sections

3. **Additional Endpoint Required**
   - `GET /api/crud6/roles/{id}/permissions` (Sprunje format)
   - Load role's permissions with pagination/filtering

### Schema Enhancement Requirements

The schema format used by C6Admin includes these sections that CRUD6 must support:

1. **relationships** - Define how models relate to each other
2. **details** - Define which relationships to load on detail pages
3. **list_fields** - Define which fields to include in relationship data

### Deliverables for sprinkle-crud6

1. Implementation of detail endpoint with relationship support
2. Relationship query handler supporting:
   - many_to_many
   - belongs_to_many_through
   - (future: has_many, belongs_to, has_one, etc.)
3. Schema parser for details section
4. Field filtering based on list_fields
5. Relationship-specific endpoints (e.g., /roles/{id}/permissions)
6. Comprehensive error handling
7. Unit and integration tests
8. Documentation and examples

---

## References

- **GitHub Actions Run:** https://github.com/ssnukala/sprinkle-c6admin/actions/runs/19486530369/job/55769907883
- **Role Schema:** `app/schema/crud6/roles.json`
- **Permission Schema:** `app/schema/crud6/permissions.json`
- **Role API Composable:** `app/assets/composables/useRoleApi.ts`
- **Permission API Composable:** `app/assets/composables/usePermissionApi.ts`
- **Role Permissions API Composable:** `app/assets/composables/useRolePermissionsApi.ts`
