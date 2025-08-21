import { ref, computed, onMounted, onUnmounted } from 'vue'
import { notificationApi } from './notificationApi'

// Estado global das notificações (singleton)
const notifications = ref([])
const unreadCount = ref(0)
const loading = ref(false)
const error = ref(null)
const lastFetch = ref(null)

// Configurações
const REFRESH_INTERVAL = 30000 // 30 segundos
const MAX_NOTIFICATIONS = 50

let refreshTimer = null
let isInitialized = false

/**
 * Composable para gerenciar notificações
 */
export function useNotifications() {
  // Computed properties
  const hasUnread = computed(() => unreadCount.value > 0)
  const recentNotifications = computed(() => 
    notifications.value.slice(0, 10)
  )
  const unreadNotifications = computed(() => 
    notifications.value.filter(n => !n.is_read)
  )

  /**
   * Carrega notificações da API
   */
  const fetchNotifications = async (params = {}) => {
    if (loading.value) return
    
    loading.value = true
    error.value = null
    
    try {
      const response = await notificationApi.list({
        limit: MAX_NOTIFICATIONS,
        ...params
      })
      
      notifications.value = response.notifications || []
      unreadCount.value = response.unread_count || 0
      lastFetch.value = new Date()
      
      return response
    } catch (err) {
      error.value = err.message || 'Erro ao carregar notificações'
      console.error('Erro ao buscar notificações:', err)
      throw err
    } finally {
      loading.value = false
    }
  }

  /**
   * Atualiza apenas a contagem de não lidas
   */
  const refreshUnreadCount = async () => {
    try {
      const response = await notificationApi.getUnreadCount()
      unreadCount.value = response.unread_count || 0
    } catch (err) {
      console.error('Erro ao atualizar contagem:', err)
    }
  }

  /**
   * Marca uma notificação como lida
   */
  const markAsRead = async (notificationId) => {
    try {
      const response = await notificationApi.markAsRead(notificationId)
      
      // Atualizar estado local
      const notification = notifications.value.find(n => n.id === notificationId)
      if (notification && !notification.is_read) {
        notification.is_read = true
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
      
      return response
    } catch (err) {
      error.value = err.message || 'Erro ao marcar como lida'
      throw err
    }
  }

  /**
   * Marca todas as notificações como lidas
   */
  const markAllAsRead = async () => {
    try {
      const response = await notificationApi.markAllAsRead()
      
      // Atualizar estado local
      notifications.value.forEach(notification => {
        if (!notification.is_read) {
          notification.is_read = true
          notification.read_at = new Date().toISOString()
        }
      })
      unreadCount.value = 0
      
      return response
    } catch (err) {
      error.value = err.message || 'Erro ao marcar todas como lidas'
      throw err
    }
  }

  /**
   * Remove uma notificação
   */
  const removeNotification = async (notificationId) => {
    try {
      const response = await notificationApi.remove(notificationId)
      
      // Atualizar estado local
      const index = notifications.value.findIndex(n => n.id === notificationId)
      if (index !== -1) {
        const notification = notifications.value[index]
        if (!notification.is_read) {
          unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
        notifications.value.splice(index, 1)
      }
      
      return response
    } catch (err) {
      error.value = err.message || 'Erro ao remover notificação'
      throw err
    }
  }

  /**
   * Adiciona uma nova notificação (para uso em tempo real)
   */
  const addNotification = (notification) => {
    // Adicionar no início da lista
    notifications.value.unshift({
      ...notification,
      is_read: false,
      created_at: notification.created_at || new Date().toISOString()
    })
    
    // Manter limite máximo
    if (notifications.value.length > MAX_NOTIFICATIONS) {
      notifications.value = notifications.value.slice(0, MAX_NOTIFICATIONS)
    }
    
    // Atualizar contagem
    if (!notification.is_read) {
      unreadCount.value += 1
    }
  }

  /**
   * Filtra notificações por tipo
   */
  const getNotificationsByType = (type) => {
    return notifications.value.filter(n => n.type === type)
  }

  /**
   * Obtém notificações por prioridade
   */
  const getNotificationsByPriority = (priority) => {
    return notifications.value.filter(n => n.priority === priority)
  }

  /**
   * Inicia o refresh automático
   */
  const startAutoRefresh = () => {
    if (refreshTimer) return
    
    refreshTimer = setInterval(() => {
      refreshUnreadCount()
    }, REFRESH_INTERVAL)
  }

  /**
   * Para o refresh automático
   */
  const stopAutoRefresh = () => {
    if (refreshTimer) {
      clearInterval(refreshTimer)
      refreshTimer = null
    }
  }

  /**
   * Inicializa o sistema de notificações
   */
  const initialize = async () => {
    if (isInitialized) return
    
    try {
      await fetchNotifications()
      startAutoRefresh()
      isInitialized = true
    } catch (err) {
      console.error('Erro ao inicializar notificações:', err)
    }
  }

  /**
   * Limpa o estado das notificações
   */
  const clear = () => {
    notifications.value = []
    unreadCount.value = 0
    error.value = null
    lastFetch.value = null
    stopAutoRefresh()
    isInitialized = false
  }

  // Lifecycle hooks
  onMounted(() => {
    if (!isInitialized) {
      initialize()
    }
  })

  onUnmounted(() => {
    // Manter o timer rodando para outros componentes
    // stopAutoRefresh()
  })

  return {
    // Estado
    notifications: computed(() => notifications.value),
    unreadCount: computed(() => unreadCount.value),
    loading: computed(() => loading.value),
    error: computed(() => error.value),
    lastFetch: computed(() => lastFetch.value),
    
    // Computed
    hasUnread,
    recentNotifications,
    unreadNotifications,
    
    // Métodos
    fetchNotifications,
    refreshUnreadCount,
    markAsRead,
    markAllAsRead,
    removeNotification,
    addNotification,
    getNotificationsByType,
    getNotificationsByPriority,
    initialize,
    clear,
    startAutoRefresh,
    stopAutoRefresh
  }
}

export default useNotifications