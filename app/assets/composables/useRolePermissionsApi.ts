import { ref } from 'vue'
import axios from 'axios'
import type { ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { PermissionInterface } from '@userfrosting/sprinkle-account/interfaces'
import type { PermissionSprunjeResponse, RolePermissionsSprunjeResponse } from '../interfaces'

/**
 * API used to fetch a match between all available permissions and the role's
 * permissions, in a single component
 *
 * This API is tied to the `PermissionsSprunje` and `RolePermissionsSprunje` API,
 *  accessed at the GET `/api/crud6/permissions` and
 * `/api/crud6/roles/{id}/permissions` endpoints.
 *
 * This composable accept a {id} to select the permissions of a specific
 * role.
 */
export function useRolePermissionsApi() {
    const loading = ref<boolean>(false)
    const error = ref<ApiErrorResponse | null>()
    const selected = ref<number[]>([])
    const permissions = ref<PermissionInterface[]>([])

    // Step 1 - Fetch all permissions
    async function fetch(id: number) {
        loading.value = true
        axios
            .get<PermissionSprunjeResponse>('/api/crud6/permissions')
            .then((response) => {
                permissions.value = response.data.rows
                fetchRolePermissions(id)
            })
            .catch((err) => {
                loading.value = false
                error.value = err.response.data
            })
    }

    // Step 2 - Fetch role permissions and match them with the permissions
    async function fetchRolePermissions(id: number) {
        axios
            .get<RolePermissionsSprunjeResponse>('/api/crud6/roles/' + id + '/permissions')
            .then((response) => {
                // Empty the selected array
                selected.value.splice(0)

                // Match the permissions with the role permissions
                const rolePermissions: PermissionInterface[] = response.data.rows
                rolePermissions.forEach((rolePermission) => {
                    const record = permissions.value.find(
                        (element) => element.id === rolePermission.id
                    )
                    if (record) {
                        selected.value.push(rolePermission.id)
                    }
                })
            })
            .catch((err) => {
                error.value = err.response.data
            })
            .finally(() => {
                loading.value = false
            })
    }

    return { error, loading, fetch, selected, permissions }
}
