# sprinkle-c6admin

Admin schemas and utilities for UserFrosting 6, designed to work with [sprinkle-crud6](https://github.com/ssnukala/sprinkle-crud6).

## Overview

`sprinkle-c6admin` provides JSON schema definitions for administrative models (users, roles, groups, permissions, activities) that work seamlessly with sprinkle-crud6. Instead of duplicating CRUD functionality, this sprinkle leverages CRUD6's generic controllers and routes.

**Key benefits:**
- **No Custom Routes**: Uses CRUD6's existing `/api/crud6/{model}` endpoints
- **No Custom Controllers**: All CRUD operations handled by CRUD6
- **JSON Schema-based**: Define models once, CRUD6 handles the rest
- **Consistent API**: All models use `id` for lookups (not slug/user_name)

## Features

- **JSON Schemas** for core admin models:
  - Users
  - Roles
  - Groups
  - Permissions
  - Activities
- **Dashboard API**: Administrative dashboard with statistics
- **System Configuration**: Cache management and system information

## Requirements

- PHP 8.1 or higher
- UserFrosting 6.0 or higher
- sprinkle-crud6 1.0 or higher

## Installation

Add to your UserFrosting 6 project:

```bash
composer require ssnukala/sprinkle-c6admin
```

Then register the sprinkle in your main application sprinkle class:

```php
use UserFrosting\Sprinkle\C6Admin\C6Admin;

public function getSprinkles(): array
{
    return [
        Core::class,
        Account::class,
        CRUD6::class,     // Required: CRUD6 must be registered
        C6Admin::class,
        // Your other sprinkles...
    ];
}
```

**Important**: `CRUD6` must be registered before `C6Admin`.

## Structure

```
app/
├── schema/crud6/          # JSON schemas for CRUD6 models
│   ├── users.json
│   ├── roles.json
│   ├── groups.json
│   ├── permissions.json
│   └── activities.json
└── src/
    ├── C6Admin.php        # Main sprinkle class
    ├── Controller/        # Non-CRUD controllers only
    │   ├── Dashboard/     # Dashboard API
    │   └── Config/        # System config/cache
    └── Routes/            # Non-CRUD routes only
        ├── DashboardRoutes.php
        └── ConfigRoutes.php
```

## Usage

### CRUD Operations

All CRUD operations use CRUD6's standard endpoints with `id` for lookups:

**Groups**:
- `GET /api/crud6/groups` - List all groups
- `GET /api/crud6/groups/{id}` - Get group by ID
- `POST /api/crud6/groups` - Create group
- `PUT /api/crud6/groups/{id}` - Update group
- `PUT /api/crud6/groups/{id}/{field}` - Update single field
- `DELETE /api/crud6/groups/{id}` - Delete group

**Users**:
- `GET /api/crud6/users` - List all users
- `GET /api/crud6/users/{id}` - Get user by ID
- `POST /api/crud6/users` - Create user
- `PUT /api/crud6/users/{id}` - Update user
- `DELETE /api/crud6/users/{id}` - Delete user

**Roles, Permissions**: Same pattern as groups

**Activities** (read-only):
- `GET /api/crud6/activities` - List activities
- `GET /api/crud6/activities/{id}` - Get activity by ID

### Admin-Specific Endpoints

**Dashboard**:
- `GET /api/dashboard` - Get dashboard data (user/role/group counts, recent users)

**Configuration**:
- `GET /api/config/info` - System information
- `DELETE /api/cache` - Clear cache

### JSON Schemas

All models are defined using JSON schemas in `app/schema/crud6/`. These schemas define:
- Table structure and fields
- Validation rules
- Permissions
- Display configuration

Example schema structure:
```json
{
  "model": "groups",
  "title": "Group Management",
  "table": "groups",
  "primary_key": "id",
  "permissions": {
    "read": "uri_groups",
    "create": "create_group",
    "update": "update_group_field",
    "delete": "delete_group"
  },
  "fields": {
    "id": { "type": "integer", "auto_increment": true },
    "slug": { "type": "string", "required": true },
    "name": { "type": "string", "required": true }
  }
}
```

## Key Differences from sprinkle-admin

1. **ID-based lookups**: Uses `id` instead of `slug` or `user_name`
2. **CRUD6 routes**: Uses `/api/crud6/{model}` instead of `/api/{model}`
3. **No custom controllers**: Leverages CRUD6's generic controllers
4. **Simpler architecture**: Just schemas + dashboard/config

## Architecture

This sprinkle follows a minimalist approach:

1. **JSON Schemas** - Define model structure for CRUD6
2. **CRUD6 Integration** - All CRUD handled by sprinkle-crud6
3. **Admin Utilities** - Dashboard and config endpoints only

No custom injectors, no custom CRUD controllers, no route conflicts.

## Contributing

Contributions are welcome! This project follows the same coding standards as UserFrosting.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

- Designed to work with [ssnukala/sprinkle-crud6](https://github.com/ssnukala/sprinkle-crud6)
- Part of the [UserFrosting](https://www.userfrosting.com) ecosystem

## Support

For issues and questions:
- GitHub Issues: https://github.com/ssnukala/sprinkle-c6admin/issues
- UserFrosting Chat: https://chat.userfrosting.com
