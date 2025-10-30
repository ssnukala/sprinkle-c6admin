// C6Admin Views
// 
// CRUD views (users, groups, roles, permissions, activities) now use
// CRUD6 dynamic templates (PageList and PageDynamic) instead of separate files.
// These are imported directly in the route definitions.
//
// Only utility pages (Dashboard, Config) are exported here.

import DashboardView from './PageDashboard.vue'

// CRUD6 Dynamic Templates (exported for reuse)
import PageList from './PageList.vue'
import PageDynamic from './PageDynamic.vue'
import PageRow from './PageRow.vue'
import PageMasterDetail from './PageMasterDetail.vue'

export {
    DashboardView,
    // CRUD6 Templates
    PageList,
    PageDynamic,
    PageRow,
    PageMasterDetail
}

