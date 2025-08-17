import { ref } from 'vue'
import api from './api'

/**
 * Composable de API para Utilizadores
 * Endpoints esperados (ajuste conforme o backend):
 * - GET    /api/users                     -> listUsers(params)
 * - GET    /api/users/:id                 -> getUser(id)
 * - POST   /api/users                     -> createUser(payload)
 * - PUT    /api/users/:id                 -> updateUser(id, payload)
 * - DELETE /api/users/:id                 -> deleteUser(id)
 * - POST   /api/users/:id/reset-password  -> resetPassword(id)
 * - POST   /api/users/:id/mfa/enable      -> enableMfa(id)
 * - POST   /api/users/:id/mfa/disable     -> disableMfa(id)
 * - POST   /api/users/bulk                -> bulkAction({ action, ids })
 * - POST   /api/users/import              -> importUsers(file)
 * - GET    /api/users/export?format=...   -> exportUsers({ format })
 */
export function useUsers() {
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

  // Listar utilizadores
  const listUsers = async (params = {}) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/users${buildQuery(params)}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao listar utilizadores'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Obter utilizador por ID
  const getUser = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.get(`/users/${encodeURIComponent(id)}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao obter utilizador'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Criar utilizador
  const createUser = async (payload) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post('/users', payload)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao criar utilizador'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Atualizar utilizador
  const updateUser = async (id, payload) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.put(`/users/${encodeURIComponent(id)}`, payload)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao atualizar utilizador'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Eliminar utilizador
  const deleteUser = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.delete(`/users/${encodeURIComponent(id)}`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao eliminar utilizador'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Resetar senha
  const resetPassword = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post(`/users/${encodeURIComponent(id)}/reset-password`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao resetar senha'
      throw e
    } finally {
      loading.value = false
    }
  }

  // MFA: ativar / desativar
  const enableMfa = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post(`/users/${encodeURIComponent(id)}/mfa/enable`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao ativar MFA'
      throw e
    } finally {
      loading.value = false
    }
  }

  const disableMfa = async (id) => {
    loading.value = true
    error.value = null
    try {
      const { data } = await api.post(`/users/${encodeURIComponent(id)}/mfa/disable`)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao desativar MFA'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Ações em massa
  const bulkAction = async ({ action, ids, payload = {} }) => {
    loading.value = true
    error.value = null
    try {
      const body = { action, ids, ...payload }
      const { data } = await api.post('/users/bulk', body)
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao executar ação em massa'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Importar utilizadores (ficheiro)
  const importUsers = async (file, extra = {}) => {
    loading.value = true
    error.value = null
    try {
      const form = new FormData()
      form.append('file', file)
      Object.entries(extra).forEach(([k, v]) => form.append(k, v))

      const { data } = await api.post('/users/import', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      return data
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao importar utilizadores'
      throw e
    } finally {
      loading.value = false
    }
  }

  // Exportar utilizadores (download de ficheiro)
  const exportUsers = async ({ format = 'csv', params = {} } = {}) => {
    loading.value = true
    error.value = null
    try {
      const query = buildQuery({ ...params, format })
      const res = await api.get(`/users/export${query}`, { responseType: 'blob' })
      const blob = res.data
      // Criar download
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `users.${format}`
      document.body.appendChild(a)
      a.click()
      a.remove()
      window.URL.revokeObjectURL(url)
      return true
    } catch (e) {
      error.value = e?.response?.data?.message || e.message || 'Erro ao exportar utilizadores'
      throw e
    } finally {
      loading.value = false
    }
  }

  return {
    // state
    loading,
    error,
    // methods
    listUsers,
    getUser,
    createUser,
    updateUser,
    deleteUser,
    resetPassword,
    enableMfa,
    disableMfa,
    bulkAction,
    importUsers,
    exportUsers,
  }
}
