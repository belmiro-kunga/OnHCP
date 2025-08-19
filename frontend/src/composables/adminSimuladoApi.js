import { api } from './api'

export const adminSimuladoApi = {
  async list(params = {}) {
    const { data } = await api.get('/admin/simulados', { params })
    return data
  },
  async get(id) {
    const { data } = await api.get(`/admin/simulados/${id}`)
    return data
  },
  async create(payload) {
    const { data } = await api.post('/admin/simulados', payload)
    return data
  },
  async update(id, payload) {
    const { data } = await api.put(`/admin/simulados/${id}`, payload)
    return data
  },
  async remove(id) {
    const { data } = await api.delete(`/admin/simulados/${id}`)
    return data
  },
  async listAssignments(simuladoId) {
    const { data } = await api.get(`/admin/simulados/${simuladoId}/assignments`)
    return data
  },
  async createAssignment(simuladoId, payload) {
    const { data } = await api.post(`/admin/simulados/${simuladoId}/assignments`, payload)
    return data
  },
  async deleteAssignment(assignmentId) {
    const { data } = await api.delete(`/admin/assignments/${assignmentId}`)
    return data
  },

  // Categories
  async listCategories() {
    const { data } = await api.get('/admin/simulado-categories')
    return data
  },
  async createCategory(name) {
    const { data } = await api.post('/admin/simulado-categories', { name })
    return data
  }
}
