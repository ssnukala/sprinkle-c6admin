# Schema Examples - CRUD6 PR#146 Features

This document provides quick reference examples of how to use the new CRUD6 PR#146 features in C6Admin schemas.

## Table of Contents
- [Multiple Detail Sections](#multiple-detail-sections)
- [Custom Action Buttons](#custom-action-buttons)
- [i18n Support](#i18n-support)
- [Complete Examples](#complete-examples)

## Multiple Detail Sections

Display multiple related tables on a single detail page.

### Basic Example

```json
{
  "model": "users",
  "details": [
    {
      "model": "activities",
      "foreign_key": "user_id",
      "list_fields": ["occurred_at", "type", "description"],
      "title": "ACTIVITY.2"
    },
    {
      "model": "roles",
      "foreign_key": "user_id",
      "list_fields": ["name", "slug"],
      "title": "ROLE.2"
    }
  ]
}
```

### Properties

- `model` (required): Related model name
- `foreign_key` (required): Foreign key field in related table
- `list_fields` (required): Array of fields to display
- `title` (optional): Section title (supports i18n keys)

### Backward Compatibility

Single `detail` configuration still works:

```json
{
  "detail": {
    "model": "users",
    "foreign_key": "group_id",
    "list_fields": ["user_name", "email"],
    "title": "Users"
  }
}
```

Internally converted to `details` array with one item.

## Custom Action Buttons

Add custom action buttons for operations beyond Edit/Delete.

### Action Types

#### 1. Field Update (Toggle)

Toggle a boolean field value:

```json
{
  "key": "toggle_enabled",
  "label": "Toggle Enabled",
  "icon": "power-off",
  "type": "field_update",
  "field": "flag_enabled",
  "toggle": true,
  "style": "default",
  "permission": "update_user_field",
  "success_message": "Status updated successfully"
}
```

#### 2. Field Update (Set Value)

Set a specific field value:

```json
{
  "key": "disable_user",
  "label": "Disable User",
  "icon": "ban",
  "type": "field_update",
  "field": "flag_enabled",
  "value": false,
  "style": "danger",
  "permission": "update_user_field",
  "confirm": "Are you sure you want to disable this user?",
  "success_message": "User disabled successfully"
}
```

#### 3. API Call

Make a custom API call:

```json
{
  "key": "reset_password",
  "label": "Reset Password",
  "icon": "envelope",
  "type": "api_call",
  "endpoint": "/api/users/{id}/password/reset",
  "method": "POST",
  "style": "secondary",
  "permission": "update_user_field",
  "confirm": "Send password reset email?",
  "success_message": "Password reset email sent"
}
```

#### 4. Route Navigation

Navigate to another page:

```json
{
  "key": "change_password",
  "label": "Change Password",
  "icon": "key",
  "type": "route",
  "route": "user.password",
  "style": "primary",
  "permission": "update_user_field"
}
```

### Common Properties

- `key` (required): Unique action identifier
- `label` (required): Button text (supports i18n keys)
- `type` (required): Action type (field_update, api_call, route, modal)
- `icon` (optional): FontAwesome icon name
- `style` (optional): Button style (primary, secondary, default, danger)
- `permission` (optional): Required permission
- `confirm` (optional): Confirmation message (supports i18n keys)
- `success_message` (optional): Success message (supports i18n keys)

### Action-Specific Properties

**field_update:**
- `field` (required): Field to update
- `toggle` or `value` (required): Toggle boolean or set specific value

**api_call:**
- `endpoint` (required): API endpoint (use `{id}` placeholder)
- `method` (optional): HTTP method (default: POST)

**route:**
- `route` (required): Route name

## i18n Support

Use translation keys instead of plain text.

### Schema Titles

```json
{
  "title": "USER.PAGE",
  "singular_title": "USER.1",
  "description": "USER.PAGE_DESCRIPTION"
}
```

### Detail Titles

```json
{
  "details": [
    {
      "model": "activities",
      "title": "ACTIVITY.2"
    }
  ]
}
```

### Action Labels

```json
{
  "actions": [
    {
      "label": "USER.ADMIN.TOGGLE_ENABLED",
      "confirm": "USER.ADMIN.TOGGLE_ENABLED_CONFIRM",
      "success_message": "USER.ADMIN.TOGGLE_ENABLED_SUCCESS"
    }
  ]
}
```

### Locale Files

**app/locale/en_US/messages.php:**

```php
return [
    'USER' => [
        'PAGE' => 'Users',
        1 => 'User',
        'PAGE_DESCRIPTION' => 'Manage users',
        'ADMIN' => [
            'TOGGLE_ENABLED' => 'Toggle Enabled',
            'TOGGLE_ENABLED_CONFIRM' => 'Toggle user status?',
            'TOGGLE_ENABLED_SUCCESS' => 'User status updated'
        ]
    ]
];
```

## Complete Examples

### Users Schema (Full Example)

```json
{
  "model": "users",
  "title": "USER.PAGE",
  "singular_title": "USER.1",
  "description": "USER.PAGE_DESCRIPTION",
  "table": "users",
  "primary_key": "id",
  "permissions": {
    "read": "uri_users",
    "create": "create_user",
    "update": "update_user_field",
    "delete": "delete_user"
  },
  "details": [
    {
      "model": "activities",
      "foreign_key": "user_id",
      "list_fields": ["occurred_at", "type", "description"],
      "title": "ACTIVITY.2"
    },
    {
      "model": "roles",
      "foreign_key": "user_id",
      "list_fields": ["name", "slug", "description"],
      "title": "ROLE.2"
    },
    {
      "model": "permissions",
      "foreign_key": "user_id",
      "list_fields": ["slug", "name"],
      "title": "PERMISSION.2"
    }
  ],
  "actions": [
    {
      "key": "toggle_enabled",
      "label": "USER.ADMIN.TOGGLE_ENABLED",
      "icon": "power-off",
      "type": "field_update",
      "field": "flag_enabled",
      "toggle": true,
      "style": "default",
      "permission": "update_user_field",
      "success_message": "USER.ADMIN.TOGGLE_ENABLED_SUCCESS"
    },
    {
      "key": "reset_password",
      "label": "USER.ADMIN.PASSWORD_RESET",
      "icon": "envelope",
      "type": "api_call",
      "endpoint": "/api/users/{id}/password/reset",
      "method": "POST",
      "style": "secondary",
      "permission": "update_user_field",
      "confirm": "USER.ADMIN.PASSWORD_RESET_CONFIRM",
      "success_message": "USER.ADMIN.PASSWORD_RESET_SUCCESS"
    }
  ],
  "fields": {
    "id": {"type": "integer", "label": "ID"},
    "user_name": {"type": "string", "label": "Username"},
    "flag_enabled": {"type": "boolean", "label": "Enabled"}
  }
}
```

### Roles Schema (Multiple Details)

```json
{
  "model": "roles",
  "title": "ROLE.PAGE",
  "singular_title": "ROLE.1",
  "table": "roles",
  "details": [
    {
      "model": "users",
      "foreign_key": "role_id",
      "list_fields": ["user_name", "email", "flag_enabled"],
      "title": "ROLE.USERS"
    },
    {
      "model": "permissions",
      "foreign_key": "role_id",
      "list_fields": ["slug", "name", "description"],
      "title": "ROLE.PERMISSIONS"
    }
  ]
}
```

### Groups Schema (Simple Detail)

```json
{
  "model": "groups",
  "title": "GROUP.PAGE",
  "singular_title": "GROUP.1",
  "table": "groups",
  "details": [
    {
      "model": "users",
      "foreign_key": "group_id",
      "list_fields": ["user_name", "first_name", "last_name", "email"],
      "title": "GROUP.USERS"
    }
  ]
}
```

## Tips and Best Practices

### Multiple Details
- Limit to 3-4 detail sections per page for better UX
- Order sections by importance
- Use clear, descriptive titles

### Custom Actions
- Use meaningful action keys
- Add confirmations for destructive actions
- Provide clear success messages
- Set appropriate permissions
- Choose suitable button styles

### i18n
- Use translation keys consistently
- Organize translations by model
- Provide translations for all languages
- Use placeholders for dynamic content

## See Also

- [SCHEMA_UPDATES_PR146.md](SCHEMA_UPDATES_PR146.md) - Complete change documentation
- CRUD6 Documentation:
  - `docs/CUSTOM_ACTIONS_FEATURE.md`
  - `docs/MULTIPLE_DETAILS_FEATURE.md`
  - `docs/I18N_SUPPORT.md`
- [CRUD6 PR#146](https://github.com/ssnukala/sprinkle-crud6/pull/146)
