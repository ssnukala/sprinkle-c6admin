# Schema Cleanup - Field-by-Field Comparison

This document shows the attributes removed from each field in all schemas.

## Users

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, filterable, searchable |
| `user_name` | ✅ Yes | sortable, filterable | searchable |
| `first_name` | ✅ Yes | sortable, filterable | searchable |
| `last_name` | ✅ Yes | sortable, filterable | searchable |
| `email` | ✅ Yes | sortable, filterable | searchable |
| `locale` | ❌ No | - | sortable, filterable, searchable |
| `group_id` | ❌ No | - | sortable, filterable, searchable |
| `flag_verified` | ✅ Yes | sortable, filterable | searchable |
| `flag_enabled` | ✅ Yes | sortable, filterable | searchable |
| `password` | ❌ No | - | sortable, filterable, searchable |
| `deleted_at` | ❌ No | - | sortable, filterable, searchable |
| `created_at` | ❌ No | - | sortable, filterable, searchable |
| `updated_at` | ❌ No | - | sortable, filterable, searchable |

## Groups

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, filterable, searchable |
| `slug` | ❌ No | - | sortable, filterable, searchable |
| `name` | ✅ Yes | sortable, filterable | searchable |
| `description` | ✅ Yes | sortable, filterable | searchable |
| `icon` | ❌ No | - | sortable, filterable, searchable |
| `created_at` | ❌ No | - | sortable, filterable, searchable |
| `updated_at` | ❌ No | - | sortable, filterable, searchable |

## Roles

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, filterable, searchable |
| `slug` | ❌ No | - | sortable, filterable, searchable |
| `name` | ✅ Yes | sortable, filterable | searchable |
| `description` | ✅ Yes | sortable, filterable | searchable |
| `created_at` | ❌ No | - | sortable, filterable, searchable |
| `updated_at` | ❌ No | - | sortable, filterable, searchable |

## Permissions

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, filterable, searchable |
| `slug` | ✅ Yes | sortable, filterable | searchable |
| `name` | ✅ Yes | sortable, filterable | searchable |
| `conditions` | ❌ No | - | sortable, filterable, searchable |
| `description` | ✅ Yes | sortable, filterable | searchable |
| `created_at` | ❌ No | - | sortable, filterable, searchable |
| `updated_at` | ❌ No | - | sortable, filterable, searchable |

## Activities

| Field | Listable | Attributes Kept | Attributes Removed |
|-------|----------|-----------------|--------------------|
| `id` | ❌ No | - | sortable, filterable, searchable |
| `ip_address` | ❌ No | - | sortable, filterable, searchable |
| `user_id` | ❌ No | - | sortable, filterable, searchable |
| `type` | ❌ No | - | sortable, filterable, searchable |
| `occurred_at` | ✅ Yes | sortable, filterable | searchable |
| `description` | ✅ Yes | sortable, filterable | searchable |

