# sprinkle-c6admin

Drop-in replacement administrative module for UserFrosting 6, powered by [sprinkle-crud6](https://github.com/ssnukala/sprinkle-crud6).

## Overview

`sprinkle-c6admin` provides the same administrative functionality as the official [userfrosting/sprinkle-admin](https://github.com/userfrosting/sprinkle-admin) but leverages the generic CRUD capabilities of sprinkle-crud6. This approach provides:

- **JSON Schema-based Configuration**: All models (users, roles, groups, permissions) are defined using JSON schemas
- **Generic CRUD Operations**: Reusable CRUD controllers for all admin entities
- **RESTful API**: Clean, consistent API endpoints following UserFrosting patterns
- **Drop-in Replacement**: Compatible with existing UserFrosting 6 applications

## Features

- **User Management**: Full CRUD operations for users using CRUD6
- **Role Management**: Manage user roles with JSON schema definitions
- **Group Management**: Organize users into groups
- **Permission Management**: Handle permissions and authorization
- **Activity Logging**: Track user activities
- **Dashboard**: Administrative dashboard with statistics
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
        C6Admin::class,
        // Your other sprinkles...
    ];
}
```

## Structure

```
app/
├── schema/c6admin/        # JSON schemas for admin models
│   ├── users.json
│   ├── roles.json
│   ├── groups.json
│   ├── permissions.json
│   └── activities.json
├── src/
│   ├── C6Admin.php        # Main sprinkle class
│   ├── Controller/        # Controllers (Dashboard, Config)
│   ├── Middlewares/       # Model-specific injectors
│   └── Routes/            # Route definitions
```

## Usage

### API Endpoints

The sprinkle provides RESTful API endpoints matching the original sprinkle-admin:

**Groups**:
- `GET /api/groups` - List all groups
- `GET /api/groups/g/{slug}` - Get group by slug
- `POST /api/groups` - Create group
- `PUT /api/groups/g/{slug}` - Update group
- `DELETE /api/groups/g/{slug}` - Delete group

**Users, Roles, Permissions**: Similar patterns (implementation in progress)

**Dashboard**:
- `GET /api/dashboard` - Get dashboard data

**Configuration**:
- `GET /api/config/info` - System information
- `DELETE /api/cache` - Clear cache

### JSON Schemas

All models are defined using JSON schemas in `app/schema/c6admin/`. These schemas define:
- Table structure and fields
- Validation rules
- Permissions
- Display configuration

Example `groups.json`:
```json
{
  "model": "groups",
  "title": "Group Management",
  "table": "groups",
  "permissions": {
    "read": "uri_groups",
    "create": "create_group",
    "update": "update_group_field",
    "delete": "delete_group"
  },
  "fields": {
    "id": { "type": "integer", "auto_increment": true },
    "slug": { "type": "string", "required": true },
    "name": { "type": "string", "required": true },
    "description": { "type": "text" }
  }
}
```

## Development Status

This sprinkle is currently in development. Completed features:

- [x] Core structure and sprinkle class
- [x] JSON schemas for all models
- [x] Dashboard controller
- [x] Config/Cache controllers
- [x] Group management routes (CRUD6-based)
- [ ] User management routes
- [ ] Role management routes
- [ ] Permission management routes
- [ ] Activity log routes
- [ ] Templates and frontend assets
- [ ] Full test coverage

## Architecture

`sprinkle-c6admin` uses a layered approach:

1. **JSON Schemas** define the data structure for each model
2. **CRUD6 Controllers** provide generic CRUD operations
3. **Custom Injectors** adapt admin-style routes to work with CRUD6
4. **Routes** maintain compatibility with original sprinkle-admin endpoints

This architecture allows the sprinkle to provide the same functionality as sprinkle-admin while leveraging the power and flexibility of sprinkle-crud6.

## Contributing

Contributions are welcome! This project follows the same coding standards as UserFrosting.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

- Based on [userfrosting/sprinkle-admin](https://github.com/userfrosting/sprinkle-admin)
- Powered by [ssnukala/sprinkle-crud6](https://github.com/ssnukala/sprinkle-crud6)
- Part of the [UserFrosting](https://www.userfrosting.com) ecosystem

## Support

For issues and questions:
- GitHub Issues: https://github.com/ssnukala/sprinkle-c6admin/issues
- UserFrosting Chat: https://chat.userfrosting.com
