<?php

/*
 * UserFrosting C6Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/ssnukala/sprinkle-c6admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/ssnukala/sprinkle-c6admin/blob/master/LICENSE.md (MIT License)
 */

/**
 * French message token translations for the 'c6admin' sprinkle.
 *
 * This file only contains translations that are NOT already defined in sprinkle-admin.
 * All standard admin translations (USER, GROUP, ROLE, PERMISSION, ACTIVITY, etc.)
 * are inherited from sprinkle-admin.
 *
 * @author Louis Charette
 */
return [
    // C6Admin-specific panel/breadcrumb translations
    'C6ADMIN_PANEL' => 'Panneau C6Admin',

    // C6Admin-specific success messages for toggle actions
    // Note: sprinkle-admin doesn't have these, so we add them here
    'USER' => [
        'ADMIN' => [
            'TOGGLE_ENABLED_SUCCESS'  => 'Statut de l\'utilisateur mis à jour avec succès',
            'TOGGLE_VERIFIED_SUCCESS' => 'Statut de vérification de l\'utilisateur mis à jour avec succès',
        ],
    ],
];
