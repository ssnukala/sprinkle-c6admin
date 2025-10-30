export default [
    {
        path: 'activities',
        name: 'c6admin.activities',
        meta: {
            auth: {},
            permission: {
                slug: 'c6_uri_activities'
            },
            title: 'ACTIVITY.PAGE',
            description: 'ACTIVITY.PAGE_DESCRIPTION',
            model: 'activities' // Model name for CRUD6 components
        },
        component: () => import('@ssnukala/sprinkle-crud6/views').then(m => m.CRUD6ListPage),
        beforeEnter: (to) => {
            to.params.model = 'activities'
        }
    }
]
