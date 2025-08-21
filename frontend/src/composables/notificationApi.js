import { api } from './api'

/**
 * API service para gerenciar notificações
 */
export const notificationApi = {
  /**
   * Lista notificações do usuário
   * @param {Object} params - Parâmetros de filtro
   * @param {boolean} params.unread_only - Apenas não lidas
   * @param {number} params.limit - Limite de resultados
   * @param {number} params.offset - Offset para paginação
   * @param {string} params.type - Tipo de notificação
   * @returns {Promise<Object>} Lista de notificações e metadados
   */
  async list(params = {}) {
    const { data } = await api.get('/notifications', { params })
    return data
  },

  /**
   * Obtém contagem de notificações não lidas
   * @returns {Promise<Object>} Contagem de não lidas
   */
  async getUnreadCount() {
    const { data } = await api.get('/notifications/unread-count')
    return data
  },

  /**
   * Marca uma notificação como lida
   * @param {number} id - ID da notificação
   * @returns {Promise<Object>} Resposta da API
   */
  async markAsRead(id) {
    const { data } = await api.patch(`/notifications/${id}/read`)
    return data
  },

  /**
   * Marca todas as notificações como lidas
   * @returns {Promise<Object>} Resposta da API
   */
  async markAllAsRead() {
    const { data } = await api.post('/notifications/mark-all-read')
    return data
  },

  /**
   * Remove uma notificação
   * @param {number} id - ID da notificação
   * @returns {Promise<Object>} Resposta da API
   */
  async remove(id) {
    const { data } = await api.delete(`/notifications/${id}`)
    return data
  },

  /**
   * Obtém estatísticas de notificações
   * @returns {Promise<Object>} Estatísticas
   */
  async getStats() {
    const { data } = await api.get('/notifications/stats')
    return data
  },

  /**
   * Obtém notificações não lidas (atalho)
   * @param {number} limit - Limite de resultados
   * @returns {Promise<Object>} Lista de notificações não lidas
   */
  async getUnread(limit = 10) {
    return this.list({ unread_only: true, limit })
  },

  /**
   * Obtém notificações por tipo
   * @param {string} type - Tipo de notificação
   * @param {number} limit - Limite de resultados
   * @returns {Promise<Object>} Lista de notificações filtradas
   */
  async getByType(type, limit = 20) {
    return this.list({ type, limit })
  }
}

export default notificationApi