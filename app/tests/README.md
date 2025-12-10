# C6Admin Test Data

This directory contains a **schema-driven SQL generation system** for creating test data.

## Quick Start

### Generate SQL from Configuration

```bash
php generate-test-data.php > test-data.sql
```

### Execute SQL

```bash
# In a UserFrosting 6 project (after migrations and base seeds)
mysql -h 127.0.0.1 -u user -p database < test-data.sql
```

## Files

- **`generate-test-data.php`** - Schema-driven SQL generator script
- **`test-data-config.json`** - Test data configuration (EDIT THIS)
- **`test-data.sql`** - Generated SQL file (DON'T EDIT - regenerate instead)

## How to Modify Test Data

### Edit Configuration

Modify `test-data-config.json`:

```json
{
  "models": [
    {
      "model": "users",
      "records": [
        {
          "user_name": "newuser",
          "email": "new@example.com",
          "_group_slug": "developers"
        }
      ]
    }
  ]
}
```

### Regenerate SQL

```bash
php generate-test-data.php > test-data.sql
```

### Test It

```bash
mysql -h 127.0.0.1 -u root -p userfrosting_test < test-data.sql
```

## Features

✅ Reads C6Admin JSON schemas automatically
✅ Type-safe SQL generation (string, int, boolean, etc.)
✅ Idempotent (can run multiple times)
✅ Foreign key lookups (e.g., `_group_slug` → `group_id`)
✅ Relationship support (pivot tables)
✅ Fully documented SQL output

## Current Test Data

### Groups (3 records)
- `developers` - Development team members
- `managers` - Management team
- `testers` - QA team

### Users (4 records)
- `testadmin` - Site admin (managers group, site-admin role)
- `c6admin` - CRUD6 admin (developers group, crud6-admin role)
- `testuser` - Regular user (terran group, user role)
- `testmoderator` - Moderator (testers group, user + crud6-admin roles)

**All users have password**: `testpass123`

## Documentation

See [TEST_DATA_GENERATION.md](../../docs/TEST_DATA_GENERATION.md) for complete documentation.

## Why SQL Instead of PHP Seeds?

**Old approach** (PHP seeds):
- ❌ Namespace issues when moving files
- ❌ Manual schema synchronization
- ❌ Complex idempotency handling
- ❌ Class loading issues in CI

**New approach** (SQL generation):
- ✅ Schema-driven (auto-sync)
- ✅ Simple execution in CI
- ✅ Built-in idempotency
- ✅ Easy to modify (JSON config)
- ✅ Works with any SQL tool
