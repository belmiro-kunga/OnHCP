<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Assinatura de Documento</h2>
          <p class="text-sm text-gray-600 mt-1">{{ signature.document?.name }}</p>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="overflow-y-auto max-h-[calc(90vh-200px)]">
        <div class="p-6 space-y-6">
          <!-- Document Info -->
          <div class="bg-blue-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-blue-900 mb-3">Informações do Documento</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-blue-600">Nome do Arquivo</p>
                <p class="font-medium text-blue-900">{{ signature.document?.file_name }}</p>
              </div>
              <div>
                <p class="text-sm text-blue-600">Tipo</p>
                <p class="font-medium text-blue-900">{{ getTypeLabel(signature.document?.type) }}</p>
              </div>
              <div>
                <p class="text-sm text-blue-600">Departamento</p>
                <p class="font-medium text-blue-900">{{ signature.document?.department?.name || 'N/A' }}</p>
              </div>
              <div>
                <p class="text-sm text-blue-600">Tamanho</p>
                <p class="font-medium text-blue-900">{{ formatFileSize(signature.document?.file_size) }}</p>
              </div>
            </div>
            <div v-if="signature.document?.description" class="mt-4">
              <p class="text-sm text-blue-600">Descrição</p>
              <p class="text-blue-900">{{ signature.document?.description }}</p>
            </div>
          </div>

          <!-- Document Preview -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Visualização do Documento</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
              <!-- PDF Preview -->
              <div v-if="isPDF(signature.document?.mime_type)" class="text-center">
                <iframe
                  :src="getDocumentUrl(signature.document)"
                  class="w-full h-96 border border-gray-300 rounded"
                  frameborder="0"
                ></iframe>
                <p class="text-sm text-gray-600 mt-2">
                  <a 
                    :href="getDocumentUrl(signature.document)"
                    target="_blank"
                    class="text-blue-600 hover:text-blue-800"
                  >
                    Abrir em nova aba para melhor visualização
                  </a>
                </p>
              </div>
              
              <!-- Image Preview -->
              <div v-else-if="isImage(signature.document?.mime_type)" class="text-center">
                <img 
                  :src="getDocumentUrl(signature.document)"
                  :alt="signature.document?.name"
                  class="max-w-full max-h-96 mx-auto rounded-lg shadow-md"
                  @error="imageError = true"
                >
                <div v-if="imageError" class="text-gray-500 py-8">
                  <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                  <p>Não foi possível carregar a imagem</p>
                </div>
              </div>
              
              <!-- Other file types -->
              <div v-else class="text-center py-8">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="text-gray-600 mb-4">Visualização não disponível para este tipo de arquivo</p>
                <button
                  @click="downloadDocument"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
                >
                  Download para Visualizar
                </button>
              </div>
            </div>
          </div>

          <!-- Terms and Conditions -->
          <div v-if="signature.terms_content">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Termos e Condições</h3>
            <div class="bg-gray-50 p-4 rounded-lg max-h-48 overflow-y-auto">
              <div class="prose prose-sm max-w-none" v-html="signature.terms_content"></div>
            </div>
          </div>

          <!-- Signature Agreement -->
          <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-yellow-900 mb-3">Confirmação de Assinatura</h3>
            <div class="space-y-3">
              <label class="flex items-start space-x-3">
                <input
                  v-model="agreements.readDocument"
                  type="checkbox"
                  class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <span class="text-sm text-yellow-800">
                  Declaro que li e compreendi completamente o conteúdo deste documento.
                </span>
              </label>
              
              <label v-if="signature.terms_content" class="flex items-start space-x-3">
                <input
                  v-model="agreements.acceptTerms"
                  type="checkbox"
                  class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <span class="text-sm text-yellow-800">
                  Aceito os termos e condições apresentados neste documento.
                </span>
              </label>
              
              <label class="flex items-start space-x-3">
                <input
                  v-model="agreements.agreeToSign"
                  type="checkbox"
                  class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <span class="text-sm text-yellow-800">
                  Concordo em assinar este documento digitalmente e entendo que esta assinatura tem validade legal.
                </span>
              </label>
            </div>
          </div>

          <!-- Digital Signature Info -->
          <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-green-900 mb-3">Informações da Assinatura Digital</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-green-600">Assinante</p>
                <p class="font-medium text-green-900">{{ currentUser?.name }}</p>
              </div>
              <div>
                <p class="text-green-600">E-mail</p>
                <p class="font-medium text-green-900">{{ currentUser?.email }}</p>
              </div>
              <div>
                <p class="text-green-600">Data/Hora</p>
                <p class="font-medium text-green-900">{{ formatCurrentDateTime() }}</p>
              </div>
              <div>
                <p class="text-green-600">IP</p>
                <p class="font-medium text-green-900">{{ userIP || 'Detectando...' }}</p>
              </div>
            </div>
          </div>

          <!-- Comments -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Comentários (opcional)
            </label>
            <textarea
              v-model="comments"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Adicione comentários sobre esta assinatura..."
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
        <div class="flex gap-3">
          <button
            @click="downloadDocument"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Download
          </button>
        </div>
        <div class="flex gap-3">
          <button
            @click="rejectSignature"
            class="px-4 py-2 text-red-700 border border-red-300 rounded-md hover:bg-red-50 transition-colors"
          >
            Rejeitar
          </button>
          <button
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="confirmSignature"
            :disabled="!canSign || signing"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <svg v-if="signing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ signing ? 'Assinando...' : 'Assinar Documento' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'

