# Sprunje Analysis and CRUD6 Recommendations

## Overview

This document analyzes the Sprunje classes from sprinkle-admin and determines which should be kept in c6admin vs which should be handled by CRUD6 generically.

## Current Sprunje Classes

### Removed (Should be handled by CRUD6)

1. **UserSprunje**
   - Basic user listing with pagination
   - Filters: name (first_name/last_name/email), status (active/disabled/unactivated), last_activity
   - Sorting: name (last_name), status, last_activity
   - **Special features:**
     - Joins last activity: `joinLastActivity()`
     - Status filter combines `flag_enabled` + `flag_verified`
     - Name filter searches across first_name, last_name, email

2. **GroupSprunje**
   - Basic group listing
   - Filters: name, description
   - Sorting: name, description, users_count
   - **Special features:**
     - Includes user count: `withCount('users')`

3. **RoleSprunje**
   - Basic role listing
   - Filters: name, description, info (name OR description)
   - Sorting: name, description
   - No special features

4. **PermissionSprunje**
   - Basic permission listing
   - Filters: name, properties (slug/conditions/description), info
   - Sorting: name, properties
   - **Special features:**
     - Properties filter searches slug, conditions, description
     - Properties sort uses slug

5. **ActivitySprunje**
   - Activity log listing
   - Filters: occurred_at, user (first/last/email), description
   - Sorting: occurred_at, user (last_name), description
   - **Special features:**
     - Joins user table: `joinUser()`
     - User filter searches across user fields

### Kept (Domain-specific to admin)

1. **UserPermissionSprunje**
   - Lists permissions for a specific user
   - Filters: name, properties (slug/conditions/description), info
   - Sorting: name, properties
   - **Special features:**
     - Uses `permissions()->withVia('roles_via')` to show which roles grant the permission
     - **Domain-specific:** Admin UI needs to show role via information

2. **PermissionUserSprunje**
   - Lists users for a specific permission
   - Filters: name (first/last/email), status
   - Sorting: name, status
   - **Special features:**
     - Uses `users()->withVia('roles_via')` to show which roles connect users
     - Status filter (active/disabled/unactivated)
     - **Domain-specific:** Admin UI needs to show role via information

## Recommendations for CRUD6

### 1. Generic Sprunje Support (High Priority)

**Current Gap:** CRUD6 doesn't provide Sprunje-style pagination/filtering/sorting for models.

**Recommendation:**
- Add a generic `SprunjeAction` controller in CRUD6
- Endpoint: `GET /api/crud6/{model}/sprunje`
- Or enhance existing `GET /api/crud6/{model}` to support Sprunje query parameters
- Use schema to automatically determine:
  - Filterable fields (all fields by default, configurable in schema)
  - Sortable fields (all fields by default, configurable in schema)
  - Listable fields (for dropdown filters)

**Schema Configuration Example:**
```json
{
  "sprunje": {
    "filterable": ["name", "email", "status"],
    "sortable": ["name", "created_at", "status"],
    "listable": {
      "status": {
        "values": ["active", "disabled", "unactivated"]
      }
    }
  }
}
```

**Benefits:**
- Eliminates need for basic Sprunje classes like UserSprunje, GroupSprunje, etc.
- Reduces code duplication
- Maintains consistency across all models

### 2. Schema-Based Join Configuration (Medium Priority)

**Current Gap:** Custom joins require custom Sprunje classes (joinLastActivity, joinUser, withCount).

**Recommendation:**
- Allow schemas to define common joins/relationships to include
- Support count relationships

**Schema Configuration Example:**
```json
{
  "sprunje": {
    "joins": [
      {
        "type": "last_activity",
        "method": "joinLastActivity"
      }
    ],
    "counts": ["users", "roles"]
  }
}
```

**Benefits:**
- ActivitySprunje and UserSprunje can be eliminated
- Relationships automatically included in base query
- Configurable per-model via schema

### 3. Composite Filter Support (Medium Priority)

**Current Gap:** Multi-field filters require custom Sprunje methods (name filter, status filter).

**Recommendation:**
- Support composite filters in schema configuration
- Support computed filters (combining multiple fields)

