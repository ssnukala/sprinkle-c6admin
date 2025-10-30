export default [
    {
        path: 'permissions',
        meta: {
            permission: {
                slug: 'c6_uri_permissions'
            },
            auth: {},
            title: 'PERMISSION.PAGE',
            description: 'PERMISSION.PAGE_DESCRIPTION',
            model: 'permissions' // Model name for CRUD6 components
        },
        children: [
            {
                path: '',
                name: 'c6admin.permissions',
                component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6ListPage),
                beforeEnter: (to) => {
                    to.params.model = 'permissions'
                }
            },
            {
                path: ':id',
                name: 'c6admin.permission',
                component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6DynamicPage),
                beforeEnter: (to) => {
                    to.params.model = 'permissions'
                },
                meta: {
                    description: 'PERMISSION.INFO_PAGE'
                }
            }
        ]
    }
]
