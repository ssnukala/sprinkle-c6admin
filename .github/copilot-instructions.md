# UserFrosting 6 C6Admin Sprinkle

UserFrosting 6 C6Admin Sprinkle is a complete admin interface for UserFrosting 6, powered by [sprinkle-crud6](https://github.com/ssnukala/sprinkle-crud6). It replicates all functionality of the official [userfrosting/sprinkle-admin](https://github.com/userfrosting/sprinkle-admin) while leveraging sprinkle-crud6 for CRUD operations.

Always reference these instructions first and fallback to search or bash commands only when you encounter unexpected information that does not match the info here.

## 🚨 CRITICAL PATTERNS - DO NOT MODIFY 🚨

### C6Admin Architecture (Established Pattern)

**THIS ARCHITECTURE IS CORRECT AND WORKING - DO NOT CHANGE IT**

C6Admin is a **frontend-focused sprinkle** that provides:
- **Complete Vue.js admin interface**: 14 pages, 14 composables, 20+ TypeScript interfaces
- **JSON schema definitions**: For users, roles, groups, permissions, activities
- **Utility controllers**: Dashboard API and system configuration utilities
- **CRUD delegation to CRUD6**: All CRUD operations handled by sprinkle-crud6

**Key Pattern:**
- C6Admin provides the **admin interface and schemas**
- CRUD6 provides the **CRUD operations and API endpoints**
- All CRUD routes are at `/api/crud6/{model}` endpoints
- Controllers in C6Admin are limited to Dashboard and Config utilities

**DO NOT:**
- ❌ Add CRUD controllers to C6Admin (use CRUD6 instead)
- ❌ Create custom API endpoints for basic CRUD (use `/api/crud6/{model}`)
- ❌ Duplicate CRUD6 functionality in C6Admin
- ❌ Remove or modify the dependency on CRUD6

**Affected Files:**
- `app/src/Controller/DashboardApiAction.php` - Dashboard statistics
- `app/src/Controller/CacheApiAction.php` - Cache management
- `app/src/Controller/SystemInfoApiAction.php` - System information
- All Vue.js files in `app/assets/` - Frontend interface

## UserFrosting 6 Framework Reference

### Core Philosophy
All code modifications and refactoring in this sprinkle **MUST** consider the UserFrosting 6 framework architecture, patterns, and standards. The goal is to **extend and reuse** existing patterns and core components already created in the framework rather than reinventing solutions.

### Reference Repositories
When developing or modifying code, always reference these official UserFrosting 6 repositories for patterns, standards, and component examples:

1. **[userfrosting/sprinkle-core (6.0 branch)](https://github.com/userfrosting/sprinkle-core/tree/6.0)**
   - Core sprinkle with fundamental services and patterns
   - Reference for: Service providers, middleware, base controllers, cache service, i18n service, session handling
   - Key patterns: `ServicesProviderInterface`, `RouteDefinitionInterface`, core middleware

2. **[userfrosting/sprinkle-admin (6.0 branch)](https://github.com/userfrosting/sprinkle-admin/tree/6.0)**
   - Admin interface sprinkle with CRUD operations
   - Reference for: Vue.js components, admin pages, UI patterns, composables
   - Key patterns: Admin UI components, dashboard layout, data tables

3. **[ssnukala/sprinkle-crud6 (main branch)](https://github.com/ssnukala/sprinkle-crud6)**
   - Generic CRUD API layer for UserFrosting 6
   - Reference for: CRUD controllers, middleware patterns, JSON schema structure
   - Key patterns: CRUD6 controllers, schema definitions, Sprunje implementations

4. **[userfrosting/framework (6.0 branch)](https://github.com/userfrosting/framework/tree/6.0)**
   - Core framework components and interfaces
   - Reference for: Base interfaces, traits, service containers, testing utilities
   - Key patterns: Framework contracts, base classes, testing infrastructure

5. **[userfrosting/theme-pink-cupcake (6.0 branch)](https://github.com/userfrosting/theme-pink-cupcake/tree/6.0)**
   - Default theme implementation
   - Reference for: Frontend patterns, Vue.js components, UI/UX standards
   - Key patterns: Template structure, asset organization, frontend integration

### Code Modification Standards

#### 1. Follow UserFrosting 6 Patterns
- **Service Providers**: All services MUST implement `ServicesProviderInterface` and follow the DI container patterns from sprinkle-core
- **Controllers**: Use action-based controllers (one action per class) following sprinkle-admin patterns
- **Routes**: Implement `RouteDefinitionInterface` for route definitions
- **Models**: Extend Eloquent models and follow UserFrosting model patterns
- **Sprunje**: Use Sprunje pattern for data listing, filtering, and pagination
- **Middleware**: Follow middleware patterns from sprinkle-core

#### 2. Extend and Reuse Core Components
Before creating new components, check if UserFrosting 6 already provides:
- **Authentication/Authorization**: Use existing `AuthGuard` and permission system from sprinkle-account
- **Data Tables**: Extend Sprunje classes rather than creating custom solutions
- **Validation**: Use UserFrosting's validation system
- **Alerts/Notifications**: Use existing alert system from sprinkle-core
- **Caching**: Use `CacheService` from sprinkle-core
- **Internationalization**: Use `I18nService` for translations
- **Session Management**: Use `SessionService` from sprinkle-core

#### 3. Adhere to Framework Standards
- **PSR-12**: All code must follow PSR-12 coding standards
- **Type Declarations**: Use strict types (`declare(strict_types=1);`)
- **Dependency Injection**: Use constructor injection with PHP-DI
- **Naming Conventions**: Follow UserFrosting naming conventions
  - Controllers: `{Action}Action.php` (e.g., `CreateAction.php`)
  - Services: `{Name}Service.php` (e.g., `SchemaService.php`)
  - Service Providers: `{Name}ServiceProvider.php`
  - Sprunje: `{Model}Sprunje.php`
  - Middleware: `{Name}Injector.php` or `{Name}Middleware.php`

#### 4. C6Admin Frontend Architecture

**Frontend Structure:**
C6Admin provides a complete Vue.js admin interface with:
- **14 Vue.js Pages**: Dashboard, Users, Groups, Roles, Permissions, Activities, Config
- **14 API Composables**: Full CRUD operations for all models
- **20+ TypeScript Interfaces**: Type-safe API communication
- **Vue Router Integration**: Clean routing with permissions
- **i18n Support**: English and French translations

**Frontend Patterns:**
```typescript
// API Composable Pattern (e.g., useUsersApi)
export function useUsersApi() {
  const fetchUsers = async (params?: SprunjeParams) => {
    return api.get('/api/crud6/users', { params });
  };
  
  const fetchUser = async (id: number) => {
    return api.get(`/api/crud6/users/${id}`);
  };
  
  // Create, update, delete methods using CRUD6 endpoints
}
```

**Backend Controllers:**
C6Admin only includes utility controllers, NOT CRUD controllers:
- `DashboardApiAction.php` - Dashboard statistics
- `CacheApiAction.php` - Cache management
- `SystemInfoApiAction.php` - System information

All CRUD operations are delegated to CRUD6 at `/api/crud6/{model}` endpoints.

**DO NOT:**
- ❌ Add CRUD controllers to C6Admin (use CRUD6)
- ❌ Create custom CRUD API endpoints (use CRUD6)
- ❌ Modify CRUD6 endpoints from within C6Admin

#### 5. Testing Standards
- Follow testing patterns from sprinkle-admin and sprinkle-account
- Use `RefreshDatabase` trait for database tests
- Backend tests focus on dashboard and utility controllers
- Frontend tests validate Vue components and composables
- Test service providers and business logic separately
- Mock external dependencies appropriately

#### 6. Documentation Standards
- Document all public methods with PHPDoc blocks
- Include `@param`, `@return`, and `@throws` annotations
- Reference UserFrosting documentation patterns
- Keep inline comments minimal and meaningful

### Integration Guidelines

#### When Adding New Features
1. **Research First**: Check reference repositories for existing solutions
2. **Pattern Matching**: Match your implementation to UserFrosting patterns
3. **Component Reuse**: Extend existing components rather than creating new ones
4. **Consistency**: Maintain consistency with core sprinkles
5. **Testing**: Add tests following UserFrosting testing patterns

#### When Refactoring Code
1. **Review References**: Check how similar code is implemented in core sprinkles
2. **Service Container**: Ensure proper DI container usage
3. **Backwards Compatibility**: Maintain compatibility when possible
4. **Documentation**: Update documentation to reflect changes
5. **Testing**: Ensure existing tests pass and add new tests if needed

### Common Patterns to Follow

#### Service Provider Pattern (from sprinkle-core)
```php
class MyServiceProvider implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            MyService::class => \DI\autowire(MyService::class),
        ];
    }
}
```

#### Action Controller Pattern (from sprinkle-admin)
```php
class MyAction
{
    public function __construct(
        protected MyService $service,
        protected AlertStream $alert
    ) {
    }

    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // Action logic here
    }
}
```

#### Route Definition Pattern (from sprinkle-core)
```php
class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/myroute', function (RouteCollectorProxy $group) {
            $group->get('', ListAction::class)->setName('api.myroute.list');
            $group->post('', CreateAction::class)->setName('api.myroute.create');
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
```

#### Sprunje Pattern (from sprinkle-admin)
```php
class MySprunje extends Sprunje
{
    protected string $name = 'my_model';
    
    protected function baseQuery()
    {
        return $this->classMapper->createInstance(MyModel::class);
    }
}
```

## Working Effectively

### Bootstrap and Setup
- Install PHP 8.1 or later: `php --version` (required: PHP 8.1+)
- Install Composer: `composer --version` (required for dependency management)
- Bootstrap dependencies: `composer install` -- takes 3-5 minutes depending on network. NEVER CANCEL. Set timeout to 15+ minutes.
  - **CRITICAL**: In CI environments, composer may require GitHub token. Use: `export COMPOSER_AUTH='{"github-oauth":{"github.com":"your_token"}}'` before running composer commands
  - **Alternative for auth issues**: `composer install --ignore-platform-reqs --no-scripts` (limited functionality)
  - **If install fails**: `composer dump-autoload` will still generate basic autoloader
- Verify syntax: `find app/src -name "*.php" -exec php -l {} \;` -- takes under 1 second. All files should show "No syntax errors detected"

### Testing
- **DEPENDENCY REQUIREMENT**: Full testing requires `composer install` to complete successfully
- Run unit tests: `vendor/bin/phpunit` -- takes 2-3 minutes. NEVER CANCEL. Set timeout to 10+ minutes.
  - **CRITICAL**: This sprinkle requires a UserFrosting 6 application context for full functionality
  - If dependencies not installed: Tests will fail with missing class errors
  - Unit tests for models: `vendor/bin/phpunit app/tests/Database/Models/CRUD6ModelTest.php`
- **MANUAL VALIDATION**: When composer install fails, validate JSON schemas and PHP syntax manually
- **JSON Schema Validation**: `php -r "echo json_encode(json_decode(file_get_contents('examples/products.json')), JSON_PRETTY_PRINT) ? 'JSON valid' : 'JSON invalid';"`

### Development Tools
- **Requires successful composer install** for development tools to be available
- Code formatting: `vendor/bin/php-cs-fixer fix` -- takes 1-2 minutes for full codebase. NEVER CANCEL. Set timeout to 10+ minutes.
- Static analysis: `vendor/bin/phpstan analyse` -- takes 2-3 minutes. NEVER CANCEL. Set timeout to 10+ minutes.
- **If vendor tools unavailable**: Use syntax check as primary validation: `find app/src -name "*.php" -exec php -l {} \;`
- **ALWAYS** run syntax validation before committing: `find app/src -name "*.php" -exec php -l {} \;`

### Integration with UserFrosting 6
- **CRITICAL**: This is a UserFrosting 6 sprinkle, not a standalone application
- To use in a UserFrosting 6 project:
  1. Add to composer.json: `"minimum-stability": "beta", "prefer-stable": true`
  2. Install: `composer require ssnukala/sprinkle-crud6`
  3. Add to sprinkles in your main sprinkle class: `CRUD6::class`
- **CANNOT RUN STANDALONE**: This sprinkle requires a full UserFrosting 6 application to function

## Validation

### Critical Testing Scenarios
- **Schema Validation**: Test JSON schema loading and validation using examples in `app/schema/crud6/` and `examples/`
- **Model Configuration**: Verify CRUD6Model can be configured from schema definitions
- **API Endpoint Structure**: Validate that controllers follow UserFrosting 6 patterns for `/api/crud6/{model}` routes
- **Field Type Mapping**: Test all supported field types (string, integer, boolean, date, datetime, text, json, float, decimal)
- **Validation Rules**: Test required fields, length constraints, email validation, and unique constraints

### Manual Testing Requirements
Always test these scenarios after making changes:
1. **Syntax Validation**: `find app/src -name "*.php" -exec php -l {} \;` - must pass for all files
2. **JSON Schema Validation**: 
   - `php -r "echo json_decode(file_get_contents('app/schema/crud6/users.json')) ? 'users.json valid' : 'users.json invalid';"`
   - `php -r "echo json_decode(file_get_contents('examples/products.json')) ? 'products.json valid' : 'products.json invalid';"`
3. **Schema Structure Check**: Verify required fields exist in JSON schemas:
   - `model`, `table`, `fields` keys must be present
   - Each field must have a `type` property
   - Valid types: string, integer, boolean, date, datetime, text, json, float, decimal
4. **API Route Structure**: Verify routes follow `/api/crud6/{model}` pattern in `app/src/Routes/CRUD6Routes.php`
5. **Controller Inheritance**: Check that controllers extend appropriate base classes in `app/src/Controller/Base/`

### CRITICAL Manual Validation Steps
After making ANY changes to CRUD6Model, schema files, or controllers:
1. Run full syntax check: `find app/src -name "*.php" -exec php -l {} \;`
2. Validate all JSON schemas: `for file in app/schema/crud6/*.json examples/*.json; do php -r "if(json_decode(file_get_contents('$file'))) { echo '$file valid'; } else { echo '$file invalid'; } echo PHP_EOL;"; done`
3. Check autoloader: `composer dump-autoload`
4. If dependencies available, run tests: `vendor/bin/phpunit`

### Code Quality
- All PHP files must pass syntax check: `php -l filename.php`
- Code must be PSR-12 compliant: run `vendor/bin/php-cs-fixer fix` before committing
- Static analysis must pass: run `vendor/bin/phpstan analyse` before committing
- **ALWAYS** run the full test suite before submitting changes: `vendor/bin/phpunit`

## Common Development Scenarios

### Working Without Full Dependencies
When `composer install` fails due to authentication issues:
1. Use `composer dump-autoload` to generate basic autoloader
2. Run syntax validation: `find app/src -name "*.php" -exec php -l {} \;`
3. Validate JSON schemas with manual commands
4. Make code changes focusing on syntax and structure
5. Test schema format and API patterns manually

### Adding New Model Support
1. Create JSON schema file in `app/schema/crud6/model_name.json`
2. Follow existing schema patterns in `app/schema/crud6/users.json` or `app/schema/crud6/groups.json`
3. Test schema: `php -r "echo json_decode(file_get_contents('app/schema/crud6/model_name.json')) ? 'valid' : 'invalid';"`
4. Verify required fields: `model`, `table`, `fields`
5. Add corresponding Vue.js pages, composables, and routes in `app/assets/`
6. Add translations in `app/locale/en_US/` and `app/locale/fr_FR/`

### Debugging Schema Issues
1. **Invalid JSON**: Use `php -r "json_decode('content'); echo json_last_error_msg();"`
2. **Missing fields**: Check for required `model`, `table`, `fields` keys
3. **Field validation**: Ensure each field has `type` property
4. **Type validation**: Use only supported types (string, integer, boolean, date, datetime, text, json, float, decimal)

### Integration Testing Approach
Since this is a UserFrosting 6 sprinkle:
1. **Unit testing**: Focus on dashboard and utility controllers
2. **Integration testing**: Requires full UserFrosting 6 application setup with CRUD6
3. **API testing**: Test dashboard API and config endpoints
4. **Frontend testing**: Test Vue.js components and composables with Vitest
5. **Schema testing**: Validate JSON schema loading and structure

### Adding New Functionality
1. **Schema Changes**: Modify or add JSON schema files in `app/schema/crud6/`
2. **Frontend Pages**: Add Vue.js pages in `app/assets/views/`
3. **Frontend Composables**: Add API composables in `app/assets/composables/`
4. **Routes**: Update `app/assets/routes/` for new frontend routes
5. **Translations**: Add translations in `app/locale/en_US/` and `app/locale/fr_FR/`
6. **Testing**: Add tests in `app/tests/` for backend and Vitest tests for frontend

## Project Structure

### Key Directories
```
app/
├── assets/                  # Frontend (Vue.js, TypeScript)
│   ├── components/          # Vue components
│   ├── composables/         # API composables (14 files)
│   ├── views/               # Page components (14 pages)
│   ├── routes/              # Vue Router definitions
│   └── interfaces/          # TypeScript types
├── locale/                  # Translations
│   ├── en_US/
│   └── fr_FR/
├── schema/crud6/            # JSON schemas for CRUD6
│   ├── users.json
│   ├── roles.json
│   ├── groups.json
│   ├── permissions.json
│   └── activities.json
├── src/                     # Backend (PHP)
│   ├── C6Admin.php          # Main sprinkle class
│   ├── Controller/          # Dashboard & Config controllers
│   └── Routes/              # Route definitions
├── templates/               # Email templates
└── tests/                   # PHPUnit and Vitest tests
```

### Important Files
- `app/src/C6Admin.php`: Main sprinkle registration class
- `app/src/Controller/DashboardApiAction.php`: Dashboard statistics API
- `app/src/Controller/CacheApiAction.php`: Cache management API
- `app/src/Controller/SystemInfoApiAction.php`: System information API
- `app/src/Routes/C6AdminRoutes.php`: Route definitions
- `app/assets/index.ts`: Frontend entry point
- `app/assets/routes/index.ts`: Vue Router configuration
- `composer.json`: Dependencies and autoloading configuration
- `package.json`: Frontend dependencies and scripts
- `phpunit.xml`: Test configuration
- `vite.config.ts`: Vite build configuration

### Configuration Files
- `composer.json`: Project dependencies and scripts
- `package.json`: Frontend dependencies and npm scripts
- `phpunit.xml`: PHPUnit test suite configuration
- `vite.config.ts`: Vite bundler configuration
- `tsconfig.json`: TypeScript configuration
- `app/schema/crud6/*.json`: Schema definitions for different models

### Directory Management Policy
**IMPORTANT**: Only create directories when they contain actual files with content. Do NOT create empty directories with `.gitkeep` files.

**Rationale**: While UserFrosting 6 framework may include certain directories (like `app/storage`, `app/logs`, `app/cache`, `app/sessions`, `app/database`), this sprinkle should only include directories that contain relevant content. Empty directories serve no purpose in a sprinkle and create unnecessary clutter.

**Rules**:
- ❌ DO NOT create empty folders with `.gitkeep` files
- ❌ DO NOT create framework-standard directories (storage, logs, cache, sessions, database) unless they contain sprinkle-specific files
- ✅ Only create directories when adding actual files with content
- ✅ Remove any empty directories found in the repository

## Time Expectations

### Build and Test Times
- **Composer install**: 2-5 minutes (network dependent). Use 10+ minute timeout
- **NPM install**: 2-5 minutes (network dependent). Use 10+ minute timeout
- **Syntax validation**: Under 1 second for all files
- **PHPUnit tests**: 1-2 minutes for backend tests. Use 10+ minute timeout
- **Vitest tests**: 1-2 minutes for frontend tests. Use 5+ minute timeout
- **PHP-CS-Fixer**: 1-2 minutes for full codebase. NEVER CANCEL - use 10+ minute timeout
- **PHPStan analysis**: 1-2 minutes. NEVER CANCEL - use 10+ minute timeout
- **Frontend build**: 2-5 minutes. Use 10+ minute timeout

### Development Workflow
1. Make changes to source files (backend or frontend)
2. **Backend changes**: Run syntax check: `find app/src -name "*.php" -exec php -l {} \;` (under 1 second)
3. **Frontend changes**: Run type check: `npm run type-check` (30 seconds - 1 minute)
4. Validate JSON schemas: `php -r "echo json_decode(file_get_contents('app/schema/crud6/users.json')) ? 'users.json valid' : 'users.json invalid';"`
5. **If dependencies available**: Run backend tests: `vendor/bin/phpunit` (2 minutes - use 10+ minute timeout)
6. **If dependencies available**: Run frontend tests: `npm test` (2 minutes - use 5+ minute timeout)
7. **If dependencies available**: Format code: `vendor/bin/php-cs-fixer fix` (2 minutes - use 10+ minute timeout)
8. **If dependencies available**: Analyze code: `vendor/bin/phpstan analyse` (2 minutes - use 10+ minute timeout)

## Common Validation Commands

### Required Before Every Commit
```bash
# Backend - Syntax check (under 1 second)
find app/src -name "*.php" -exec php -l {} \;

# Frontend - Type check (30 seconds - 1 minute)
npm run type-check

# Validate JSON schemas
php -r "echo json_decode(file_get_contents('app/schema/crud6/users.json')) ? 'users.json valid' : 'users.json invalid';"

# If dependencies available (run these if vendor/bin exists):
# Backend code formatting (2 minutes - NEVER CANCEL)
vendor/bin/php-cs-fixer fix

# Backend static analysis (2 minutes - NEVER CANCEL)  
vendor/bin/phpstan analyse

# Backend test suite (2 minutes - NEVER CANCEL)
vendor/bin/phpunit

# Frontend test suite (2 minutes)
npm test
```

### Quick Development Checks
```bash
# Fast syntax validation (ALWAYS works)
php -l app/src/C6Admin.php

# Validate main JSON schema files
php -r "echo json_decode(file_get_contents('app/schema/crud6/users.json')) ? 'users.json valid' : 'users.json invalid';"
php -r "echo json_decode(file_get_contents('app/schema/crud6/groups.json')) ? 'groups.json valid' : 'groups.json invalid';"

# Test autoloader generation
composer dump-autoload

# If dependencies available:
# Single backend test class
vendor/bin/phpunit app/tests/Controller/DashboardApiTest.php

# Frontend dev server
npm run dev
```

## Troubleshooting

### Common Issues
- **Composer timeout**: Use longer timeout (10+ minutes) for `composer install`
- **NPM install issues**: Clear cache with `npm cache clean --force` before installing
- **"Class not found" errors**: Indicates missing dependencies - run `composer install` successfully first
- **UserFrosting integration**: This sprinkle requires a UserFrosting 6 application with CRUD6 - cannot run standalone
- **PHP version**: Requires PHP 8.1 or later
- **Node.js version**: Requires Node.js 16 or later
- **CRUD6 dependency**: C6Admin requires sprinkle-crud6 to be installed and registered before C6Admin
- **Vendor tools missing**: If `vendor/bin/` doesn't exist, use basic validation only (syntax check, JSON validation)
- **Frontend build errors**: Ensure all npm dependencies are installed with `npm install`

## Documentation Guidelines

### Documentation Folder (docs/)
All repository documentation, including fix summaries, implementation guides, analysis documents, and issue-specific guides should be created in the `docs/` directory. This keeps the repository root clean and organized while preserving documentation for future reference.

**Important**: Files in `docs/` ARE tracked by git and will be committed to the repository. The docs folder is not ignored - it's a permanent part of the repository structure.

#### Documentation Location Rules
- **Active Documentation** (keep in root):
  - `README.md` - Main project documentation
  - `CHANGELOG.md` - Version history
  - `LICENSE.md` - License file

- **Repository Documentation** (place in `docs/`):
  - All fix summaries (e.g., `*_FIX_SUMMARY.md`, `*_FIX.md`)
  - Visual comparison documents (e.g., `VISUAL_*.md`, `*_COMPARISON*.md`)
  - Issue-specific documentation (e.g., `ISSUE_*.md`, `PR*.md`)
  - Testing approach documents (e.g., `TESTING_APPROACH.md`, `TESTING_GUIDE.md`)
  - Implementation summaries (e.g., `*_IMPLEMENTATION_SUMMARY.md`)
  - Code review documents (e.g., `CODE_REVIEW.md`)
  - Analysis documents (e.g., `SPRUNJE_ANALYSIS.md`, `*_ANALYSIS.md`)
  - Checklist documents (e.g., `*_CHECKLIST.md`)
  - Before/after comparison documents
  - Migration guides (e.g., `MIGRATION_*.md`)
  - Integration testing guides (e.g., `INTEGRATION_TESTING.md`)
  - Quick reference guides (e.g., `QUICK_TEST_GUIDE.md`)
  - Any other development or repository-specific documentation

#### Creating New Documentation
When creating documentation for fixes, features, or issues:
1. **Always create in `docs/`**: All new documentation should go directly into `docs/` unless it's a core documentation file (README.md, CHANGELOG.md, LICENSE.md)
2. **Use descriptive names**: Name files clearly to indicate their purpose (e.g., `ISSUE_123_FIX_SUMMARY.md`, `FEATURE_XYZ_IMPLEMENTATION.md`)
3. **Include context**: Add issue/PR numbers and dates to help with future reference
4. **Keep root clean**: Never create fix summaries or temporary documentation in the repository root

#### Note
The `docs/` directory is tracked by git and all files in it are committed to the repository. This approach keeps the repository root clean while maintaining a complete history of fixes and changes in an organized subdirectory.