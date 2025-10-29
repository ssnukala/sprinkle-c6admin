export default [
    {
        path: 'c6/admin/users',
        meta: {
            auth: {},
            title: 'USER.PAGE',
            description: 'USER.PAGE_DESCRIPTION'
        },
        children: [
            {
                path: '',
                name: 'c6admin.users',
                meta: {
                    permission: {
                        slug: 'c6_uri_users'
                    }
                },
                component: () => import('../views/PageUsers.vue')
            },
            {
                path: ':id',
                name: 'c6admin.user',
                meta: {
                    title: 'USER.PAGE',
                    description: 'USER.INFO_PAGE',
                    permission: {
                        slug: 'c6_uri_user'
                    }
                },
                component: () => import('../views/PageUser.vue')
            }
        ]
    }
]
