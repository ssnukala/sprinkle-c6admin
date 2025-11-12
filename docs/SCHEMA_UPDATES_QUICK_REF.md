# Schema Updates Quick Reference

## Boolean Toggle Switches

### Change Field Type
```json
{
  "flag_enabled": {
    "type": "boolean-tgl",  // Changed from "boolean"
    "label": "Enabled"
  }
}
```

**Result:** Modern toggle switch with "Enabled/Disabled" labels

## Pivot Table Management

### User → Roles (users.json)

```json
{
  "relationships": [{
    "name": "roles",
    "actions": {
      "on_create": {
        "attach": [{
          "related_id": 2,
          "pivot_data": {"created_at": "now", "updated_at": "now"}
        }]
      },
      "on_update": {"sync": "role_ids"},
      "on_delete": {"detach": "all"}
    }
  }],
  "fields": {
    "role_ids": {"type": "multiselect", "label": "Roles"}
  }
}
```

### Role → Permissions (roles.json)

```json
{
  "relationships": [{
    "name": "permissions",
    "actions": {
      "on_update": {"sync": "permission_ids"},
      "on_delete": {"detach": "all"}
    }
  }],
  "fields": {
    "permission_ids": {"type": "multiselect", "label": "Permissions"}
  }
}
```

### Permission → Roles (permissions.json)

```json
{
  "relationships": [{
    "name": "roles",
    "actions": {
      "on_update": {"sync": "role_ids"},
      "on_delete": {"detach": "all"}
    }
  }],
  "fields": {
    "role_ids": {"type": "multiselect", "label": "Roles"}
  }
}
```

## Action Types

| Action | Event | Purpose | Example |
|--------|-------|---------|---------|
| `attach` | on_create | Add default relationships | Assign default role to new user |
| `sync` | on_update | Sync from form field | Update user's roles from multiselect |
| `detach` | on_delete | Clean up before delete | Remove pivot entries before deleting |

## Special Values

| Value | Replaced With |
|-------|--------------|
| `"now"` | Current timestamp |
| `"current_user"` | Auth user ID |
| `"current_date"` | Today (Y-m-d) |

## Complete Example

```json
{
  "model": "users",
  "relationships": [{
    "name": "roles",
    "type": "many_to_many",
    "pivot_table": "role_users",
    "foreign_key": "user_id",
    "related_key": "role_id",
    "actions": {
      "on_create": {
        "attach": [{
          "related_id": 2,
          "description": "Assign default User role",
          "pivot_data": {
            "created_at": "now",
            "updated_at": "now"
          }
        }]
      },
      "on_update": {
        "sync": "role_ids",
        "description": "Sync user roles from form"
      },
      "on_delete": {
        "detach": "all",
        "description": "Remove all role associations"
      }
    }
  }],
  "fields": {
    "flag_enabled": {
      "type": "boolean-tgl",
      "label": "Enabled",
      "default": true
    },
    "role_ids": {
      "type": "multiselect",
      "label": "Roles",
      "editable": true
    }
  }
}
```

## See Full Documentation

📖 [docs/SCHEMA_UPDATES_TOGGLE_PIVOT.md](./SCHEMA_UPDATES_TOGGLE_PIVOT.md)
