export default [
    {
        path: 'dashboard',
        name: 'c6admin.dashboard',
        meta: {
            auth: {},
            permission: {
                slug: 'c6_uri_dashboard'
            },
            title: 'DASHBOARD'
        },
        component: () => import('../views/PageDashboard.vue')
    }
]
