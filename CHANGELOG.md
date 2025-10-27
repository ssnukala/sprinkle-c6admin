# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- **BREAKING**: Removed custom injectors - now uses CRUD6's built-in injector
- **BREAKING**: Removed individual route files for CRUD operations
- **BREAKING**: Changed from slug/user_name lookups to ID-based lookups for consistency
- **BREAKING**: CRUD endpoints now at `/api/crud6/{model}` instead of `/api/{model}`
- Moved schemas from `app/schema/c6admin/` to `app/schema/crud6/`
- Simplified architecture - now just schemas + dashboard/config utilities

### Added
- JSON schemas for core models (users, roles, groups, permissions, activities)
- Dashboard controller for admin statistics
- Config controllers for system info and cache management
- Comprehensive documentation
- MIT License

### Removed
- Custom middleware injectors (GroupInjector, UserInjector, etc.)
- Custom route files (GroupsRoute, UsersRoutes, etc.)
- Duplicate CRUD functionality (now handled by CRUD6)

## [0.1.0] - 2024-10-27

### Added
- Initial project setup
- Core sprinkle class (C6Admin)
- composer.json with dependencies
- Basic route structure
