# CRUD6 Dynamic Template Migration

## Overview

This document describes the migration from individual view files to CRUD6's dynamic template system in sprinkle-c6admin.

## What Changed

### Before (Old Structure)

C6Admin used separate Vue component files for each model's list and detail views:

```
app/assets/views/
├── PageUsers.vue       # Users list page
├── PageUser.vue        # User detail page
├── PageGroups.vue      # Groups list page
├── PageGroup.vue       # Group detail page
├── PageRoles.vue       # Roles list page
├── PageRole.vue        # Role detail page
├── PagePermissions.vue # Permissions list page
├── PagePermission.vue  # Permission detail page
└── PageActivities.vue  # Activities list page
```

Each file simply imported a component from UserFrosting's sprinkle-admin:

```vue
<template>
    <UFAdminUsersPage />
</template>
```

### After (New Structure)

C6Admin now imports CRUD6's dynamic templates from the `@ssnukala/sprinkle-crud6` package:

```
app/assets/views/
├── PageDashboard.vue     # Dashboard (unchanged - not CRUD)
├── PageConfig.vue        # Config (unchanged - not CRUD)
├── PageConfigCache.vue   # Cache config (unchanged - not CRUD)
└── PageConfigInfo.vue    # System info (unchanged - not CRUD)
```

CRUD views now use templates from the CRUD6 package:
- `CRUD6ListPage` - Common list template for all models
- `CRUD6DynamicPage` - Dynamic wrapper (chooses PageRow or PageMasterDetail)
- `CRUD6RowPage` - Standard detail view  
- `CRUD6MasterDetailPage` - Master-detail view (for related data)


## How It Works

### 1. Dynamic Template System

The CRUD6 templates are **schema-driven**:

- **CRUD6ListPage**: Renders list views using the schema from `app/schema/crud6/{model}.json`
- **CRUD6DynamicPage**: Wrapper that chooses between CRUD6RowPage and CRUD6MasterDetailPage based on schema settings
- **CRUD6RowPage**: Standard detail/edit view
- **CRUD6MasterDetailPage**: Advanced view for models with nested relationships

All templates are imported from `@ssnukala/sprinkle-crud6` package.

### 2. Route Configuration

Routes now inject the model name via `beforeEnter` hooks and import from the CRUD6 package:

```typescript
// app/assets/routes/UserRoutes.ts
{
    path: 'users',
    children: [
        {
            path: '',
            name: 'c6admin.users',
            component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6ListPage),
            beforeEnter: (to) => {
                to.params.model = 'users'  // Inject model name
            }
        },
        {
            path: ':id',
            name: 'c6admin.user',
            component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6DynamicPage),
            beforeEnter: (to) => {
                to.params.model = 'users'
            }
        }
    ]
}
```

### 3. Schema-Driven Rendering

The templates read the model from `route.params.model` and load the corresponding schema:

```javascript
// Inside CRUD6ListPage (from @ssnukala/sprinkle-crud6)
const model = computed(() => route.params.model as string)
const { schema, loadSchema } = useCRUD6Schema()

onMounted(() => {
    loadSchema(model.value)  // Loads app/schema/crud6/users.json
})
```

The schema defines:
- Field labels and types
- Which fields to show in lists
- Which fields are editable
- Validation rules
- Permissions
- And more...

## CRUD6 Package Dependency

C6Admin now uses `@ssnukala/sprinkle-crud6` as a peer dependency. All CRUD6 components, composables, and views are imported from this package:

### Components (from @ssnukala/sprinkle-crud6)

Components available in the CRUD6 package:

- **AutoLookup**: Smart lookup/autocomplete for relationships
- **CreateModal**: Modal for creating new records
- **EditModal**: Modal for editing records
- **DeleteModal**: Modal for deleting records
- **Info**: Display record information
- **Form**: Generic form renderer based on schema
- **Details**: Display related/nested data
- **DetailGrid**: Grid display for detail records
- **MasterDetailForm**: Form for master-detail views

