import { ref } from 'vue'
import axios from 'axios'
import { Severity } from '@userfrosting/sprinkle-core/interfaces'
import type { ApiErrorResponse, ApiResponse } from '@userfrosting/sprinkle-core/interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

// TODO : Add validation - See comment in the PHP action. The API needs to be
// split into sub-api with their own schema first.
// 'schema://requests/user/edit-field.yaml'

/**
 * API Composable for updating user fields.
 * 
 * Uses CRUD6's UpdateFieldAction to update a single field of a user record.
 * For relationship updates (roles, permissions), use the relationship endpoints.
 */
export function useUserUpdateApi() {
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function submitUserUpdate(id: number, fieldName: string, formData: any) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .put<ApiResponse>('/api/crud6/users/' + id + '/' + fieldName, formData)
            .then((response) => {
                useAlertsStore().push({
                    ...{ style: Severity.Success },
                    ...response.data
                })

                return response.data
            })
            .catch((err) => {
                apiError.value = err.response.data
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return { submitUserUpdate, apiLoading, apiError }
}
