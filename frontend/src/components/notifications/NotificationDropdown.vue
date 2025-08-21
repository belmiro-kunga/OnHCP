<template>
  <div class="relative">
    <!-- Notification Bell Button -->
    <button 
      @click="toggleDropdown"
      class="relative p-2 rounded-md hover:bg-gray-100 transition-colors"
      :class="{ 
        'has-notifications': hasUnread,
        'connecting': isConnecting,
        'disconnected': !isConnected && !isConnecting
      }"
      aria-label="Notificações"
      :title="getButtonTitle()"
    >
      <svg class="w-5 h-5 text-text-secondary" fill="currentColor" viewBox="0 0 20 20">
        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
      </svg>
      
      <!-- Unread Badge -->
      <span 
        v-if="hasUnread" 
        class="absolute -top-1 -right-1 w-5 h-5 bg-error text-white text-xs rounded-full flex items-center justify-center font-medium"
      >
        {{ unreadCount > 99 ? '99+' : unreadCount }}
      </span>
      
      <!-- Indicador de status da conexão -->
      <span 
        v-if="isConnecting" 
        class="connection-indicator connecting"
        title="Conectando..."
      ></span>
      <span 
        v-else-if="!isConnected" 
        class="connection-indicator disconnected"
        title="Desconectado - Notificações em tempo real indisponíveis"
      ></span>
      <span 
        v-else 
        class="connection-indicator connected"
        title="Conectado - Notificações em tempo real ativas"
      ></span>
    </button>

    <!-- Dropdown Panel -->
    <div 
      v-if="isOpen" 
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Notificações</h3>
        <div class="flex items-center space-x-2">
          <button 
            v-if="hasUnread"
            @click="handleMarkAllAsRead"
            class="text-sm text-primary hover:text-primary-dark transition-colors"
            :disabled="loading"
          >
            Marcar todas como lidas
          </button>
          <button 
            @click="closeDropdown"
            class="text-gray-400 hover:text-gray-600 transition-colors"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="p-4 text-center">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary mx-auto"></div>
        <p class="text-sm text-gray-500 mt-2">Carregando notificações...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="p-4 text-center">
        <div class="text-error mb-2">
          <svg class="w-6 h-6 mx-auto" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <p class="text-sm text-gray-600">{{ error }}</p>
        <button 
          @click="handleRefresh"
          class="mt-2 text-sm text-primary hover:text-primary-dark transition-colors"
        >
          Tentar novamente
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="notifications.length === 0" class="p-8 text-center">
        <div class="text-gray-400 mb-3">
          <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
          </svg>
        </div>
        <p class="text-gray-600 font-medium">Nenhuma notificação</p>
        <p class="text-sm text-gray-500">Você está em dia!</p>
      </div>

      <!-- Notifications List -->
      <div v-else class="max-h-96 overflow-y-auto">
        <div 
          v-for="notification in displayNotifications" 
          :key="notification.id"
          class="notification-item border-b border-gray-100 last:border-b-0 hover:bg-gray-50 transition-colors"
          :class="{ 'bg-blue-50': !notification.is_read }"
        >
          <div class="p-4">
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <!-- Priority Indicator -->
                <div class="flex items-center mb-1">
                  <span 
                    v-if="notification.priority === 'urgent'"
                    class="inline-block w-2 h-2 bg-red-500 rounded-full mr-2"
                  ></span>
                  <span 
                    v-else-if="notification.priority === 'high'"
                    class="inline-block w-2 h-2 bg-orange-500 rounded-full mr-2"
                  ></span>
                  <span 
                    v-else
                    class="inline-block w-2 h-2 bg-gray-300 rounded-full mr-2"
                  ></span>
                  
                  <h4 class="text-sm font-medium text-gray-900 truncate">
                    {{ notification.title }}
                  </h4>
                  
                  <!-- Unread Indicator -->
                  <span 
                    v-if="!notification.is_read"
                    class="ml-2 w-2 h-2 bg-primary rounded-full flex-shrink-0"
                  ></span>
                </div>
                
                <p class="text-sm text-gray-600 line-clamp-2 mb-2">
                  {{ notification.message }}
                </p>
                
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">
                    {{ formatTime(notification.created_at) }}
                  </span>
                  
                  <span 
                    v-if="notification.type_label"
                    class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full"
                  >
                    {{ notification.type_label }}
                  </span>
                </div>
              </div>
              
              <!-- Actions -->
              <div class="flex items-center space-x-1 ml-3">
                <button 
                  v-if="!notification.is_read"
                  @click="handleMarkAsRead(notification.id)"
                  class="p-1 text-gray-400 hover:text-primary transition-colors"
                  title="Marcar como lida"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                  </svg>
                </button>
                
                <button 
                  @click="handleRemove(notification.id)"
                  class="p-1 text-gray-400 hover:text-error transition-colors"
                  title="Remover notificação"
                >
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="notifications.length > 0" class="p-3 border-t border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
          <span class="text-xs text-gray-500">
            {{ notifications.length }} notificações
          </span>
          <button 
            @click="handleViewAll"
            class="text-xs text-primary hover:text-primary-dark transition-colors font-medium"
          >
            Ver todas
          </button>
        </div>
      </div>
    </div>

    <!-- Backdrop -->
    <div 
      v-if="isOpen" 
      @click="closeDropdown"
      class="fixed inset-0 z-40"
    ></div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useNotifications } from '../../composables/useNotifications'
