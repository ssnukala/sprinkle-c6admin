# Schema Cleanup - Field-by-Field Comparison

This document shows the attributes removed from each field in all schemas.

## Users

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, searchable, filterable |
| `user_name` | ✅ Yes | sortable, searchable | filterable |
| `first_name` | ✅ Yes | sortable, searchable | filterable |
| `last_name` | ✅ Yes | sortable, searchable | filterable |
| `email` | ✅ Yes | sortable, searchable | filterable |
| `locale` | ❌ No | - | sortable, searchable, filterable |
| `group_id` | ❌ No | - | sortable, searchable, filterable |
| `flag_verified` | ✅ Yes | sortable, searchable | filterable |
| `flag_enabled` | ✅ Yes | sortable, searchable | filterable |
| `password` | ❌ No | - | sortable, searchable, filterable |
| `deleted_at` | ❌ No | - | sortable, searchable, filterable |
| `created_at` | ❌ No | - | sortable, searchable, filterable |
| `updated_at` | ❌ No | - | sortable, searchable, filterable |

## Groups

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, searchable, filterable |
| `slug` | ❌ No | - | sortable, searchable, filterable |
| `name` | ✅ Yes | sortable, searchable | filterable |
| `description` | ✅ Yes | sortable, searchable | filterable |
| `icon` | ❌ No | - | sortable, searchable, filterable |
| `created_at` | ❌ No | - | sortable, searchable, filterable |
| `updated_at` | ❌ No | - | sortable, searchable, filterable |

## Roles

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, searchable, filterable |
| `slug` | ❌ No | - | sortable, searchable, filterable |
| `name` | ✅ Yes | sortable, searchable | filterable |
| `description` | ✅ Yes | sortable, searchable | filterable |
| `created_at` | ❌ No | - | sortable, searchable, filterable |
| `updated_at` | ❌ No | - | sortable, searchable, filterable |

## Permissions

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, searchable, filterable |
| `slug` | ✅ Yes | sortable, searchable | filterable |
| `name` | ✅ Yes | sortable, searchable | filterable |
| `conditions` | ❌ No | - | sortable, searchable, filterable |
| `description` | ✅ Yes | sortable, searchable | filterable |
| `created_at` | ❌ No | - | sortable, searchable, filterable |
| `updated_at` | ❌ No | - | sortable, searchable, filterable |

## Activities

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, searchable, filterable |
| `ip_address` | ❌ No | - | sortable, searchable, filterable |
| `user_id` | ❌ No | - | sortable, searchable, filterable |
| `type` | ❌ No | - | sortable, searchable, filterable |
| `occurred_at` | ✅ Yes | sortable, searchable | filterable |
| `description` | ✅ Yes | sortable, searchable | filterable |