export default {
  name: 'SignatureModal',
  props: {
    signature: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'signed'],
  setup(props, { emit }) {
    const { showToast } = useToast()
    
    const signing = ref(false)
    const imageError = ref(false)
    const comments = ref('')
    const userIP = ref('')
    const currentUser = ref(null)
    
    const agreements = ref({
      readDocument: false,
      acceptTerms: false,
      agreeToSign: false
    })
    
    const canSign = computed(() => {
      const baseRequirements = agreements.value.readDocument && agreements.value.agreeToSign
      const termsRequirement = !props.signature.terms_content || agreements.value.acceptTerms
      return baseRequirements && termsRequirement
    })
    
    const getTypeLabel = (type) => {
      const labels = {
        contract: 'Contrato',
        policy: 'Política',
        manual: 'Manual',
        form: 'Formulário',
        other: 'Outro'
      }
      return labels[type] || type
    }
    
    const formatFileSize = (bytes) => {
      if (!bytes) return 'N/A'
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(1024))
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
    }
    
    const formatCurrentDateTime = () => {
      return new Date().toLocaleString('pt-AO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
      })
    }
    
    const isPDF = (mimeType) => {
      return mimeType === 'application/pdf'
    }
    
    const isImage = (mimeType) => {
      return mimeType && mimeType.startsWith('image/')
    }
    
    const getDocumentUrl = (document) => {
      return `/api/documents/${document.id}/download`
    }
    
    const downloadDocument = async () => {
      try {
        const response = await fetch(`/api/documents/${props.signature.document.id}/download`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (!response.ok) {
          throw new Error('Erro ao baixar documento')
        }
        
        const blob = await response.blob()
        const url = window.URL.createObjectURL(blob)
        const a = document.createElement('a')
        a.href = url
        a.download = props.signature.document.file_name
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
        document.body.removeChild(a)
        
        showToast('Download iniciado com sucesso!', 'success')
      } catch (error) {
        showToast('Erro ao baixar documento: ' + error.message, 'error')
      }
    }
    
    const confirmSignature = async () => {
      if (!canSign.value) {
        showToast('Por favor, confirme todos os itens obrigatórios', 'warning')
        return
      }
      
      signing.value = true
      
      try {
        const response = await fetch(`/api/document-signatures/${props.signature.id}/sign`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            comments: comments.value,
            ip_address: userIP.value,
            agreements: agreements.value
          })
        })
        
        if (response.ok) {
          showToast('Documento assinado com sucesso!', 'success')
          emit('signed')
        } else {
          const errorData = await response.json()
          throw new Error(errorData.message || 'Erro ao assinar documento')
        }
      } catch (error) {
        showToast('Erro ao assinar documento: ' + error.message, 'error')
      } finally {
        signing.value = false
      }
    }
    
    const rejectSignature = async () => {
      if (!confirm('Tem certeza que deseja rejeitar esta assinatura?')) {
        return
      }
      
      try {
        const response = await fetch(`/api/document-signatures/${props.signature.id}/reject`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            comments: comments.value
          })
        })
        
        if (response.ok) {
          showToast('Assinatura rejeitada com sucesso!', 'success')
          emit('signed') // Refresh the list
        } else {
          throw new Error('Erro ao rejeitar assinatura')
        }
      } catch (error) {
        showToast('Erro ao rejeitar assinatura: ' + error.message, 'error')
      }
    }
    
    const getUserIP = async () => {
      try {
        const response = await fetch('https://api.ipify.org?format=json')
        const data = await response.json()
        userIP.value = data.ip
      } catch (error) {
        console.error('Erro ao obter IP:', error)
        userIP.value = 'Não disponível'
      }
    }
    
    const getCurrentUser = () => {
      const userStr = localStorage.getItem('user')
      if (userStr) {
        currentUser.value = JSON.parse(userStr)
      }
    }
    
    onMounted(() => {
      getUserIP()
      getCurrentUser()
    })
    
    return {
      signing,
      imageError,
      comments,
      userIP,
      currentUser,
      agreements,
      canSign,
      getTypeLabel,
      formatFileSize,
      formatCurrentDateTime,
      isPDF,
      isImage,
      getDocumentUrl,
      downloadDocument,
      confirmSignature,
      rejectSignature
    }
  }
}
</script>

<style scoped>
.prose {
  color: inherit;
}

.prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
  color: inherit;
  margin-top: 1em;
  margin-bottom: 0.5em;
}

.prose p {
  margin-bottom: 1em;
}

.prose ul, .prose ol {
  margin-bottom: 1em;
  padding-left: 1.5em;
}

.prose li {
  margin-bottom: 0.25em;
}
</style>