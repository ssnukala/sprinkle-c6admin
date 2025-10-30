export default [
    {
        path: 'roles',
        meta: {
            auth: {},
            title: 'ROLE.PAGE',
            description: 'ROLE.PAGE_DESCRIPTION',
            model: 'roles' // Model name for CRUD6 components
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
                component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6ListPage),
                beforeEnter: (to) => {
                    to.params.model = 'roles'
                }
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
                component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6DynamicPage),
                beforeEnter: (to) => {
                    to.params.model = 'roles'
                }
            }
        ]
    }
]