**Schema Configuration Example:**
```json
{
  "sprunje": {
    "filters": {
      "name": {
        "type": "composite",
        "operator": "OR",
        "fields": ["first_name", "last_name", "email"]
      },
      "status": {
        "type": "computed",
        "values": {
          "active": {"flag_enabled": 1, "flag_verified": 1},
          "disabled": {"flag_enabled": 0},
          "unactivated": {"flag_verified": 0}
        }
      },
      "properties": {
        "type": "composite",
        "operator": "OR",
        "fields": ["slug", "conditions", "description"]
      }
    }
  }
}
```

**Benefits:**
- UserSprunje name filter becomes schema config
- UserSprunje/PermissionUserSprunje status filter becomes schema config
- PermissionSprunje properties filter becomes schema config

### 4. Relationship Sprunje Support (Low Priority)

**Current Gap:** Sprunjes for relationships with via info (UserPermissionSprunje, PermissionUserSprunje).

**Recommendation:**
- Keep these in c6admin as domain-specific
- Alternatively, CRUD6 could provide a generic relationship Sprunje
- Endpoint: `GET /api/crud6/{model}/{id}/{relation}/sprunje`
- Support `withVia()` parameter to include intermediate relationship info

**Benefits:**
- Could eliminate UserPermissionSprunje and PermissionUserSprunje
- Provides generic relationship listing with via info
- Still needs testing to ensure UI compatibility

## Implementation Priority

### Phase 1: High Priority (Required for c6admin)
1. **Generic Sprunje Support**
   - Add SprunjeAction to CRUD6
   - Schema-based filterable/sortable configuration
   - Would eliminate 5 basic Sprunje classes from c6admin

### Phase 2: Medium Priority (Nice to have)
2. **Schema-Based Joins**
   - Reduce need for custom joins
   - Works with existing models
3. **Composite Filters**
   - Powerful filtering without custom code
   - Configurable per-model

### Phase 3: Low Priority (Future enhancement)
4. **Relationship Sprunjes**
   - Generic relationship listing
   - Via info support
   - Would eliminate last 2 Sprunjes from c6admin

## Current c6admin State

**Removed Sprunjes (handled by CRUD6):**
- UserSprunje (awaiting CRUD6 generic support)
- GroupSprunje (awaiting CRUD6 generic support)
- RoleSprunje (awaiting CRUD6 generic support)
- PermissionSprunje (awaiting CRUD6 generic support)
- ActivitySprunje (awaiting CRUD6 generic support)

**Kept Sprunjes (admin-specific):**
- UserPermissionSprunje (shows role via info for permissions of a user)
- PermissionUserSprunje (shows role via info for users of a permission)

**Frontend Impact:**
- Frontend currently calls `/api/crud6/{model}` for listing
- May need to switch to `/api/crud6/{model}/sprunje` once CRUD6 adds this
- Or CRUD6's existing endpoint could support Sprunje parameters

## Recommended CRUD6 Issues to Create

1. **Issue: Add Generic Sprunje Support**
   - Title: "Add generic Sprunje endpoint for all models"
   - Description: Reference this analysis, Phase 1 recommendations
   - Labels: enhancement, high-priority

2. **Issue: Schema-Based Join Configuration**
   - Title: "Support joins and relationship counts via schema config"
   - Description: Reference this analysis, Phase 2 recommendations
   - Labels: enhancement, medium-priority

3. **Issue: Composite Filter Support**
   - Title: "Add support for composite and computed filters in schemas"
   - Description: Reference this analysis, Phase 2 recommendations
   - Labels: enhancement, medium-priority

4. **Issue: Relationship Sprunje Endpoint**
   - Title: "Add generic relationship Sprunje with via info support"
   - Description: Reference this analysis, Phase 3 recommendations
   - Labels: enhancement, low-priority

## Conclusion

By implementing these recommendations in CRUD6, we can:
- Eliminate all 5 basic Sprunje classes from c6admin
- Potentially eliminate the 2 relationship Sprunjes
- Provide a more powerful, configurable Sprunje system
- Maintain separation: CRUD6 handles generic operations, c6admin provides admin-specific schemas and UI

The current approach (removing redundant Sprunjes, keeping only admin-specific ones) aligns with the architecture principle that c6admin should provide domain-specific features while CRUD6 handles generic CRUD operations.
