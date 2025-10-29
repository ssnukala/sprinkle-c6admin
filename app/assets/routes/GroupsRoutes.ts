export default [
    {
        path: 'c6/admin/groups',
        meta: {
            auth: {},
            title: 'GROUP.PAGE',
            description: 'GROUP.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'c6admin.groups',
                meta: {
                    permission: {
                        slug: 'c6_uri_groups'
                    }
                },
                component: () => import('../views/PageGroups.vue')
            },
            {
                path: ':id',
                name: 'c6admin.group',
                meta: {
                    title: 'GROUP.PAGE',
                    description: 'GROUP.INFO_PAGE',
                    permission: {
                        slug: 'c6_uri_group'
                    }
                },
                component: () => import('../views/PageGroup.vue')
            }
        ]
    }
]
