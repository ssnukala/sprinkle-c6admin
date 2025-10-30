export default [
    {
        path: 'groups',
        meta: {
            auth: {},
            title: 'GROUP.PAGE',
            description: 'GROUP.PAGE_DESCRIPTION',
            model: 'groups' // Model name for CRUD6 components
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
                component: () => import('../views/PageList.vue'),
                beforeEnter: (to) => {
                    to.params.model = 'groups'
                }
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
                component: () => import('../views/PageDynamic.vue'),
                beforeEnter: (to) => {
                    to.params.model = 'groups'
                }
            }
        ]
    }
]
