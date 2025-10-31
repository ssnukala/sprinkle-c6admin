# Detail Relationships Implementation

## Overview

This document describes the implementation of detail relationships in C6Admin schemas to match the functionality of userfrosting/sprinkle-admin. These relationships enable viewing related data on detail pages, such as a user's roles, a group's users, and a role's permissions.

## Problem Statement

The original `/admin/users/u/{username}` functionality in userfrosting/sprinkle-admin displayed:
- User's groups
- User's roles  
- Change password button
- Other user management features

The C6Admin detail views at `/c6/admin/users/{id}` needed to provide equivalent functionality using CRUD6 components.

## Solution

CRUD6 supports two types of relationship configurations in schemas:

### 1. One-to-Many Relationships (Detail Object)

Use the `detail` property to display related records in a one-to-many relationship:

```json
{
  "model": "groups",
  "detail": {
    "model": "users",
    "foreign_key": "group_id",
    "list_fields": ["user_name", "first_name", "last_name", "email", "flag_enabled"],
    "title": "GROUP.USERS"
  }
}
```

**Properties:**
- `model`: The related model name
- `foreign_key`: The foreign key field in the related table
- `list_fields`: Array of field names to display in the detail list
- `title`: Display title (supports i18n keys)

### 2. Many-to-Many Relationships (Relationships Array)

Use the `relationships` array for many-to-many associations through pivot tables:

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

**Properties:**
- `name`: Relationship name (used in API endpoints)
- `type`: Must be "many_to_many"
- `pivot_table`: Junction/pivot table name
- `foreign_key`: Column in pivot table referencing this model
- `related_key`: Column in pivot table referencing related model
- `title`: Display title (supports i18n keys)

## Implementation

### Schema Changes

#### users.json

Added `relationships` array to show user's roles and `detail` object to show user's activities:

```json
"relationships": [
  {
    "name": "roles",
    "type": "many_to_many",
    "pivot_table": "role_user",
    "foreign_key": "user_id",
    "related_key": "role_id",
    "title": "ROLE.2"
  }
],
"detail": {
  "model": "activities",
  "foreign_key": "user_id",
  "list_fields": ["occurred_at", "type", "description", "ip_address"],
  "title": "ACTIVITY.2"
}
```

This displays:
- A list of roles assigned to the user
- A list of activities (audit log) for this user

#### groups.json

Added `detail` object to show users in the group:

```json
"detail": {
  "model": "users",
  "foreign_key": "group_id",
  "list_fields": ["user_name", "first_name", "last_name", "email", "flag_enabled"],
  "title": "GROUP.USERS"
}
```

This displays a list of all users belonging to the group on the group detail page.

#### roles.json

Added `relationships` array with two relationships - permissions and users:

```json
"relationships": [
  {
    "name": "permissions",
    "type": "many_to_many",
    "pivot_table": "permission_role",
    "foreign_key": "role_id",
    "related_key": "permission_id",
    "title": "ROLE.PERMISSIONS"
  },
  {
    "name": "users",
    "type": "many_to_many",
    "pivot_table": "role_user",
    "foreign_key": "role_id",
    "related_key": "user_id",
    "title": "ROLE.USERS"
  }
]
```

This displays both:
- Permissions assigned to the role
- Users who have this role

#### permissions.json

Added `relationships` array to show roles that have the permission:

```json
"relationships": [
  {
    "name": "roles",
    "type": "many_to_many",
    "pivot_table": "permission_role",
    "foreign_key": "permission_id",
    "related_key": "role_id",
    "title": "ROLE.2"
  }
]
```

This displays a list of roles that have this permission.

## Translation Keys

All relationship titles use existing translation keys from the locale files:

- `ROLE.2` - "Roles" (plural form)
- `ROLE.PERMISSIONS` - "Role permissions"
- `ROLE.USERS` - "Users with this role"
- `GROUP.USERS` - "Users in this group"
- `ACTIVITY.2` - "Activities" (plural form)

These keys are already defined in:
- `app/locale/en_US/messages.php`
- `app/locale/fr_FR/messages.php`

## How It Works

### CRUD6 View Selection

The CRUD6 package uses the `CRUD6DynamicPage` component as a wrapper that automatically chooses between:

1. **CRUD6RowPage**: Standard detail/edit view (when no relationships/details are configured)
2. **CRUD6MasterDetailPage**: Advanced view with relationship sections (when relationships/details are present)

