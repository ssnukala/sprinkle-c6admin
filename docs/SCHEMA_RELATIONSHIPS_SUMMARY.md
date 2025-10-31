# C6Admin Schema Relationships Summary

## Overview of Implemented Relationships

This table shows all detail relationships and many-to-many relationships configured in C6Admin schemas:

| Model | Relationship Type | Related Model | Description | Translation Key |
|-------|------------------|---------------|-------------|-----------------|
| **users** | many_to_many | roles | User's assigned roles | ROLE.2 |
| **users** | detail (1-to-many) | activities | User's activity log | ACTIVITY.2 |
| **groups** | detail (1-to-many) | users | Users in this group | GROUP.USERS |
| **roles** | many_to_many | permissions | Permissions for this role | ROLE.PERMISSIONS |
| **roles** | many_to_many | users | Users with this role | ROLE.USERS |
| **permissions** | many_to_many | roles | Roles with this permission | ROLE.2 |
| **activities** | none | - | Read-only log entries | - |

## Database Relationships

### Many-to-Many Pivot Tables
- `role_user`: Links users ↔ roles
- `permission_role`: Links permissions ↔ roles

### One-to-Many Foreign Keys
- `users.group_id` → `groups.id`: User belongs to one group
- `activities.user_id` → `users.id`: Activities belong to one user

## Detail View Features by Model

### Users Detail Page (`/c6/admin/users/{id}`)
Shows:
- ✓ User information (name, email, group, etc.)
- ✓ Roles section (many-to-many)
- ✓ Activities section (one-to-many audit log)
- ✓ Change password button (via `/api/c6/users/{id}/password-reset`)

### Groups Detail Page (`/c6/admin/groups/{id}`)
Shows:
- ✓ Group information (name, description, icon)
- ✓ Users in this group section (one-to-many)

### Roles Detail Page (`/c6/admin/roles/{id}`)
Shows:
- ✓ Role information (name, description)
- ✓ Permissions section (many-to-many)
- ✓ Users section (many-to-many)

### Permissions Detail Page (`/c6/admin/permissions/{id}`)
Shows:
- ✓ Permission information (slug, name, conditions)
- ✓ Roles section (many-to-many)

### Activities Detail Page (`/c6/admin/activities/{id}`)
Shows:
- ✓ Activity information (type, description, timestamp, IP)
- (No relationships - read-only log entry)

## Equivalence with sprinkle-admin

All key features from userfrosting/sprinkle-admin are now replicated:

| Feature | sprinkle-admin | C6Admin |
|---------|----------------|---------|
| View user's roles | ✓ | ✓ |
| View user's activities | ✓ | ✓ |
| View user's group | ✓ | ✓ (via group_id field) |
| Change user password | ✓ | ✓ |
| View group's users | ✓ | ✓ |
| View role's permissions | ✓ | ✓ |
| View role's users | ✓ | ✓ |
| View permission's roles | ✓ | ✓ |

## Implementation Notes

1. **CRUD6 handles rendering**: All relationship sections are automatically rendered by CRUD6's `CRUD6MasterDetailPage` component
2. **Schema-driven**: No custom Vue components needed - everything configured in JSON schemas
3. **API endpoints**: CRUD6 automatically provides endpoints for managing relationships
4. **Translation keys**: All section titles use existing locale keys from UserFrosting
5. **No breaking changes**: All changes are additive - existing functionality unchanged

## How Relationships are Configured

### Many-to-Many Relationships

Use the `relationships` array in schema:

```json
{
  "model": "users",
  "relationships": [
    {
      "name": "roles",
      "type": "many_to_many",
      "pivot_table": "role_user",
      "foreign_key": "user_id",
      "related_key": "role_id",
      "title": "ROLE.2"
    }
  ]
}
```

### One-to-Many Details

Use the `detail` object in schema:

```json
{
  "model": "groups",
  "detail": {
    "model": "users",
    "foreign_key": "group_id",
    "list_fields": ["user_name", "first_name", "last_name", "email"],
    "title": "GROUP.USERS"
  }
}
```

## API Endpoints for Relationships

CRUD6 automatically provides these endpoints:

### Read Relationships
- `GET /api/crud6/users/{id}/roles` - Get user's roles
- `GET /api/crud6/roles/{id}/permissions` - Get role's permissions
- `GET /api/crud6/roles/{id}/users` - Get users with role
- `GET /api/crud6/permissions/{id}/roles` - Get roles with permission
- `GET /api/crud6/groups/{id}/users` - Get users in group (via foreign key)
- `GET /api/crud6/users/{id}/activities` - Get user's activities (via foreign key)

### Manage Many-to-Many (Attach/Detach)
- `POST /api/crud6/users/{id}/roles` - Attach roles to user
  ```json
  { "ids": [1, 2, 3] }
  ```
- `DELETE /api/crud6/users/{id}/roles` - Detach roles from user
  ```json
  { "ids": [2] }
  ```

## References

- [DETAIL_RELATIONSHIPS_IMPLEMENTATION.md](./DETAIL_RELATIONSHIPS_IMPLEMENTATION.md) - Detailed implementation guide
- [CRUD6 README](../node_modules/@ssnukala/sprinkle-crud6/README.md) - Full CRUD6 documentation
- [CRUD6_MIGRATION.md](./CRUD6_MIGRATION.md) - How C6Admin uses CRUD6 templates
