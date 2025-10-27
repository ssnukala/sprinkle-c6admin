import { ref } from 'vue'
import axios from 'axios'
import { Severity, type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type { UserDeleteResponse } from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

/**
 * API Composable for admin password reset functionality.
 * 
 * This forces a user to reset their password on next login by expiring
 * their current password.
 * 
 * Endpoint: POST /api/users/{id}/password-reset
 */
export function useUserPasswordResetApi() {
    // Form data
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)

    async function passwordReset(id: number) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<UserDeleteResponse>('/api/users/' + id + '/password-reset')
            .then((response) => {
                useAlertsStore().push({
                    title: response.data.title,
                    description: response.data.description,
                    style: Severity.Success
                })
            })
            .catch((err) => {
                apiError.value = err.response.data

                throw apiError.value
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    return { apiLoading, apiError, passwordReset }
}
