# Action Label Translation Error Fix

## Issue
User reported error when accessing user detail page at `/c6/admin/users/1`:

```
useTranslator.ts:219 Uncaught (in promise) TypeError: Cannot read properties of undefined (reading 'replace')
at replacePlaceholders (useTranslator.ts:219:31)
at Proxy.translate (useTranslator.ts:88:20)
...
at Info.vue:221:20
```

## Root Cause

The error occurred in the `Info.vue` component from `@ssnukala/sprinkle-crud6` at line 221:

```vue
<button v-for="action in customActions" ...>
    {{ $t(action.label) }}  <!-- Line 221 -->
</button>
```

When `action.label` is `undefined`, the Vue `$t()` helper calls the translator with `undefined`:
- `translate(undefined, {})` is called
- `getMessageFromKey(undefined, {})` returns `{ message: undefined, placeholders: {} }`
- `replacePlaceholders(undefined, {})` is called with `message = undefined`
- At line 219, `message.replace()` fails because `message` is `undefined`

## Investigation

Checked the `app/schema/crud6/users.json` file and found that all 6 actions were missing the required `label` property:

```json
{
    "key": "toggle_enabled",
    "type": "field_update",
    "field": "flag_enabled",
    "toggle": true,
    // Missing: "label": "USER.ADMIN.TOGGLE_ENABLED"
    "permission": "update_user_field"
}
```

## Solution

Added the `label` property to all actions in `app/schema/crud6/users.json`:

| Action Key | Label Translation Key |
|------------|----------------------|
| `toggle_enabled` | `USER.ADMIN.TOGGLE_ENABLED` |
| `toggle_verified` | `USER.ADMIN.TOGGLE_VERIFIED` |
| `reset_password` | `USER.ADMIN.PASSWORD_RESET` |
| `password_action` | `USER.ADMIN.CHANGE_PASSWORD` |
| `disable_user` | `USER.DISABLE` |
| `enable_user` | `USER.ENABLE` |

All translation keys already existed in both:
- `app/locale/en_US/messages.php`
- `app/locale/fr_FR/messages.php`

## Schema Action Requirements

### Required Properties
When defining actions in schema files, the following properties are **required**:

1. **`key`** (string): Unique identifier for the action
2. **`type`** (string): Action type (`field_update`, `api_call`, etc.)
3. **`label`** (string): Translation key for the button label (e.g., `USER.ADMIN.TOGGLE_ENABLED`)
4. **`permission`** (string): Permission required to see/execute the action

### Optional Properties
- **`field`** (string): Field to update (for `field_update` type)
- **`value`** (any): Value to set (for `field_update` type)
- **`toggle`** (boolean): Whether to toggle boolean field (for `field_update` type)
- **`confirm`** (string): Translation key for confirmation message
- **`method`** (string): HTTP method (for `api_call` type)
- **`style`** (string): Button style class (e.g., `primary`, `danger`)
- **`icon`** (string): FontAwesome icon name

### Example Action Definition

```json
{
    "key": "toggle_enabled",
    "type": "field_update",
    "field": "flag_enabled",
    "toggle": true,
    "label": "USER.ADMIN.TOGGLE_ENABLED",
    "permission": "update_user_field",
    "style": "primary",
    "icon": "toggle-on"
}
```

## Files Changed

- `app/schema/crud6/users.json`: Added `label` property to all 6 actions

## Verification

1. ✅ JSON syntax validated with PHP
2. ✅ All translation keys verified to exist in en_US and fr_FR locales
3. ✅ Checked other schema files (groups, roles, permissions, activities) - none have actions
4. ⏳ Runtime testing requires full UserFrosting 6 application with CRUD6

## Related Files

- **Source of error**: `node_modules/@ssnukala/sprinkle-crud6/app/assets/components/CRUD6/Info.vue:221`
- **Translator**: `node_modules/@userfrosting/sprinkle-core/app/assets/stores/useTranslator.ts:219`
- **Schema**: `app/schema/crud6/users.json`
- **Translations**: `app/locale/en_US/messages.php`, `app/locale/fr_FR/messages.php`

## Prevention

To prevent similar issues in the future:

1. **Always include `label` property** when defining actions in schema files
2. **Verify translation keys exist** before adding them to schema
3. **Add schema validation** to catch missing required properties (future enhancement)
4. **Document schema requirements** clearly in README or schema documentation

## Notes

- This fix is specific to c6admin but the pattern applies to any UserFrosting 6 sprinkle using CRUD6
- The Info.vue component is provided by sprinkle-crud6, not c6admin
- A defensive fix in Info.vue to handle missing labels could prevent this error in CRUD6, but proper schema definition is the correct solution
