# Multiselect Data Source Configuration - Summary

## Question
**"Where is the multiselect data coming for role_ids?"**

## Answer

The multiselect fields get their data from CRUD6 API endpoints by querying the model specified in the `lookup_model` property.

## Configuration

Each multiselect field requires three properties to specify its data source:

```json
{
  "role_ids": {
    "type": "multiselect",
    "lookup_model": "roles",     // Which model to query
    "lookup_id": "id",            // Which field to use as the value
    "lookup_desc": "name"         // Which field to display to the user
  }
}
```

## How It Works

### 1. API Call
When the form loads, the multiselect field makes an API call:
```
GET /api/crud6/roles
```

### 2. Response
The API returns all available records:
```json
{
  "rows": [
    {"id": 1, "name": "Admin"},
    {"id": 2, "name": "User"},
    {"id": 3, "name": "Guest"}
  ]
}
```

### 3. Display
The multiselect dropdown shows the `lookup_desc` field:
- Admin
- User  
- Guest

### 4. Storage
When the user selects options, the `lookup_id` values are stored:
- Selects "Admin" and "User" → Stores `[1, 2]`

### 5. Sync to Pivot Table
On update, the `on_update.sync` action uses these IDs to sync the pivot table:
```json
{
  "on_update": {
    "sync": "role_ids"  // Syncs the selected [1, 2] to role_users table
  }
}
```

## Complete Example

### users.json
```json
{
  "relationships": [{
    "name": "roles",
    "type": "many_to_many",
    "pivot_table": "role_users",
    "foreign_key": "user_id",
    "related_key": "role_id",
    "actions": {
      "on_update": {
        "sync": "role_ids"  // ← Syncs from the multiselect field
      }
    }
  }],
  "fields": {
    "role_ids": {
      "type": "multiselect",
      "label": "Roles",
      "lookup_model": "roles",    // ← Data comes from roles model
      "lookup_id": "id",           // ← Stores role IDs
      "lookup_desc": "name",       // ← Displays role names
      "editable": true
    }
  }
}
```

## Data Flow Diagram

```
1. Form Loads
   ↓
2. Multiselect queries: GET /api/crud6/roles
   ↓
3. API returns: [{id: 1, name: "Admin"}, {id: 2, name: "User"}]
   ↓
4. Multiselect displays: ["Admin", "User"]
   ↓
5. User selects: "Admin" and "User"
   ↓
6. Multiselect stores: [1, 2] in role_ids field
   ↓
7. User clicks Save
   ↓
8. on_update.sync action reads role_ids: [1, 2]
   ↓
9. Eloquent sync() updates role_users table
   ↓
10. Pivot table now has:
    - user_id: 123, role_id: 1
    - user_id: 123, role_id: 2
```

## All Multiselect Fields in C6Admin

### users.json → role_ids
```json
{
  "lookup_model": "roles",
  "lookup_id": "id",
  "lookup_desc": "name"
}
```
**Data Source:** `/api/crud6/roles`

### roles.json → permission_ids
```json
{
  "lookup_model": "permissions",
  "lookup_id": "id",
  "lookup_desc": "name"
}
```
**Data Source:** `/api/crud6/permissions`

### permissions.json → role_ids
```json
{
  "lookup_model": "roles",
  "lookup_id": "id",
  "lookup_desc": "name"
}
```
**Data Source:** `/api/crud6/roles`

## Key Properties

| Property | Purpose | Example |
|----------|---------|---------|
| `lookup_model` | Which model to query for data | `"roles"` |
| `lookup_id` | Which field to use as value | `"id"` |
| `lookup_desc` | Which field to display | `"name"` |

## Common Patterns

### Pattern 1: Basic Multiselect
```json
{
  "field_name_ids": {
    "type": "multiselect",
    "lookup_model": "related_model",
    "lookup_id": "id",
    "lookup_desc": "name"
  }
}
```

### Pattern 2: With Relationship Sync
```json
{
  "relationships": [{
    "name": "related_items",
    "actions": {
      "on_update": {
        "sync": "field_name_ids"  // ← Matches the field name
      }
    }
  }],
  "fields": {
    "field_name_ids": {  // ← Field name
      "type": "multiselect",
      "lookup_model": "related_model"
    }
  }
}
```

### Pattern 3: Multiple Display Fields
```json
{
  "customer_id": {
    "type": "multiselect",
    "lookup_model": "customers",
    "lookup_id": "id",
    "lookup_desc": "name",
    "display_fields": ["name", "email", "phone"]  // Shows: "John - john@example.com - 555-1234"
  }
}
```

## Troubleshooting

### Problem: Multiselect shows no options

**Check:**
1. Is `lookup_model` correct? (should match an existing CRUD6 model)
2. Does `/api/crud6/{lookup_model}` endpoint work?
3. Does the model have records in the database?
4. Is `lookup_desc` field present in the model schema?

### Problem: Wrong values stored

**Check:**
1. Is `lookup_id` correct? (usually "id")
2. Does the lookup model have the specified ID field?

### Problem: Sync not working

**Check:**
1. Does field name match the `sync` value?
   - Field: `role_ids`
   - Sync: `"sync": "role_ids"` ✅
   - Mismatch: `"sync": "roles"` ❌

## See Also

- [CRUD6 SmartLookup Documentation](https://github.com/ssnukala/sprinkle-crud6/blob/main/docs/SMARTLOOKUP_FIELD_TYPE.md)
- [Relationship Actions Guide](./SCHEMA_UPDATES_TOGGLE_PIVOT.md)
- [Quick Reference](./SCHEMA_UPDATES_QUICK_REF.md)
