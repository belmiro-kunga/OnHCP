import { ref, reactive } from 'vue'
import { api } from './api'
import { useToast } from './useToast'

export function useVideoConfig() {
  const { showToast } = useToast()
  const loading = ref(false)
  const saving = ref(false)
  const testing = ref({
    cloudflare: false,
    youtube: false
  })
  const error = ref('')
  const message = ref('')
  const messageType = ref('success')

  // Estado das configurações
  const config = reactive({
    video_source: 'local',
    cloudflare_r2: {
      account_id: '',
      access_key_id: '',
      secret_access_key: '',
      bucket_name: '',
      region: 'auto',
      endpoint: '',
      public_url: ''
    },
    youtube_api: {
      api_key: '',
      channel_id: '',
      enabled: false
    },
    available_sources: {
      local: 'Armazenamento Local',
      cloudflare_r2: 'Cloudflare R2',
      youtube_api: 'YouTube API'
    }
  })

  /**
   * Carregar configurações de vídeo
   */
  const loadConfig = async () => {
    loading.value = true
    error.value = ''
    
    try {
      const response = await api.get('/video-config')
      
      if (response.data) {
        Object.assign(config, response.data)
      }
    } catch (err) {
      error.value = err?.response?.data?.message || 'Erro ao carregar configurações'
      console.error('Erro ao carregar configurações de vídeo:', err)
    } finally {
      loading.value = false
    }
  }

  /**
   * Atualizar origem dos vídeos
   */
  const updateVideoSource = async (source) => {
    saving.value = true
    error.value = ''
    message.value = ''
    
    try {
      const response = await api.put('/video-config/source', {
        video_source: source
      })
      
      if (response.data) {
        config.video_source = source
        message.value = response.data.message
        messageType.value = 'success'
        showToast('Origem dos vídeos atualizada com sucesso!', 'success')
      }
    } catch (err) {
      error.value = err?.response?.data?.message || 'Erro ao atualizar origem dos vídeos'
      messageType.value = 'error'
      showToast('Erro ao atualizar origem dos vídeos', 'error')
      console.error('Erro ao atualizar origem dos vídeos:', err)
    } finally {
      saving.value = false
    }
  }

  /**
   * Atualizar configurações do Cloudflare R2
   */
  const updateCloudflareConfig = async (cloudflareConfig) => {
    saving.value = true
    error.value = ''
    message.value = ''
    
    try {
      const response = await api.put('/video-config/cloudflare', cloudflareConfig)
      
      if (response.data) {
        Object.assign(config.cloudflare_r2, response.data)
        message.value = response.data.message
        messageType.value = 'success'
        showToast('Configurações do Cloudflare R2 salvas com sucesso!', 'success')
      }
    } catch (err) {
      error.value = err?.response?.data?.message || 'Erro ao atualizar configurações do Cloudflare R2'
      messageType.value = 'error'
      showToast('Erro ao salvar configurações do Cloudflare R2', 'error')
      console.error('Erro ao atualizar configurações do Cloudflare R2:', err)
    } finally {
      saving.value = false
    }
  }

  /**
   * Atualizar configurações do YouTube API
   */
  const updateYoutubeConfig = async (youtubeConfig) => {
    saving.value = true
    error.value = ''
    message.value = ''
    
    try {
      const response = await api.put('/video-config/youtube', youtubeConfig)
      
      if (response.data) {
        Object.assign(config.youtube_api, response.data)
        message.value = response.data.message
        messageType.value = 'success'
        showToast('Configurações do YouTube API salvas com sucesso!', 'success')
      }
    } catch (err) {
      error.value = err?.response?.data?.message || 'Erro ao atualizar configurações do YouTube API'
      messageType.value = 'error'
      showToast('Erro ao salvar configurações do YouTube API', 'error')
      console.error('Erro ao atualizar configurações do YouTube API:', err)
    } finally {
      saving.value = false
    }
  }

  /**
   * Testar conexão com Cloudflare R2
   */
  const testCloudflareConnection = async () => {
    testing.value.cloudflare = true
    error.value = ''
    message.value = ''
    
    try {
      const response = await api.post('/video-config/test/cloudflare')
      
      if (response.data?.success) {
        message.value = response.data.message
        messageType.value = 'success'
        showToast('Conexão com Cloudflare R2 testada com sucesso!', 'success')
      }
    } catch (err) {
      error.value = err?.response?.data?.message || 'Erro ao testar conexão com Cloudflare R2'
      messageType.value = 'error'
      showToast('Erro ao testar conexão com Cloudflare R2', 'error')
      console.error('Erro ao testar conexão com Cloudflare R2:', err)
    } finally {
      testing.value.cloudflare = false
    }
  }

  /**
   * Testar conexão com YouTube API
   */
  const testYoutubeConnection = async () => {
    testing.value.youtube = true
    error.value = ''
    message.value = ''
    
    try {
      const response = await api.post('/video-config/test/youtube')
      
      if (response.data?.success) {
        message.value = response.data.message
        messageType.value = 'success'
        showToast('Conexão com YouTube API testada com sucesso!', 'success')
      }
    } catch (err) {
      error.value = err?.response?.data?.message || 'Erro ao testar conexão com YouTube API'
      messageType.value = 'error'
      showToast('Erro ao testar conexão com YouTube API', 'error')
      console.error('Erro ao testar conexão com YouTube API:', err)
    } finally {
      testing.value.youtube = false
    }
  }

  /**
   * Resetar configurações para valores padrão
   */
  const resetConfig = async () => {
    saving.value = true
    error.value = ''
    message.value = ''
    
    try {
      const response = await api.post('/video-config/reset')
      
      if (response.data?.success) {
        await loadConfig() // Recarregar configurações
        message.value = response.data.message
        messageType.value = 'success'
        showToast('Configurações resetadas com sucesso!', 'success')
      }
    } catch (err) {
      error.value = err?.response?.data?.message || 'Erro ao resetar configurações'
      messageType.value = 'error'
      showToast('Erro ao resetar configurações', 'error')
      console.error('Erro ao resetar configurações:', err)
    } finally {
      saving.value = false
    }
  }

  /**
   * Limpar mensagens
   */
  const clearMessages = () => {
    error.value = ''
    message.value = ''
  }

  /**
   * Verificar se uma origem de vídeo está ativa
   */
  const isSourceActive = (source) => {
    return config.video_source === source
  }

  /**
   * Verificar se as configurações do Cloudflare estão completas
   */
  const isCloudflareConfigured = () => {
    const cf = config.cloudflare_r2
    return cf.account_id && cf.access_key_id && cf.secret_access_key && 
           cf.bucket_name && cf.endpoint && cf.public_url
  }

  /**
   * Verificar se as configurações do YouTube estão completas
   */
  const isYoutubeConfigured = () => {
    return config.youtube_api.api_key && config.youtube_api.enabled
  }

  return {
    // Estado
    config,
    loading,
    saving,
    testing,
    error,
    message,
    messageType,
    
    // Métodos
    loadConfig,
    updateVideoSource,
    updateCloudflareConfig,
    updateYoutubeConfig,
    testCloudflareConnection,
    testYoutubeConnection,
    resetConfig,
    clearMessages,
    
    // Computed
    isSourceActive,
    isCloudflareConfigured,
    isYoutubeConfigured
  }
}