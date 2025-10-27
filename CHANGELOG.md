# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Complete Frontend from sprinkle-admin**:
  - 14 Vue.js page components (Dashboard, Users, Groups, Roles, Permissions, Activities, Config)
  - 14 API composables for CRUD operations
  - 20+ TypeScript interfaces for type safety
  - Vue Router integration with permission guards
  - Sidebar menu component
  - i18n support (English and French)
  - Email templates
- **Admin-Specific Features**:
  - Password reset functionality (custom controller at `/api/users/{id}/password-reset`)
  - User activation/deactivation (via CRUD6 UpdateFieldAction)
  - Relationship management (via CRUD6 RelationshipAction)
- JSON schemas for core models (users, roles, groups, permissions, activities)
- Dashboard controller for admin statistics
- Config controllers for system info and cache management
- Comprehensive README documentation
- MIT License

### Changed
- **BREAKING**: API endpoints now at `/api/crud6/{model}` for CRUD operations
- **BREAKING**: Admin-specific operations at `/api/{model}` (e.g., password reset)
- **BREAKING**: All lookups use `id` instead of slug/user_name
- **Frontend Routes**: Updated to use `:id` param instead of `:slug` or `:user_name`
- **API Composables**: All refactored to use ID-based lookups
  - Fixed `useUserUpdateApi.submitUserUpdate(id, ...)` - parameter changed from user_name to id
  - Fixed `useRoleUpdateApi.submitRoleUpdate(id, ...)` - parameter changed from slug to id
- Schemas location: `app/schema/c6admin/` → `app/schema/crud6/`
- Simplified architecture - leverages CRUD6 for all CRUD operations

### Fixed
- Update API composables now use correct ID parameter instead of slug/user_name

### Removed
- Custom middleware injectors (not needed - CRUD6 provides)
- Individual CRUD route files (conflicts avoided)
- Slug/user_name based lookups

## [0.1.0] - 2024-10-27

### Added
- Initial project setup
- Core sprinkle class (C6Admin)
- composer.json with dependencies
- Basic route structure
