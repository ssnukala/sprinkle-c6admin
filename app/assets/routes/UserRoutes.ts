export default [
    {
        path: 'users',
        meta: {
            auth: {},
            title: 'USER.PAGE',
            description: 'USER.PAGE_DESCRIPTION',
            model: 'users' // Model name for CRUD6 components
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
                component: () => import('../views/PageList.vue'),
                // Pass model as a route param
                beforeEnter: (to) => {
                    to.params.model = 'users'
                }
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
                component: () => import('../views/PageDynamic.vue'),
                beforeEnter: (to) => {
                    to.params.model = 'users'
                }
            }
        ]
    }
]