The selection is automatic based on the schema configuration - no additional frontend code is needed.

### Detail View Sections

When viewing a detail page:
1. The master record information is displayed in the main section
2. Related records are shown in additional sections below
3. Each relationship appears as a separate data table
4. Relationship sections use the configured `title` for headers
5. Relationship sections show the fields specified in `list_fields` (for detail) or default fields (for relationships)

### API Endpoints

CRUD6 automatically provides API endpoints for managing relationships:

**Read relationships:**
- `GET /api/crud6/users/5/roles` - Get user's roles
- `GET /api/crud6/roles/3/permissions` - Get role's permissions

**Manage many-to-many relationships:**
- `POST /api/crud6/users/5/roles` - Attach roles to user
  ```json
  { "ids": [1, 2, 3] }
  ```
- `DELETE /api/crud6/users/5/roles` - Detach roles from user
  ```json
  { "ids": [2] }
  ```

**Read one-to-many details:**
- `GET /api/crud6/groups/2/users` - Get users in group (via foreign key)

## Password Reset Feature

The password reset functionality is already implemented in C6Admin:

**Backend:**
- Controller: `app/src/Controller/User/UserPasswordResetAction.php`
- Route: `POST /api/c6/users/{id}/password-reset`
- Translation: `USER.ADMIN.PASSWORD_RESET_SUCCESS`

**Frontend:**
- Composable: `app/assets/composables/useUserPasswordResetApi.ts`
- Function: `passwordReset(id: number)`

This forces a user to reset their password on next login by expiring their current password (setting `password_last_set` to null).

## Testing

### Automated Tests

All existing tests pass:
```bash
npm test  # Frontend tests (3/3 passed)
```

### Manual Testing

To test the detail relationships in a full UserFrosting 6 application:

1. **User detail page** (`/c6/admin/users/{id}`):
   - Should display user information
   - Should show a "Roles" section with the user's assigned roles
   - Should show an "Activities" section with the user's activity log
   - Should have a change password button (if implemented in CRUD6 detail view)

2. **Group detail page** (`/c6/admin/groups/{id}`):
   - Should display group information
   - Should show a "Users in this group" section with all users in the group

3. **Role detail page** (`/c6/admin/roles/{id}`):
   - Should display role information
   - Should show a "Role permissions" section with permissions assigned to the role
   - Should show a "Users with this role" section with users who have this role

4. **Permission detail page** (`/c6/admin/permissions/{id}`):
   - Should display permission information
   - Should show a "Roles" section with roles that have this permission

## Benefits

### 1. Matches sprinkle-admin Functionality

The detail views now provide the same information as userfrosting/sprinkle-admin:
- Users show their roles (equivalent to role management in sprinkle-admin)
- Groups show their users
- Roles show permissions and users
- Permissions show roles

### 2. No Frontend Code Required

All relationship rendering is handled automatically by CRUD6:
- No custom Vue components needed
- No custom API calls required
- Schema changes are the only modification needed

### 3. Consistent with CRUD6 Architecture

The implementation follows CRUD6's schema-driven approach:
- All configuration in JSON schemas
- Automatic API endpoint generation
- Consistent UI/UX across all models

### 4. Maintainable and Extensible

Adding new relationships requires only schema updates:
```json
// Add a new relationship - that's it!
"relationships": [
  {
    "name": "new_relation",
    "type": "many_to_many",
    "pivot_table": "pivot_table_name",
    "foreign_key": "this_id",
    "related_key": "related_id",
    "title": "TRANSLATION.KEY"
  }
]
```

## Future Enhancements

Potential future improvements:

1. **Inline Relationship Editing**: Enable adding/removing relationships directly from the detail view
2. **Custom Relationship Actions**: Add custom buttons or actions for specific relationships
3. **Relationship Filtering**: Add search/filter capabilities to relationship sections
4. **Nested Relationships**: Show relationships of related records (e.g., permissions of roles that a user has)

## References

- [CRUD6 README](../node_modules/@ssnukala/sprinkle-crud6/README.md) - Full CRUD6 documentation
- [CRUD6 Migration Guide](CRUD6_MIGRATION.md) - How C6Admin uses CRUD6 templates
- [UserFrosting sprinkle-admin](https://github.com/userfrosting/sprinkle-admin) - Original admin sprinkle
- [UserFrosting Documentation](https://learn.userfrosting.com) - UserFrosting framework documentation
