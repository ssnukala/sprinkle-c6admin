import { ref, toValue } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import { Severity, type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type {
    UserCreateRequest,
    UserCreateResponse,
    UserDeleteResponse,
    UserEditRequest,
    UserEditResponse,
    UserResponse
} from '../interfaces'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/user/create.yaml'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'

/**
 * Vue composable for User CRUD operations.
 *
 * Endpoints:
 * - GET    /api/users/u/{user_name}  -> UserResponse
 * - POST   /api/users                -> UserCreateResponse
 * - PUT    /api/users/u/{user_name}  -> UserEditResponse
 * - DELETE /api/users/u/{user_name}  -> UserDeleteResponse
 *
 * Reactive state:
 * - apiLoading: boolean
 * - apiError: ApiErrorResponse | null
 * - formData: UserCreateRequest
 * - r$: validation state from Regle for formData
 *
 * Methods:
 * - fetchUser(id: number): Promise<UserResponse>
 * - createUser(data: UserCreateRequest): Promise<void>
 * - updateUser(id: number, data: UserEditRequest): Promise<void>
 * - deleteUser(id: number): Promise<void>
 * - resetForm(): void
 */
export function useUserApi() {
    const defaultFormData = (): UserCreateRequest => ({
        user_name: '',
        group_id: 0,
        first_name: '',
        last_name: '',
        email: '',
        locale: 'users'
    })

    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>()
    const formData = ref<UserCreateRequest>(defaultFormData())

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    async function fetchUser(id: number) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .get<UserResponse>('/api/crud6/users/' + toValue(id))
            .then((response) => {
                return response.data
            })
            .catch((err) => {
                apiError.value = err.response.data

                throw apiError.value
            })
            .finally(() => {
                apiLoading.value = false
            })
    }

    async function createUser(data: UserCreateRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<UserCreateResponse>('/api/crud6/users', data)
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

    async function updateUser(id: number, data: UserEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .put<UserEditResponse>('/api/crud6/users/' + id, data)
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

    async function deleteUser(id: number) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .delete<UserDeleteResponse>('/api/crud6/users/' + id)
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

    function resetForm() {
        formData.value = defaultFormData()
    }

    return {
        fetchUser,
        createUser,
        updateUser,
        deleteUser,
        apiError,
        apiLoading,
        formData,
        r$,
        resetForm
    }
}
