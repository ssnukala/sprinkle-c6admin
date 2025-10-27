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
                name: 'admin.roles',
                meta: {
                    permission: {
                        slug: 'uri_roles'
                    }
                },
                component: () => import('../views/PageRoles.vue')
            },
            {
                path: ':id',
                name: 'admin.role',
                meta: {
                    title: 'ROLE.PAGE',
                    description: 'ROLE.INFO_PAGE',
                    permission: {
                        slug: 'uri_role'
                    }
                },
                component: () => import('../views/PageRole.vue')
            }
        ]
    }
]
