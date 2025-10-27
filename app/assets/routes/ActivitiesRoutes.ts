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
            description: 'ACTIVITY.PAGE_DESCRIPTION'
        },
        component: () => import('../views/PageActivities.vue')
    }
]
