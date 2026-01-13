-- ========================================
-- Dynamically Generated Test Data SQL
-- Generated: 2026-01-13 01:17:40
-- Source: /home/runner/work/sprinkle-c6admin/sprinkle-c6admin/app/tests/test-data-config.json
-- ========================================

-- ========================================
-- Test Groups - Additional groups for testing (base groups already exist from Account seeds)
-- Model: groups
-- Table: groups
-- ========================================

-- Developers group - for development team members (ID 100 for testing)
INSERT INTO `groups` (`id`, `slug`, `name`, `description`, `icon`)
VALUES (100, 'developers', 'Developers', 'Development team members with elevated privileges', 'fa fa-code')
ON DUPLICATE KEY UPDATE
    `slug` = VALUES(`slug`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `icon` = VALUES(`icon`);

-- Managers group - for management team (ID 101 for testing)
INSERT INTO `groups` (`id`, `slug`, `name`, `description`, `icon`)
VALUES (101, 'managers', 'Managers', 'Management team with administrative access', 'fa fa-users')
ON DUPLICATE KEY UPDATE
    `slug` = VALUES(`slug`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `icon` = VALUES(`icon`);

-- Testers group - for QA team (ID 102 for testing)
INSERT INTO `groups` (`id`, `slug`, `name`, `description`, `icon`)
VALUES (102, 'testers', 'Testers', 'Quality assurance and testing team', 'fa fa-bug')
ON DUPLICATE KEY UPDATE
    `slug` = VALUES(`slug`),
    `name` = VALUES(`name`),
    `description` = VALUES(`description`),
    `icon` = VALUES(`icon`);


-- ========================================
-- Test Users - Users with different roles and permissions for testing
-- Model: users
-- Table: users
-- ========================================

-- testadmin - Test administrator with site-admin role (ID 100 for testing)
INSERT INTO `users` (`id`, `user_name`, `first_name`, `last_name`, `email`, `password`, `flag_enabled`, `flag_verified`, `group_id`)
SELECT 100, 'testadmin', 'Test', 'Administrator', 'testadmin@example.com', '$2y$10$8pJ7TqVqXqKvGqGqVqXqKeVqXqVqXqVqXqVqXqVqXqVqXqVqXqVqO', 1, 1, `groups`.`id`
FROM `groups`
WHERE `groups`.`slug` = 'managers'
ON DUPLICATE KEY UPDATE
    `user_name` = VALUES(`user_name`),
    `first_name` = VALUES(`first_name`),
    `last_name` = VALUES(`last_name`),
    `email` = VALUES(`email`),
    `password` = VALUES(`password`),
    `flag_enabled` = VALUES(`flag_enabled`),
    `flag_verified` = VALUES(`flag_verified`),
    `group_id` = VALUES(`group_id`);

-- c6admin - C6Admin test user with crud6-admin role (ID 101 for testing)
INSERT INTO `users` (`id`, `user_name`, `first_name`, `last_name`, `email`, `password`, `flag_enabled`, `flag_verified`, `group_id`)
SELECT 101, 'c6admin', 'C6', 'Administrator', 'c6admin@example.com', '$2y$10$8pJ7TqVqXqKvGqGqVqXqKeVqXqVqXqVqXqVqXqVqXqVqXqVqXqVqO', 1, 1, `groups`.`id`
FROM `groups`
WHERE `groups`.`slug` = 'developers'
ON DUPLICATE KEY UPDATE
    `user_name` = VALUES(`user_name`),
    `first_name` = VALUES(`first_name`),
    `last_name` = VALUES(`last_name`),
    `email` = VALUES(`email`),
    `password` = VALUES(`password`),
    `flag_enabled` = VALUES(`flag_enabled`),
    `flag_verified` = VALUES(`flag_verified`),
    `group_id` = VALUES(`group_id`);

-- testuser - Regular user with basic permissions (ID 102 for testing)
INSERT INTO `users` (`id`, `user_name`, `first_name`, `last_name`, `email`, `password`, `flag_enabled`, `flag_verified`, `group_id`)
VALUES (102, 'testuser', 'Test', 'User', 'testuser@example.com', '$2y$10$8pJ7TqVqXqKvGqGqVqXqKeVqXqVqXqVqXqVqXqVqXqVqXqVqXqVqO', 1, 1, 1)
ON DUPLICATE KEY UPDATE
    `user_name` = VALUES(`user_name`),
    `first_name` = VALUES(`first_name`),
    `last_name` = VALUES(`last_name`),
    `email` = VALUES(`email`),
    `password` = VALUES(`password`),
    `flag_enabled` = VALUES(`flag_enabled`),
    `flag_verified` = VALUES(`flag_verified`),
    `group_id` = VALUES(`group_id`);

-- testmoderator - Test moderator with multiple roles (ID 103 for testing)
INSERT INTO `users` (`id`, `user_name`, `first_name`, `last_name`, `email`, `password`, `flag_enabled`, `flag_verified`, `group_id`)
SELECT 103, 'testmoderator', 'Test', 'Moderator', 'testmoderator@example.com', '$2y$10$8pJ7TqVqXqKvGqGqVqXqKeVqXqVqXqVqXqVqXqVqXqVqXqVqXqVqO', 1, 1, `groups`.`id`
FROM `groups`
WHERE `groups`.`slug` = 'testers'
ON DUPLICATE KEY UPDATE
    `user_name` = VALUES(`user_name`),
    `first_name` = VALUES(`first_name`),
    `last_name` = VALUES(`last_name`),
    `email` = VALUES(`email`),
    `password` = VALUES(`password`),
    `flag_enabled` = VALUES(`flag_enabled`),
    `flag_verified` = VALUES(`flag_verified`),
    `group_id` = VALUES(`group_id`);


-- ========================================
-- Relationship Assignments (Pivot Tables)
-- ========================================

-- Role assignments for test users
INSERT INTO `role_users` (`user_id`, `role_id`)
SELECT `users`.`id`, `roles`.`id`
FROM `users`, `roles`
WHERE `users`.`user_name` = 'testadmin' AND `roles`.`slug` = 'site-admin'
ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`);

INSERT INTO `role_users` (`user_id`, `role_id`)
SELECT `users`.`id`, `roles`.`id`
FROM `users`, `roles`
WHERE `users`.`user_name` = 'c6admin' AND `roles`.`slug` = 'crud6-admin'
ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`);

INSERT INTO `role_users` (`user_id`, `role_id`)
SELECT `users`.`id`, `roles`.`id`
FROM `users`, `roles`
WHERE `users`.`user_name` = 'testuser' AND `roles`.`slug` = 'user'
ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`);

INSERT INTO `role_users` (`user_id`, `role_id`)
SELECT `users`.`id`, `roles`.`id`
FROM `users`, `roles`
WHERE `users`.`user_name` = 'testmoderator' AND `roles`.`slug` = 'user'
ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`);

INSERT INTO `role_users` (`user_id`, `role_id`)
SELECT `users`.`id`, `roles`.`id`
FROM `users`, `roles`
WHERE `users`.`user_name` = 'testmoderator' AND `roles`.`slug` = 'crud6-admin'
ON DUPLICATE KEY UPDATE `user_id` = VALUES(`user_id`);


-- ========================================
-- Summary
-- ========================================
-- groups: 3 record(s)
-- users: 4 record(s)
-- role_users: 5 assignment(s)
-- Total: 7 records, 5 relationships
-- ========================================
