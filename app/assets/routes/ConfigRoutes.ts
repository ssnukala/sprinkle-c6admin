export default [
    {
        path: 'config',
        name: 'c6admin.config',
        redirect: { name: 'c6admin.config.info' },
        meta: {
            auth: {},
            title: 'SITE_CONFIG',
            description: 'SITE_CONFIG.PAGE_DESCRIPTION'
        },
        component: () => import('../views/PageConfig.vue'),
        children: [
            {
                path: 'info',
                name: 'c6admin.config.info',
                meta: {
                    permission: {
                        slug: 'c6_view_system_info'
                    }
                },
                component: () => import('../views/PageConfigInfo.vue')
            },
            {
                path: 'cache',
                name: 'c6admin.config.cache',
                meta: {
                    permission: {
                        slug: 'c6_clear_cache'
                    }
                },
                component: () => import('../views/PageConfigCache.vue')
            }
        ]
    }
]
