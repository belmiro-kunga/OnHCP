import { ref } from 'vue'
import api from './api'

/**
 * Composable de API para Cargos/Roles
 * Endpoints esperados:
 * - GET    /api/roles                     -> listRoles(params)
 * - GET    /api/roles/options            -> getRoleOptions()
 * - GET    /api/roles/:id                -> getRole(id)
 * - POST   /api/roles                    -> createRole(payload)
 * - PUT    /api/roles/:id                -> updateRole(id, payload)
 * - DELETE /api/roles/:id               -> deleteRole(id)
 * - POST   /api/roles/:id/toggle-status -> toggleRoleStatus(id)
 */
export function useRoles() {
  const loading = ref(false)
  const error = ref(null)

  const buildQuery = (params = {}) => {
    const q = new URLSearchParams()
    Object.entries(params).forEach(([k, v]) => {
      if (v !== undefined && v !== null && v !== '') q.append(k, v)
    })
    const qs = q.toString()
    return qs ? `?${qs}` : ''
  }

  // Listar cargos
  const listRoles = async (params = {}) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/roles${buildQuery(params)}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao listar cargos'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Obter opções de cargos para dropdowns
  const getRoleOptions = async () => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/roles/options')
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao carregar opções de cargos'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Obter cargo por ID
  const getRole = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/roles/${id}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao carregar cargo'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Criar novo cargo
  const createRole = async (payload) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/roles', payload)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao criar cargo'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Atualizar cargo
  const updateRole = async (id, payload) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/roles/${id}`, payload)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao atualizar cargo'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Excluir cargo
  const deleteRole = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.delete(`/roles/${id}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao excluir cargo'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Alternar status do cargo (ativo/inativo)
  const toggleRoleStatus = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post(`/roles/${id}/toggle-status`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao alterar status do cargo'
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    listRoles,
    getRoleOptions,
    getRole,
    createRole,
    updateRole,
    deleteRole,
    toggleRoleStatus
  }
}