<template>
    <UFSideBarItem
        v-if="$checkAccess('c6_uri_dashboard')"
        :to="{ name: 'c6admin.dashboard' }"
        faIcon="gauge-high"
        :label="$t('DASHBOARD')" />
    <UFSideBarItem
        v-if="$checkAccess('c6_uri_users')"
        :to="{ name: 'c6admin.users' }"
        faIcon="user"
        :label="$t('USER.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('c6_uri_activities')"
        :to="{ name: 'c6admin.activities' }"
        faIcon="list-check"
        :label="$t('ACTIVITY.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('c6_uri_roles')"
        :to="{ name: 'c6admin.roles' }"
        faIcon="address-card"
        :label="$t('ROLE.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('c6_uri_permissions')"
        :to="{ name: 'c6admin.permissions' }"
        faIcon="key"
        :label="$t('PERMISSION.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('c6_uri_groups')"
        :to="{ name: 'c6admin.groups' }"
        faIcon="users"
        :label="$t('GROUP.PAGE')" />
    <UFSideBarItem
        v-if="$checkAccess('c6_view_system_info') || $checkAccess('c6_clear_cache')"
        :to="{ name: 'c6admin.config' }"
        faIcon="gear"
        :label="$t('SITE_CONFIG')" />
</template>
