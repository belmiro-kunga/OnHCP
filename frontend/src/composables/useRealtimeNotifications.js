import { ref, onMounted, onUnmounted } from 'vue'
import { useNotifications } from './useNotifications'
import { useToast } from './useToast'

export function useRealtimeNotifications() {
  const { refreshNotifications, unreadCount } = useNotifications()
  const { showToast } = useToast()
  
  const isConnected = ref(false)
  const isConnecting = ref(false)
  const connectionError = ref(null)
  const echo = ref(null)
  
  // Configuração do WebSocket (condicional para local Reverb ou Pusher Cloud)
  const envHost = import.meta.env.VITE_PUSHER_HOST
  const envPort = import.meta.env.VITE_PUSHER_PORT
  const envScheme = import.meta.env.VITE_PUSHER_SCHEME

  const wsConfig = {
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || 'local-key',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
    // Quando sem host explícito, deixar Pusher JS decidir (Pusher Cloud)
    forceTLS: envScheme ? envScheme === 'https' : true,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('auth_token')}`,
        Accept: 'application/json'
      }
    }
  }

  // Se um host foi definido (ambiente local via Nginx/Reverb), configurar endpoints explícitos
  if (envHost) {
    wsConfig.wsHost = envHost || window.location.hostname
    wsConfig.wsPort = envPort || 6001
    wsConfig.wssPort = envPort || 6001
    // Opcional: wsPath padrão do servidor Pusher-like (Reverb)
    // wsConfig.wsPath = '/app'
    wsConfig.forceTLS = envScheme === 'https'
  }

  /**
   * Inicializar conexão WebSocket
   */
  const connect = async () => {
    if (isConnecting.value || isConnected.value) {
      return
    }

    try {
      isConnecting.value = true
      connectionError.value = null

      // Importar Laravel Echo dinamicamente
      const { default: Echo } = await import('laravel-echo')
      
      // Importar Pusher se necessário
      if (wsConfig.broadcaster === 'pusher') {
        const Pusher = await import('pusher-js')
        window.Pusher = Pusher.default
      }

      // Criar instância do Echo
      echo.value = new Echo(wsConfig)

      // Configurar eventos de conexão
      echo.value.connector.pusher.connection.bind('connected', () => {
        isConnected.value = true
        isConnecting.value = false
        console.log('WebSocket conectado com sucesso')
      })

      echo.value.connector.pusher.connection.bind('disconnected', () => {
        isConnected.value = false
        console.log('WebSocket desconectado')
      })

      echo.value.connector.pusher.connection.bind('error', (error) => {
        connectionError.value = error.message || 'Erro de conexão WebSocket'
        isConnecting.value = false
        console.error('Erro WebSocket:', error)
      })

      // Escutar notificações do usuário
      const userId = getUserId()
      if (userId) {
        listenToUserNotifications(userId)
      }

    } catch (error) {
      connectionError.value = error.message
      isConnecting.value = false
      console.error('Erro ao conectar WebSocket:', error)
    }
  }

  /**
   * Desconectar WebSocket
   */
  const disconnect = () => {
    if (echo.value) {
      echo.value.disconnect()
      echo.value = null
    }
    isConnected.value = false
    isConnecting.value = false
  }

  /**
   * Escutar notificações do usuário
   */
  const listenToUserNotifications = (userId) => {
    if (!echo.value) return

    echo.value.private(`user.${userId}`)
      .listen('.notification.sent', (data) => {
        handleNewNotification(data)
      })
  }

  /**
   * Processar nova notificação recebida
   */
  const handleNewNotification = (data) => {
    const { notification, unread_count } = data
    
    // Atualizar contador de não lidas
    unreadCount.value = unread_count
    
    // Mostrar toast para notificações importantes
    if (notification.priority === 'urgent' || notification.priority === 'high') {
      showToast(notification.title, 'info', {
        duration: 5000,
        action: {
          label: 'Ver',
          handler: () => {
            // Navegar para a notificação ou abrir modal
            handleNotificationClick(notification)
          }
        }
      })
    }
    
    // Reproduzir som de notificação (opcional)
    playNotificationSound(notification.priority)
    
    // Atualizar lista de notificações
    refreshNotifications()
    
    // Emitir evento customizado para outros componentes
    window.dispatchEvent(new CustomEvent('notification-received', {
      detail: notification
    }))
  }

  /**
   * Reproduzir som de notificação
   */
  const playNotificationSound = (priority) => {
    try {
      // Verificar se o usuário permite sons
      const soundEnabled = localStorage.getItem('notification_sound') !== 'false'
      if (!soundEnabled) return

      // Diferentes sons para diferentes prioridades
      const soundFile = priority === 'urgent' ? 'urgent.mp3' : 'notification.mp3'
      const audio = new Audio(`/sounds/${soundFile}`)
      audio.volume = 0.3
      audio.play().catch(() => {
        // Ignorar erros de reprodução (usuário pode ter bloqueado autoplay)
      })
    } catch (error) {
      console.warn('Erro ao reproduzir som de notificação:', error)
    }
  }

  /**
   * Lidar com clique na notificação
   */
  const handleNotificationClick = (notification) => {
    // Implementar navegação baseada no tipo de notificação
    const { type, data } = notification
    
    switch (type) {
      case 'simulado_assigned':
      case 'simulado_deadline':
        if (data.simulado_id) {
          // Navegar para o simulado
          window.location.href = `/simulados/${data.simulado_id}`
        }
        break
      case 'simulado_result':
        if (data.attempt_id) {
          // Navegar para o resultado
          window.location.href = `/simulados/results/${data.attempt_id}`
        }
        break
      default:
        // Abrir painel de notificações
        window.dispatchEvent(new CustomEvent('open-notifications'))
    }
  }

  /**
   * Obter ID do usuário do token
   */
  const getUserId = () => {
    try {
      const token = localStorage.getItem('auth_token')
      if (!token) return null
      
      // Decodificar JWT para obter user ID (implementação simples)
      const payload = JSON.parse(atob(token.split('.')[1]))
      return payload.sub || payload.user_id
    } catch (error) {
      console.warn('Erro ao obter user ID do token:', error)
      return null
    }
  }

  /**
   * Reconectar automaticamente
   */
  const reconnect = () => {
    disconnect()
    setTimeout(() => {
      connect()
    }, 1000)
  }

  /**
   * Verificar status da conexão
   */
  const checkConnection = () => {
    if (!isConnected.value && !isConnecting.value) {
      reconnect()
    }
  }

  // Configurar reconexão automática
  let reconnectInterval = null
  
  onMounted(() => {
    // Conectar automaticamente
    connect()
    
    // Verificar conexão periodicamente
    reconnectInterval = setInterval(checkConnection, 30000) // 30 segundos
    
    // Reconectar quando a aba voltar a ficar ativa
    document.addEventListener('visibilitychange', () => {
      if (!document.hidden && !isConnected.value) {
        reconnect()
      }
    })
  })

  onUnmounted(() => {
    disconnect()
    if (reconnectInterval) {
      clearInterval(reconnectInterval)
    }
  })

  return {
    // Estado
    isConnected,
    isConnecting,
    connectionError,
    
    // Métodos
    connect,
    disconnect,
    reconnect,
    handleNotificationClick
  }
}