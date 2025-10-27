export default [
    {
        path: 'roles',
        meta: {
            auth: {},
            title: 'ROLE.PAGE',
            description: 'ROLE.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'c6admin.roles',
                meta: {
                    permission: {
                        slug: 'c6_uri_roles'
                    }
                },
                component: () => import('../views/PageRoles.vue')
            },
            {
                path: ':id',
                name: 'c6admin.role',
                meta: {
                    title: 'ROLE.PAGE',
                    description: 'ROLE.INFO_PAGE',
                    permission: {
                        slug: 'c6_uri_role'
                    }
                },
                component: () => import('../views/PageRole.vue')
            }
        ]
    }
]