### Composables (from @ssnukala/sprinkle-crud6)

Composables available in the CRUD6 package:

- **useCRUD6Api**: Generic CRUD operations (create, read, update, delete)
- **useCRUD6Schema**: Schema loading and caching
- **useCRUD6Relationships**: Handle model relationships
- **useCRUD6sApi**: Batch operations
- **useMasterDetail**: Master-detail view logic

### Views (from @ssnukala/sprinkle-crud6)

View templates used by C6Admin:

- **CRUD6ListPage**: Common list view for all models
- **CRUD6DynamicPage**: Dynamic wrapper component
- **CRUD6RowPage**: Standard detail/edit view
- **CRUD6MasterDetailPage**: Master-detail view

All are imported from `@ssnukala/sprinkle-crud6` package.

## Benefits

### 1. Reduced Code Duplication

**Before**: 9 separate view files (Users, Groups, Roles, Permissions, Activities - each with list and detail)

**After**: CRUD6 package provides reusable templates for all models

### 2. Schema-Driven Development

Changes to model structure only require updating the JSON schema, not Vue components:

```json
// app/schema/crud6/users.json
{
  "model": "users",
  "fields": {
    "user_name": {
      "type": "string",
      "label": "Username",
      "listable": true,
      "searchable": true
    }
  }
}
```

### 3. Consistent UI/UX

All models now use the same proven templates, ensuring consistency across the admin interface.

### 4. Future-Proof

When CRUD6 adds new features or improvements, C6Admin automatically benefits.

## Backward Compatibility

### Routes Unchanged

All existing routes remain the same:
- `/c6/admin/users` → Users list
- `/c6/admin/users/123` → User detail
- `/c6/admin/groups` → Groups list
- `/c6/admin/groups/456` → Group detail
- etc.

### Permissions Unchanged

Route permissions remain the same:
- `c6_uri_users` for users list
- `c6_uri_user` for user detail
- etc.

### Functionality Unchanged

All CRUD operations work the same way, using the same API endpoints.

## Migration Checklist

If you're updating C6Admin or creating a similar sprinkle:

- [x] Copy CRUD6 view templates (PageList, PageDynamic, PageRow, PageMasterDetail)
- [x] Copy CRUD6 components (CRUD6/* directory)
- [x] Copy CRUD6 composables (useCRUD6*.ts)
- [x] Copy CRUD6 interfaces and types
- [x] Copy CRUD6 stores
- [x] Update route files to use PageList and PageDynamic
- [x] Add beforeEnter hooks to inject model name
- [x] Update imports in copied files to use local paths
- [x] Remove old individual view files
- [x] Update exports in index.ts files
- [x] Verify tests pass
- [x] Test navigation and CRUD operations

## Testing

### Automated Tests

```bash
npm test
```

All route tests should pass, verifying:
- Routes are properly registered
- ID-based parameters are used (not slug/user_name)
- Route structure is maintained

### Manual Testing

Test the following scenarios:
1. Navigate to users list (`/c6/admin/users`)
2. Search and filter users
3. Click on a user to view details
4. Edit a user
5. Create a new user
6. Delete a user
7. Repeat for groups, roles, permissions, activities

## Troubleshooting

### Schema Not Loading

**Symptom**: Page shows "No schema" or loading error

**Solution**: Check that the schema file exists in `app/schema/crud6/{model}.json` and is valid JSON

### Component Not Found

**Symptom**: Import errors for CRUD6 components

**Solution**: Verify all CRUD6 components, composables, and types were copied correctly

### Model Not Detected

**Symptom**: Template can't find the model name

**Solution**: Check that the route has a `beforeEnter` hook that sets `to.params.model`

## Further Reading

- [CRUD6 Documentation](https://github.com/ssnukala/sprinkle-crud6)
- [C6Admin README](../../../README.md)
- [JSON Schema Files](../../schema/crud6/)
