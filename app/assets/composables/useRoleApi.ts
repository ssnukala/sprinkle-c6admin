import { ref, toValue, watch } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import slug from 'limax'
import { Severity, type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type {
    RoleCreateResponse,
    RoleCreateRequest,
    RoleEditRequest,
    RoleEditResponse,
    RoleDeleteResponse,
    RoleResponse
} from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/role.yaml'

/**
 * Vue composable for Role CRUD operations.
 *
 * Endpoints (via CRUD6):
 * - GET    /api/crud6/roles/{id}  -> RoleResponse
 * - POST   /api/crud6/roles       -> RoleCreateResponse
 * - PUT    /api/crud6/roles/{id}  -> RoleEditResponse
 * - DELETE /api/crud6/roles/{id}  -> RoleDeleteResponse
 *
 * Reactive state:
 * - apiLoading: boolean
 * - apiError: ApiErrorResponse | null
 * - formData: RoleCreateRequest
 * - r$: validation state from Regle for formData
 *
 * Methods:
 * - fetchRole(id: number): Promise<RoleResponse>
 * - createRole(data: RoleCreateRequest): Promise<void>
 * - updateRole(id: number, data: RoleEditRequest): Promise<void>
 * - deleteRole(id: number): Promise<void>
 * - resetForm(): void
 */
export function useRoleApi() {
    const defaultFormData = (): RoleCreateRequest => ({
        name: '',
        slug: '',
        description: ''
    })

    const slugLocked = ref<boolean>(true)
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)
    const formData = ref<RoleCreateRequest>(defaultFormData())

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    async function fetchRole(id: number) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .get<RoleResponse>('/api/crud6/roles/' + toValue(id))
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

    async function createRole(data: RoleCreateRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<RoleCreateResponse>('/api/crud6/roles', data)
            .then((response) => {
                // Add the message to the alert stream
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

    async function updateRole(id: number, data: RoleEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .put<RoleEditResponse>('/api/crud6/roles/' + id, data)
            .then((response) => {
                // Add the message to the alert stream
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

    async function deleteRole(id: number) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .delete<RoleDeleteResponse>('/api/crud6/roles/' + id)
            .then((response) => {
                // Add the message to the alert stream
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

    watch(
        () => formData.value.name,
        (newName) => {
            if (slugLocked.value) {
                formData.value.slug = slug(newName)
            }
        }
    )

    return {
        fetchRole,
        createRole,
        updateRole,
        deleteRole,
        apiLoading,
        apiError,
        formData,
        r$,
        resetForm,
        slugLocked
    }
}
