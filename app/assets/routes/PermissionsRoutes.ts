export default [
    {
        path: 'permissions',
        meta: {
            permission: {
                slug: 'c6_uri_permissions'
            },
            auth: {},
            title: 'PERMISSION.PAGE',
            description: 'PERMISSION.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'c6admin.permissions',
                component: () => import('../views/PagePermissions.vue')
            },
            {
                path: ':id',
                name: 'c6admin.permission',
                component: () => import('../views/PagePermission.vue'),
                meta: {
                    description: 'PERMISSION.INFO_PAGE'
                }
            }
        ]
    }
]
