import { ref } from 'vue'
import api from './api'

/**
 * Composable de API para Departamentos
 * Endpoints esperados:
 * - GET    /api/departments                     -> listDepartments(params)
 * - GET    /api/departments/options            -> getDepartmentOptions()
 * - GET    /api/departments/potential-managers -> getPotentialManagers()
 * - GET    /api/departments/:id                -> getDepartment(id)
 * - POST   /api/departments                    -> createDepartment(payload)
 * - PUT    /api/departments/:id                -> updateDepartment(id, payload)
 * - DELETE /api/departments/:id               -> deleteDepartment(id)
 * - POST   /api/departments/:id/toggle-status -> toggleDepartmentStatus(id)
 */
export function useDepartments() {
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

  // Listar departamentos
  const listDepartments = async (params = {}) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/departments${buildQuery(params)}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao listar departamentos'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Obter opções de departamentos para dropdowns
  const getDepartmentOptions = async () => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/departments/options')
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao carregar opções de departamentos'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Obter usuários que podem ser gerentes
  const getPotentialManagers = async () => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get('/departments/potential-managers')
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao carregar gerentes potenciais'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Obter departamento por ID
  const getDepartment = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/departments/${id}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao carregar departamento'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Criar novo departamento
  const createDepartment = async (payload) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/departments', payload)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao criar departamento'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Atualizar departamento
  const updateDepartment = async (id, payload) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/departments/${id}`, payload)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao atualizar departamento'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Excluir departamento
  const deleteDepartment = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.delete(`/departments/${id}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao excluir departamento'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Alternar status do departamento (ativo/inativo)
  const toggleDepartmentStatus = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post(`/departments/${id}/toggle-status`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao alterar status do departamento'
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    loading,
    error,
    listDepartments,
    getDepartmentOptions,
    getPotentialManagers,
    getDepartment,
    createDepartment,
    updateDepartment,
    deleteDepartment,
    toggleDepartmentStatus
  }
}