import { useRealtimeNotifications } from '@/composables/useRealtimeNotifications'

export default {
  name: 'NotificationDropdown',
  setup() {
    const isOpen = ref(false)
    const {
      notifications,
      unreadCount,
      loading,
      error,
      hasUnread,
      recentNotifications,
      fetchNotifications,
      markAsRead,
      markAllAsRead,
      removeNotification,
      initialize
    } = useNotifications()
    
    const {
      isConnected,
      isConnecting,
      connectionError,
      handleNotificationClick
    } = useRealtimeNotifications()

    // Mostrar apenas as 10 mais recentes no dropdown
    const displayNotifications = computed(() => recentNotifications.value)

    const toggleDropdown = async () => {
      if (!isOpen.value) {
        // Atualizar notificações ao abrir
        await fetchNotifications()
      }
      isOpen.value = !isOpen.value
    }

    const closeDropdown = () => {
      isOpen.value = false
    }

    const handleMarkAsRead = async (notificationId) => {
      try {
        await markAsRead(notificationId)
      } catch (err) {
        console.error('Erro ao marcar como lida:', err)
      }
    }

    const handleMarkAllAsRead = async () => {
      try {
        await markAllAsRead()
      } catch (err) {
        console.error('Erro ao marcar todas como lidas:', err)
      }
    }

    const handleRemove = async (notificationId) => {
      try {
        await removeNotification(notificationId)
      } catch (err) {
        console.error('Erro ao remover notificação:', err)
      }
    }

    const handleRefresh = async () => {
      try {
        await fetchNotifications()
      } catch (err) {
        console.error('Erro ao atualizar notificações:', err)
      }
    }

    const handleViewAll = () => {
      // TODO: Navegar para página de notificações
      console.log('Ver todas as notificações')
      closeDropdown()
    }

    const formatTime = (dateString) => {
      if (!dateString) return ''
      
      const date = new Date(dateString)
      const now = new Date()
      const diffMs = now - date
      const diffMins = Math.floor(diffMs / 60000)
      const diffHours = Math.floor(diffMins / 60)
      const diffDays = Math.floor(diffHours / 24)
      
      if (diffMins < 1) return 'Agora'
      if (diffMins < 60) return `${diffMins}m`
      if (diffHours < 24) return `${diffHours}h`
      if (diffDays < 7) return `${diffDays}d`
      
      return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit'
      })
    }

    // Fechar dropdown ao clicar fora
    const handleClickOutside = (event) => {
      if (isOpen.value && !event.target.closest('.relative')) {
        closeDropdown()
      }
    }

    /**
     * Lidar com nova notificação recebida
     */
    const handleNewNotification = (event) => {
      // Adicionar animação ou efeito visual
      const notification = event.detail
      console.log('Nova notificação recebida:', notification)
    }
    
    /**
     * Abrir dropdown programaticamente
     */
    const openDropdown = () => {
      isOpen.value = true
    }
    
    /**
     * Obter título do botão baseado no status
     */
    const getButtonTitle = () => {
      if (isConnecting.value) {
        return 'Conectando às notificações em tempo real...'
      }
      if (!isConnected.value) {
        return hasUnread.value 
          ? `${unreadCount.value} notificações não lidas (modo offline)`
          : 'Notificações (modo offline)'
      }
      return hasUnread.value 
        ? `${unreadCount.value} notificações não lidas`
        : 'Notificações'
    }

    onMounted(() => {
      document.addEventListener('click', handleClickOutside)
      initialize()
      
      // Escutar evento customizado de nova notificação
      window.addEventListener('notification-received', handleNewNotification)
      window.addEventListener('open-notifications', openDropdown)
    })

    onUnmounted(() => {
      document.removeEventListener('click', handleClickOutside)
      window.removeEventListener('notification-received', handleNewNotification)
      window.removeEventListener('open-notifications', openDropdown)
    })

    return {
      isOpen,
      notifications,
      displayNotifications,
      unreadCount,
      loading,
      error,
      hasUnread,
      isConnected,
      isConnecting,
      connectionError,
      toggleDropdown,
      closeDropdown,
      handleMarkAsRead,
      handleMarkAllAsRead,
      handleRemove,
      handleRefresh,
      handleViewAll,
      formatTime,
      openDropdown,
      getButtonTitle
    }
  }
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.notification-item {
  cursor: pointer;
}

.notification-item:hover .text-gray-400 {
  @apply text-gray-600;
}

.notification-button {
  position: relative;
  padding: 8px;
  background: transparent;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: background-color 0.2s;
  color: #6b7280;
}

.notification-button:hover {
  background-color: #f3f4f6;
  color: #374151;
}

.notification-button.has-notifications {
  color: #3b82f6;
}

.notification-button.connecting {
  opacity: 0.7;
}

.notification-button.disconnected {
  opacity: 0.5;
}

.connection-indicator {
  position: absolute;
  bottom: 2px;
  left: 2px;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  border: 1px solid white;
}

.connection-indicator.connected {
  background-color: #10b981;
  box-shadow: 0 0 4px rgba(16, 185, 129, 0.5);
}

.connection-indicator.connecting {
  background-color: #f59e0b;
  animation: pulse 1.5s infinite;
}

.connection-indicator.disconnected {
  background-color: #ef4444;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
</style>