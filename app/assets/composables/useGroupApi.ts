import { ref, toValue, watch } from 'vue'
import axios from 'axios'
import { useRegle } from '@regle/core'
import slug from 'limax'
import { Severity, type ApiErrorResponse } from '@userfrosting/sprinkle-core/interfaces'
import type {
    GroupCreateRequest,
    GroupCreateResponse,
    GroupDeleteResponse,
    GroupEditRequest,
    GroupEditResponse,
    GroupResponse
} from '../interfaces'
import { useAlertsStore } from '@userfrosting/sprinkle-core/stores'
import { useRuleSchemaAdapter } from '@userfrosting/sprinkle-core/composables'
import schemaFile from '../../schema/requests/group.yaml'

/**
 * Vue composable for Group CRUD operations.
 *
 * Endpoints (via CRUD6):
 * - GET    /api/crud6/groups/{id}  -> GroupResponse
 * - POST   /api/crud6/groups       -> GroupCreateResponse
 * - PUT    /api/crud6/groups/{id}  -> GroupEditResponse
 * - DELETE /api/crud6/groups/{id}  -> GroupDeleteResponse
 *
 * Reactive state:
 * - apiLoading: boolean
 * - apiError: ApiErrorResponse | null
 * - formData: GroupCreateRequest
 * - r$: validation state from Regle for formData
 *
 * Methods:
 * - fetchGroup(id: number): Promise<GroupResponse>
 * - createGroup(data: GroupCreateRequest): Promise<void>
 * - updateGroup(id: number, data: GroupEditRequest): Promise<void>
 * - deleteGroup(id: number): Promise<void>
 * - resetForm(): void
 */
export function useGroupApi() {
    const defaultFormData = (): GroupCreateRequest => ({
        slug: '',
        name: '',
        description: '',
        icon: 'users'
    })

    const slugLocked = ref<boolean>(true)
    const apiLoading = ref<boolean>(false)
    const apiError = ref<ApiErrorResponse | null>(null)
    const formData = ref<GroupCreateRequest>(defaultFormData())

    // Load the schema and set up the validator
    const { r$ } = useRegle(formData, useRuleSchemaAdapter().adapt(schemaFile))

    async function fetchGroup(id: number) {
        apiLoading.value = true
        apiError.value = null

        return axios
            .get<GroupResponse>('/api/crud6/groups/' + toValue(id))
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

    async function createGroup(data: GroupCreateRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .post<GroupCreateResponse>('/api/crud6/groups', data)
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

    async function updateGroup(id: number, data: GroupEditRequest) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .put<GroupEditResponse>('/api/crud6/groups/' + id, data)
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

    async function deleteGroup(id: number) {
        apiLoading.value = true
        apiError.value = null
        return axios
            .delete<GroupDeleteResponse>('/api/crud6/groups/' + id)
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
        (name) => {
            if (slugLocked.value) {
                formData.value.slug = slug(name)
            }
        }
    )

    return {
        fetchGroup,
        createGroup,
        updateGroup,
        deleteGroup,
        apiLoading,
        apiError,
        formData,
        r$,
        resetForm,
        slugLocked
    }
}
