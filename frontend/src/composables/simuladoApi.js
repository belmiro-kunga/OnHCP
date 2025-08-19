import { api } from './api'

// Simulado API service (v1)
// All methods return data or throw an error already normalized
export const simuladoApi = {
  async list() {
    const { data } = await api.get('/simulados')
    return data
  },

  async get(id) {
    const { data } = await api.get(`/simulados/${id}`)
    return data
  },

  async startAttempt(simuladoId, { resume = false } = {}) {
    const { data } = await api.post(`/simulados/${simuladoId}/attempts`, { resume })
    return data
  },

  async getAttempt(attemptId) {
    const { data } = await api.get(`/attempts/${attemptId}`)
    return data
  },

  async saveAttempt(attemptId, payload) {
    // payload: { currentQuestion, answers, timeRemaining }
    const { data } = await api.patch(`/attempts/${attemptId}`, payload)
    return data
  },

  async submitAttempt(attemptId, answers = null) {
    const { data } = await api.post(`/attempts/${attemptId}/submit`, answers ? { answers } : {})
    return data
  },

  async getResult(attemptId) {
    const { data } = await api.get(`/attempts/${attemptId}/result`)
    return data
  },

  async listAttempts(simuladoId, params = {}) {
    const { data } = await api.get(`/simulados/${simuladoId}/attempts`, { params })
    return data
  },

  async issueCertificate(simuladoId, attemptId) {
    const { data } = await api.post('/certificates', { simuladoId, attemptId })
    return data
  },

  async verifyCertificate(code) {
    const { data } = await api.get('/certificates/verify', { params: { code } })
    return data
  },
}
