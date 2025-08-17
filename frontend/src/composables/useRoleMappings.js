import { ref } from 'vue'
import api from './api'

export function useRoleMappings() {
  const loading = ref(false)
  const error = ref(null)

  const list = async () => {
    loading.value = true; error.value = null
    try {
      const { data } = await api.get('/role-mappings')
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  const create = async (payload) => {
    loading.value = true; error.value = null
    try {
      const { data } = await api.post('/role-mappings', payload)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  const update = async (id, payload) => {
    loading.value = true; error.value = null
    try {
      const { data } = await api.put(`/role-mappings/${id}`, payload)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  const remove = async (id) => {
    loading.value = true; error.value = null
    try {
      const { data } = await api.delete(`/role-mappings/${id}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  const reorder = async (items) => {
    loading.value = true; error.value = null
    try {
      const { data } = await api.post('/role-mappings/reorder', { items })
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message
      throw e
    } finally {
      loading.value = false
    }
  }

  return { loading, error, list, create, update, remove, reorder }
}
