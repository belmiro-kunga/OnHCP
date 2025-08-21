import { ref, computed } from 'vue'
import { useApi } from './useApi'
import { useToast } from './useToast'

export function useNotificationPreferences() {
  const { apiCall } = useApi()
  const { showToast } = useToast()
  
  const preferences = ref(null)
  const settings = ref(null)
  const loading = ref(false)
  const error = ref(null)

  // Valores padrão para as preferências
  const defaultPreferences = {
    simulado_assigned: true,
    simulado_completed: true,
    simulado_result: true,
    simulado_deadline: true,
    email_notifications: true,
    quiet_hours_enabled: false,
    quiet_hours_start: '22:00',
    quiet_hours_end: '08:00'
  }

  // Computed para verificar se as preferências foram carregadas
  const isLoaded = computed(() => preferences.value !== null)

  // Computed para obter as preferências com fallback para valores padrão
  const currentPreferences = computed(() => {
    return preferences.value || defaultPreferences
  })

  /**
   * Carregar preferências do usuário
   */
  const loadPreferences = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall('/notification-preferences', 'GET')
      
      if (response.success) {
        preferences.value = response.data.preferences
      } else {
        throw new Error(response.message || 'Erro ao carregar preferências')
      }
    } catch (err) {
      error.value = err.message
      console.error('Erro ao carregar preferências de notificação:', err)
      // Em caso de erro, usar valores padrão
      preferences.value = defaultPreferences
    } finally {
      loading.value = false
    }
  }

  /**
   * Carregar configurações disponíveis
   */
  const loadSettings = async () => {
    try {
      const response = await apiCall('/notification-preferences/settings', 'GET')
      
      if (response.success) {
        settings.value = response.data
      }
    } catch (err) {
      console.error('Erro ao carregar configurações:', err)
    }
  }

  /**
   * Atualizar preferências
   */
  const updatePreferences = async (newPreferences) => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall('/notification-preferences', 'PUT', newPreferences)
      
      if (response.success) {
        preferences.value = response.data.preferences
        showToast('Preferências atualizadas com sucesso', 'success')
        return true
      } else {
        throw new Error(response.message || 'Erro ao atualizar preferências')
      }
    } catch (err) {
      error.value = err.message
      showToast('Erro ao atualizar preferências', 'error')
      console.error('Erro ao atualizar preferências:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Resetar preferências para valores padrão
   */
  const resetPreferences = async () => {
    loading.value = true
    error.value = null
    
    try {
      const response = await apiCall('/notification-preferences/reset', 'POST')
      
      if (response.success) {
        preferences.value = response.data.preferences
        showToast('Preferências resetadas com sucesso', 'success')
        return true
      } else {
        throw new Error(response.message || 'Erro ao resetar preferências')
      }
    } catch (err) {
      error.value = err.message
      showToast('Erro ao resetar preferências', 'error')
      console.error('Erro ao resetar preferências:', err)
      return false
    } finally {
      loading.value = false
    }
  }

  /**
   * Atualizar uma preferência específica
   */
  const updateSinglePreference = async (key, value) => {
    const updates = { [key]: value }
    return await updatePreferences(updates)
  }

  /**
   * Verificar se um tipo de notificação está habilitado
   */
  const isNotificationEnabled = (type) => {
    return currentPreferences.value[type] === true
  }

  /**
   * Verificar se as notificações por email estão habilitadas
   */
  const areEmailNotificationsEnabled = computed(() => {
    return currentPreferences.value.email_notifications === true
  })

  /**
   * Verificar se o horário de silêncio está habilitado
   */
  const isQuietHoursEnabled = computed(() => {
    return currentPreferences.value.quiet_hours_enabled === true
  })

  /**
   * Obter o horário de silêncio formatado
   */
  const quietHoursRange = computed(() => {
    if (!isQuietHoursEnabled.value) return null
    
    const start = currentPreferences.value.quiet_hours_start
    const end = currentPreferences.value.quiet_hours_end
    
    return `${start} - ${end}`
  })

  /**
   * Inicializar o composable
   */
  const initialize = async () => {
    await Promise.all([
      loadPreferences(),
      loadSettings()
    ])
  }

  return {
    // Estado
    preferences: currentPreferences,
    settings,
    loading,
    error,
    isLoaded,
    
    // Computed
    areEmailNotificationsEnabled,
    isQuietHoursEnabled,
    quietHoursRange,
    
    // Métodos
    loadPreferences,
    loadSettings,
    updatePreferences,
    updateSinglePreference,
    resetPreferences,
    isNotificationEnabled,
    initialize
  }
}