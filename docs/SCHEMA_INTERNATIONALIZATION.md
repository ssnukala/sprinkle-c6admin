# Schema Internationalization Implementation

## Overview

This document describes the implementation of internationalization for the C6Admin schema files. All English text that was previously hardcoded in the JSON schema files has been moved to translation files to support multiple languages.

## Problem Statement

The schema files contained English text in various `description` fields that could not be translated. This prevented the application from being fully localized for non-English users.

## Solution

All hardcoded English text in schema files has been replaced with translation keys that follow the pattern `CRUD6.{MODEL}.{KEY}`. These keys are defined in the locale files for both English (en_US) and French (fr_FR).

## Changes Made

### 1. Schema Files Updated

#### users.json
- `"Assign default role to new users"` → `"CRUD6.USER.ASSIGN_DEFAULT_ROLE_DESCRIPTION"`
- `"Sync user roles from form input"` → `"CRUD6.USER.SYNC_ROLES_DESCRIPTION"`
- `"Remove all role associations when user is deleted"` → `"CRUD6.USER.DETACH_ROLES_DESCRIPTION"`
- `"Email verification status"` → `"CRUD6.USER.VERIFIED_DESCRIPTION"`
- `"Account enabled status"` → `"CRUD6.USER.ENABLED_DESCRIPTION"`
- `"User roles (used for sync on update)"` → `"CRUD6.USER.ROLE_IDS_DESCRIPTION"`

#### roles.json
- `"Sync role permissions from form input"` → `"CRUD6.ROLE.SYNC_PERMISSIONS_DESCRIPTION"`
- `"Remove all permission associations when role is deleted"` → `"CRUD6.ROLE.DETACH_PERMISSIONS_DESCRIPTION"`
- `"Remove all user associations when role is deleted"` → `"CRUD6.ROLE.DETACH_USERS_DESCRIPTION"`
- `"Role permissions (used for sync on update)"` → `"CRUD6.ROLE.PERMISSION_IDS_DESCRIPTION"`

#### permissions.json
- `"Sync permission roles from form input"` → `"CRUD6.PERMISSION.SYNC_ROLES_DESCRIPTION"`
- `"Remove all role associations when permission is deleted"` → `"CRUD6.PERMISSION.DETACH_ROLES_DESCRIPTION"`
- `"Permission roles (used for sync on update)"` → `"CRUD6.PERMISSION.ROLE_IDS_DESCRIPTION"`

### 2. Translation Keys Added

All translation keys were added to both `app/locale/en_US/messages.php` and `app/locale/fr_FR/messages.php`.

#### English Translations (en_US)

**User translations:**
- `VERIFIED_DESCRIPTION`: "Email verification status"
- `ENABLED_DESCRIPTION`: "Account enabled status"
- `ROLE_IDS_DESCRIPTION`: "User roles (used for sync on update)"
- `ASSIGN_DEFAULT_ROLE_DESCRIPTION`: "Assign default role to new users"
- `SYNC_ROLES_DESCRIPTION`: "Sync user roles from form input"
- `DETACH_ROLES_DESCRIPTION`: "Remove all role associations when user is deleted"

**Role translations:**
- `PERMISSION_IDS_DESCRIPTION`: "Role permissions (used for sync on update)"
- `SYNC_PERMISSIONS_DESCRIPTION`: "Sync role permissions from form input"
- `DETACH_PERMISSIONS_DESCRIPTION`: "Remove all permission associations when role is deleted"
- `DETACH_USERS_DESCRIPTION`: "Remove all user associations when role is deleted"

**Permission translations:**
- `ROLE_IDS_DESCRIPTION`: "Permission roles (used for sync on update)"
- `SYNC_ROLES_DESCRIPTION`: "Sync permission roles from form input"
- `DETACH_ROLES_DESCRIPTION`: "Remove all role associations when permission is deleted"

#### French Translations (fr_FR)

All translations were also added in French. Examples:
- `VERIFIED_DESCRIPTION`: "Statut de vérification de l'e-mail"
- `ENABLED_DESCRIPTION`: "Statut d'activation du compte"
- `SYNC_ROLES_DESCRIPTION`: "Synchroniser les rôles d'utilisateur à partir de l'entrée du formulaire"

### 3. Tests Added

New test methods were added to `app/tests/Schema/SchemaValidationTest.php`:

#### `testDescriptionsUseTranslationKeys()`
Validates that all `description` fields in schema files use translation keys starting with `CRUD6.` rather than plain English text.

#### `testTranslationKeysExistInLocaleFiles()`
Validates that all translation keys used in schema files exist in both English and French locale files.

## Validation

All changes were validated to ensure:

1. ✅ All JSON schema files are valid JSON
2. ✅ All PHP locale files have valid syntax
3. ✅ All translation keys used in schemas exist in both en_US and fr_FR locale files
4. ✅ No English text remains in schema description fields
5. ✅ All translations are non-empty strings

## Testing

Run the schema validation tests with:

```bash
vendor/bin/phpunit app/tests/Schema/SchemaValidationTest.php
```

Or run all tests:

```bash
vendor/bin/phpunit
```

## Benefits

1. **Full Localization**: The application can now be fully translated to any language
2. **Consistency**: All user-facing text follows the same translation pattern
3. **Maintainability**: Text changes only need to be made in locale files, not in schema files
4. **Testing**: Automated tests ensure translation keys exist and are valid

## Files Modified

- `app/schema/crud6/users.json`
- `app/schema/crud6/roles.json`
- `app/schema/crud6/permissions.json`
- `app/locale/en_US/messages.php`
- `app/locale/fr_FR/messages.php`
- `app/tests/Schema/SchemaValidationTest.php`

## Migration Notes

For developers using this sprinkle:

1. No breaking changes were introduced
2. All existing functionality remains the same
3. Schema files now properly support internationalization
4. If you extend these schemas, ensure you use translation keys for all descriptions

## Future Improvements

Consider extending this pattern to other schema properties that may contain user-facing text, such as:
- Action labels
- Validation error messages
- Field placeholders
