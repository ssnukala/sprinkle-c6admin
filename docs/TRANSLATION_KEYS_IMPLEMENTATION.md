# @TRANSLATION Keys Implementation

**Date:** December 4, 2025  
**Issue:** Using @TRANSLATION keys in messages like UserFrosting 6 core  
**PR Branch:** copilot/add-translation-keys-messages

## Overview

This document describes the implementation of `@TRANSLATION` keys in C6Admin message arrays, following the UserFrosting 6 pattern demonstrated in [sprinkle-core](https://github.com/userfrosting/sprinkle-core/blob/6.0/app/locale/en_US/messages.php).

## Background

UserFrosting 6 uses a special `@TRANSLATION` key within nested translation arrays to provide a default translation for the parent key itself. This is useful when you need to reference the nested key as a translation (e.g., in dropdown menus, headings, or navigation).

### Example from UserFrosting Core

```php
'CAPTCHA' => [
    '@TRANSLATION' => 'Captcha',
    'FAIL'         => 'You did not enter the captcha code correctly.',
    'SPECIFY'      => 'Enter the captcha',
    'VERIFY'       => 'Verify the captcha',
],
```

In this example, `@TRANSLATION` provides the translation for "CAPTCHA" itself, while the nested keys provide related messages.

## Implementation

### Changes Made

Added `@TRANSLATION` keys to six nested message arrays in both English (`app/locale/en_US/messages.php`) and French (`app/locale/fr_FR/messages.php`) locale files:

| Array Path | English Value | French Value |
|-----------|---------------|--------------|
| `CRUD6.ACTIVITY` | 'Activity' | 'Activité' |
| `CRUD6.GROUP` | 'Group' | 'Groupe' |
| `CRUD6.PERMISSION` | 'Permission' | 'Autorisation' |
| `CRUD6.ROLE` | 'Role' | 'Rôle' |
| `CRUD6.USER` | 'User' | 'Utilisateur' |
| `CRUD6.USER.ADMIN` | 'Admin' | 'Admin' |

### Code Examples

**English (en_US):**
```php
'ACTIVITY' => [
    '@TRANSLATION' => 'Activity',
    1 => 'Activity',
    2 => 'Activities',
    'LAST' => 'Last Activity',
    // ... other keys
],
```

**French (fr_FR):**
```php
'ACTIVITY' => [
    '@TRANSLATION' => 'Activité',
    1 => 'Activité',
    2 => 'Activités',
    'LAST' => 'Dernière activité',
    // ... other keys
],
```

## Pattern Consistency

The `@TRANSLATION` values were chosen to match the singular form (key `1`) in each nested array, maintaining consistency with how these translations are already structured:

- **ACTIVITY**: Singular form ('Activity', 'Activité')
- **GROUP**: Singular form ('Group', 'Groupe')
- **PERMISSION**: Singular form ('Permission', 'Autorisation')
- **ROLE**: Singular form ('Role', 'Rôle')
- **USER**: Singular form ('User', 'Utilisateur')
- **USER.ADMIN**: Singular form ('Admin', 'Admin')

## Files Modified

1. **app/locale/en_US/messages.php**
   - Added 6 `@TRANSLATION` keys
   - Lines affected: 55, 75, 113, 144, 184, 207

2. **app/locale/fr_FR/messages.php**
   - Added 6 `@TRANSLATION` keys
   - Lines affected: 20, 44, 75, 95, 149, 154

## Testing

All changes were validated using:

1. **PHP Syntax Validation**
   ```bash
   php -l app/locale/en_US/messages.php
   php -l app/locale/fr_FR/messages.php
   ```
   Result: ✅ No syntax errors

2. **Key Presence Validation**
   - Verified all 6 `@TRANSLATION` keys exist in both locales
   - Result: ✅ All keys present

3. **Value Validation**
   - Verified translation values match expected singular forms
   - Result: ✅ All values correct

4. **Code Review**
   - Automated code review completed
   - Result: ✅ No issues found

## Usage

With these changes, the i18n system can now reference these nested keys directly:

```php
// Example usage in PHP
$translator->translate('CRUD6.ACTIVITY'); // Returns: "Activity" (en) or "Activité" (fr)
```

```vue
<!-- Example usage in Vue.js -->
<template>
  {{ $t('ACTIVITY') }} <!-- Returns: "Activity" (en) or "Activité" (fr) -->
</template>
```

## Benefits

1. **Consistency**: Aligns C6Admin with UserFrosting 6 core patterns
2. **Flexibility**: Allows referencing nested keys as standalone translations
3. **Maintainability**: Single source of truth for singular forms
4. **i18n Support**: Proper support for both English and French locales

## References

- [UserFrosting 6 Core Messages](https://github.com/userfrosting/sprinkle-core/blob/6.0/app/locale/en_US/messages.php)
- [UserFrosting 6 Admin Sprinkle Messages](https://github.com/userfrosting/sprinkle-admin/6.0/app/locale/en_US/messages.php)
- PR: `copilot/add-translation-keys-messages`
- Commit: `5fafaf944208527963b098e56bf98653dd0184ed`
