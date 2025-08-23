<template>
  <div class="space-y-8">
    <!-- Cabeçalho -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Configurações de Vídeo</h2>
          <p class="text-sm text-gray-600 mt-1">
            Configure a origem dos vídeos e as APIs de armazenamento
          </p>
        </div>
        <button
          @click="resetConfig"
          :disabled="saving"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          <i class="fas fa-undo mr-2"></i>
          Resetar
        </button>
      </div>
    </div>

    <!-- Mensagens -->
    <div v-if="message" :class="messageClasses" class="p-4 rounded-md">
      <div class="flex">
        <div class="flex-shrink-0">
          <i :class="messageIcon" class="h-5 w-5"></i>
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium">{{ message }}</p>
        </div>
        <div class="ml-auto pl-3">
          <button @click="clearMessages" class="inline-flex text-sm">
            <i class="fas fa-times h-4 w-4"></i>
          </button>
        </div>
      </div>
    </div>

    <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-md">
      <div class="flex">
        <div class="flex-shrink-0">
          <i class="fas fa-exclamation-circle h-5 w-5 text-red-400"></i>
        </div>
        <div class="ml-3">
          <p class="text-sm font-medium">{{ error }}</p>
        </div>
        <div class="ml-auto pl-3">
          <button @click="clearMessages" class="inline-flex text-red-400 hover:text-red-600">
            <i class="fas fa-times h-4 w-4"></i>
          </button>
        </div>
      </div>
    </div>

    <!-- Seleção da Origem dos Vídeos -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Origem dos Vídeos</h3>
      <div class="space-y-4">
        <div v-for="(label, source) in config.available_sources" :key="source" class="flex items-center">
          <input
            :id="source"
            v-model="config.video_source"
            :value="source"
            @change="updateVideoSource(source)"
            type="radio"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300"
            :disabled="saving"
          />
          <label :for="source" class="ml-3 block text-sm font-medium text-gray-700">
            {{ label }}
          </label>
          <div v-if="isSourceActive(source)" class="ml-2">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
              <i class="fas fa-check mr-1"></i>
              Ativo
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Configurações do Cloudflare R2 -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Cloudflare R2</h3>
        <div class="flex space-x-2">
          <button
            @click="testCloudflareConnection"
            :disabled="testing.cloudflare || !isCloudflareConfigured()"
            class="px-3 py-1 text-sm font-medium text-blue-700 bg-blue-100 border border-blue-300 rounded-md hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <i v-if="testing.cloudflare" class="fas fa-spinner fa-spin mr-1"></i>
            <i v-else class="fas fa-plug mr-1"></i>
            Testar Conexão
          </button>
        </div>
      </div>
      
      <form @submit.prevent="saveCloudflareConfig" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account ID</label>
            <input
              v-model="cloudflareForm.account_id"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Cloudflare Account ID"
              :disabled="saving"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Access Key ID</label>
            <input
              v-model="cloudflareForm.access_key_id"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="R2 Access Key ID"
              :disabled="saving"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Secret Access Key</label>
            <input
              v-model="cloudflareForm.secret_access_key"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="R2 Secret Access Key"
              :disabled="saving"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bucket Name</label>
            <input
              v-model="cloudflareForm.bucket_name"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="Nome do bucket R2"
              :disabled="saving"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Região</label>
            <input
              v-model="cloudflareForm.region"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="auto"
              :disabled="saving"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Endpoint</label>
            <input
              v-model="cloudflareForm.endpoint"
              type="url"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="https://account-id.r2.cloudflarestorage.com"
              :disabled="saving"
            />
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">URL Público</label>
          <input
            v-model="cloudflareForm.public_url"
            type="url"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="https://pub-bucket.r2.dev"
            :disabled="saving"
          />
        </div>
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
            Salvar Configurações
          </button>
        </div>
      </form>
    </div>

    <!-- Configurações do YouTube API -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">YouTube API</h3>
        <div class="flex space-x-2">
          <button
            @click="testYoutubeConnection"
            :disabled="testing.youtube || !isYoutubeConfigured()"
            class="px-3 py-1 text-sm font-medium text-red-700 bg-red-100 border border-red-300 rounded-md hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50"
          >
            <i v-if="testing.youtube" class="fas fa-spinner fa-spin mr-1"></i>
            <i v-else class="fas fa-plug mr-1"></i>
            Testar Conexão
          </button>
        </div>
      </div>
      
      <form @submit.prevent="saveYoutubeConfig" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
            <input
              v-model="youtubeForm.api_key"
              type="password"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="YouTube Data API v3 Key"
              :disabled="saving"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Channel ID (Opcional)</label>
            <input
              v-model="youtubeForm.channel_id"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              placeholder="ID do canal do YouTube"
              :disabled="saving"
            />
          </div>
        </div>
        <div class="flex items-center">
          <input
            v-model="youtubeForm.enabled"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
            :disabled="saving"
          />
          <label class="ml-2 block text-sm text-gray-700">
            Habilitar integração com YouTube API
          </label>
        </div>
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
            Salvar Configurações
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useVideoConfig } from '../../composables/useVideoConfig'

export default {
  name: 'AdminVideoConfig',
  setup() {
    const {
      config,
      loading,
      saving,
      testing,
      error,
      message,
      messageType,
      loadConfig,
      updateVideoSource,
      updateCloudflareConfig,
      updateYoutubeConfig,
      testCloudflareConnection,
      testYoutubeConnection,
      resetConfig,
      clearMessages,
      isSourceActive,
      isCloudflareConfigured,
      isYoutubeConfigured
    } = useVideoConfig()

    // Formulários locais
    const cloudflareForm = reactive({
      account_id: '',
      access_key_id: '',
      secret_access_key: '',
      bucket_name: '',
      region: 'auto',
      endpoint: '',
      public_url: ''
    })

    const youtubeForm = reactive({
      api_key: '',
      channel_id: '',
      enabled: false
    })

    // Computed para classes de mensagem
    const messageClasses = computed(() => {
      return messageType.value === 'success'
        ? 'bg-green-50 border border-green-200 text-green-700'
        : 'bg-red-50 border border-red-200 text-red-700'
    })

    const messageIcon = computed(() => {
      return messageType.value === 'success'
        ? 'fas fa-check-circle h-5 w-5 text-green-400'
        : 'fas fa-exclamation-circle h-5 w-5 text-red-400'
    })

    // Métodos para salvar configurações
    const saveCloudflareConfig = async () => {
      await updateCloudflareConfig(cloudflareForm)
    }

    const saveYoutubeConfig = async () => {
      await updateYoutubeConfig(youtubeForm)
    }

    // Sincronizar formulários com configurações carregadas
    const syncForms = () => {
      Object.assign(cloudflareForm, config.cloudflare_r2)
      Object.assign(youtubeForm, config.youtube_api)
    }

    // Carregar configurações na inicialização
    onMounted(async () => {
      await loadConfig()
      syncForms()
    })

    return {
      // Estado
      config,
      loading,
      saving,
      testing,
      error,
      message,
      messageType,
      cloudflareForm,
      youtubeForm,
      
      // Computed
      messageClasses,
      messageIcon,
      
      // Métodos
      updateVideoSource,
      saveCloudflareConfig,
      saveYoutubeConfig,
      testCloudflareConnection,
      testYoutubeConnection,
      resetConfig,
      clearMessages,
      isSourceActive,
      isCloudflareConfigured,
      isYoutubeConfigured
    }
  }
}
</script>

<style scoped>
/* Estilos específicos do componente */
.form-input {
  @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
}

.form-checkbox {
  @apply h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded;
}
</style>