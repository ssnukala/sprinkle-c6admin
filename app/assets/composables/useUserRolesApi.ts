import { ref } from 'vue'
import axios from 'axios'
import { type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { RoleInterface } from '@userfrosting/sprinkle-account/interfaces'
import type { RolesSprunjeResponse, UserRoleSprunjeResponse } from '../interfaces'

/**
 * API used to fetch a match between all available roles and the user's role,
 * in a single component
 *
 * This API is tied to the `RolesSprunje` and `UserRoleSprunje` API, accessed at
 * the GET `/api/crud6/roles` and `/api/crud6/users/{id}/roles` endpoints.
 *
 * This composable accept a {id} to select the roles of a specific user.
 */
export function useUserRolesApi() {
    const loading = ref<boolean>(false)
    const error = ref<ApiErrorResponse | null>()
    const selected = ref<number[]>([])
    const roles = ref<RoleInterface[]>([])

    // Step 1 - Fetch all permissions
    async function fetch(id: number) {
        loading.value = true
        axios
            .get<RolesSprunjeResponse>('/api/crud6/roles')
            .then((response) => {
                roles.value = response.data.rows
                fetchUserRoles(id)
            })
            .catch((err) => {
                loading.value = false
                error.value = err.response.data
            })
    }

    // Step 2 - Fetch role permissions and match them with the permissions
    async function fetchUserRoles(id: number) {
        axios
            .get<UserRoleSprunjeResponse>('/api/crud6/users/' + id + '/roles')
            .then((response) => {
                // Empty the selected array
                selected.value.splice(0)

                // Match the permissions with the role permissions
                const userRoles: RoleInterface[] = response.data.rows
                userRoles.forEach((userRole) => {
                    const record = roles.value.find((element) => element.id === userRole.id)
                    if (record) {
                        selected.value.push(userRole.id)
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

    return { error, loading, fetch, selected, roles }
}
