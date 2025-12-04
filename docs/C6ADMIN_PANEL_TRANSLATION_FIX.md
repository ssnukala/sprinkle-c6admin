# C6ADMIN_PANEL Translation Fix

## Issue Description

The breadcrumb on pages like `/c6/admin/users/8` was showing the literal translation key `C6ADMIN_PANEL` instead of the translated text.

**Before:** "UserFrosting / C6ADMIN_PANEL / Users / user01"  
**After:** "UserFrosting / Admin Panel / Users / user01" (English) or "UserFrosting / Panneau d'admin / Users / user01" (French)

## Root Cause

The route configuration in `app/assets/routes/index.ts` was using `C6ADMIN_PANEL` as the meta title, but this translation key didn't exist in the locale files. The locale files had:

- English (`app/locale/en_US/messages.php`): `CRUD6.ADMIN_PANEL` => 'Admin Panel'
- French (`app/locale/fr_FR/messages.php`): `ADMIN_PANEL` => "Panneau d'admin" (at root level, not namespaced)

## Solution

Instead of creating a new `C6ADMIN_PANEL` translation key, we decided to use the existing `CRUD6.ADMIN_PANEL` key to maintain consistency with the existing locale structure.

### Changes Made

1. **Route Configuration** (`app/assets/routes/index.ts`)
   - Changed default title from `'C6ADMIN_PANEL'` to `'CRUD6.ADMIN_PANEL'`
   - Updated JSDoc comments to reflect the new key

2. **Test Update** (`app/assets/tests/router/routes.test.ts`)
   - Updated test expectation to check for `'CRUD6.ADMIN_PANEL'` instead of `'C6ADMIN_PANEL'`

3. **French Locale Restructure** (`app/locale/fr_FR/messages.php`)
   - Wrapped all translations in a `CRUD6` namespace to match the English locale structure
   - This ensures consistency across both locale files

## Verification

To verify the fix works:

1. **Check Translation Keys Exist:**
   ```bash
   grep "'ADMIN_PANEL'" app/locale/en_US/messages.php
   grep "'ADMIN_PANEL'" app/locale/fr_FR/messages.php
   ```

2. **Verify PHP Syntax:**
   ```bash
   php -l app/locale/en_US/messages.php
   php -l app/locale/fr_FR/messages.php
   ```

3. **Run Tests:**
   ```bash
   npm test
   ```

## Translation System Usage

When using the routes in a UserFrosting 6 application, the translation system will automatically resolve `CRUD6.ADMIN_PANEL` to the correct translated text based on the user's locale:

- **English (en_US):** "Admin Panel"
- **French (fr_FR):** "Panneau d'admin"

## Best Practices

When adding new routes or modifying existing ones:

1. Always use namespaced translation keys (e.g., `CRUD6.SOMETHING`) for consistency
2. Ensure the translation key exists in all supported locale files
3. Maintain the same namespace structure across all locale files
4. Test the translations in both English and French locales

## Files Modified

- `app/assets/routes/index.ts` - Route configuration
- `app/assets/tests/router/routes.test.ts` - Test expectations
- `app/locale/fr_FR/messages.php` - French translations (restructured with CRUD6 namespace)

## Related Documentation

- [UserFrosting 6 Internationalization](https://github.com/userfrosting/framework/tree/6.0)
- [C6Admin Routes Documentation](../app/assets/routes/index.ts)
