<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">{{ document.name }}</h2>
          <div class="flex items-center gap-2 mt-1">
            <span :class="getStatusClass(document.status)" class="text-xs font-medium px-2 py-1 rounded">
              {{ getStatusLabel(document.status) }}
            </span>
            <span :class="getTypeClass(document.type)" class="text-xs font-medium px-2 py-1 rounded">
              {{ getTypeLabel(document.type) }}
            </span>
            <span v-if="document.department" class="text-sm text-gray-500">{{ document.department.name }}</span>
          </div>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
        <div class="p-6 space-y-6">
          <!-- Document Info -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-sm font-medium text-blue-900">Arquivo</span>
              </div>
              <p class="text-sm font-medium text-blue-600">{{ document.file_name }}</p>
              <p class="text-xs text-blue-500">{{ formatFileSize(document.file_size) }}</p>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-green-900">Assinatura</span>
              </div>
              <p class="text-sm font-medium text-green-600">
                {{ document.requires_signature ? 'Obrigatória' : 'Não requerida' }}
              </p>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-purple-900">Criado em</span>
              </div>
              <p class="text-sm font-medium text-purple-600">{{ formatDate(document.created_at) }}</p>
              <p class="text-xs text-purple-500">por {{ document.uploaded_by?.name || 'N/A' }}</p>
            </div>
          </div>

          <!-- Description -->
          <div v-if="document.description">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Descrição</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-gray-700 whitespace-pre-wrap">{{ document.description }}</p>
            </div>
          </div>

          <!-- Document Properties -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Propriedades</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm font-medium text-gray-700">Documento obrigatório</span>
                <span :class="document.is_mandatory ? 'text-green-600' : 'text-gray-500'" class="text-sm font-medium">
                  {{ document.is_mandatory ? 'Sim' : 'Não' }}
                </span>
              </div>
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm font-medium text-gray-700">Requer assinatura</span>
                <span :class="document.requires_signature ? 'text-green-600' : 'text-gray-500'" class="text-sm font-medium">
                  {{ document.requires_signature ? 'Sim' : 'Não' }}
                </span>
              </div>
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm font-medium text-gray-700">Tipo MIME</span>
                <span class="text-sm text-gray-600">{{ document.mime_type || 'N/A' }}</span>
              </div>
              <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm font-medium text-gray-700">Última atualização</span>
                <span class="text-sm text-gray-600">{{ formatDate(document.updated_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Signature Statistics (if requires signature) -->
          <div v-if="document.requires_signature && signatureStats">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Estatísticas de Assinatura</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div class="bg-blue-50 p-3 rounded-lg text-center">
                <p class="text-sm text-blue-600">Total de Assinaturas</p>
                <p class="text-xl font-bold text-blue-900">{{ signatureStats.total || 0 }}</p>
              </div>
              <div class="bg-green-50 p-3 rounded-lg text-center">
                <p class="text-sm text-green-600">Assinadas</p>
                <p class="text-xl font-bold text-green-900">{{ signatureStats.signed || 0 }}</p>
              </div>
              <div class="bg-yellow-50 p-3 rounded-lg text-center">
                <p class="text-sm text-yellow-600">Pendentes</p>
                <p class="text-xl font-bold text-yellow-900">{{ signatureStats.pending || 0 }}</p>
              </div>
              <div class="bg-red-50 p-3 rounded-lg text-center">
                <p class="text-sm text-red-600">Rejeitadas</p>
                <p class="text-xl font-bold text-red-900">{{ signatureStats.rejected || 0 }}</p>
              </div>
            </div>
          </div>

          <!-- Recent Signatures -->
          <div v-if="document.requires_signature && recentSignatures.length > 0">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Assinaturas Recentes</h3>
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
              <div class="max-h-64 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                      <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="signature in recentSignatures" :key="signature.id">
                      <td class="px-4 py-2 text-sm text-gray-900">{{ signature.user?.name || 'N/A' }}</td>
                      <td class="px-4 py-2">
                        <span :class="getSignatureStatusClass(signature.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                          {{ getSignatureStatusLabel(signature.status) }}
                        </span>
                      </td>
                      <td class="px-4 py-2 text-sm text-gray-500">{{ formatDate(signature.signed_at || signature.created_at) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- File Preview (for images) -->
          <div v-if="isImage(document.mime_type)">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Visualização</h3>
            <div class="bg-gray-50 p-4 rounded-lg text-center">
              <img 
                :src="getFileUrl(document)"
                :alt="document.name"
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
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 flex justify-between">
        <div class="flex gap-3">
          <button
            @click="downloadDocument"
            class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Download
          </button>
          <button
            v-if="document.requires_signature"
            @click="viewSignatures"
            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Ver Assinaturas
          </button>
        </div>
        <div class="flex gap-3">
          <button
            @click="$emit('edit', document)"
            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Editar
          </button>
          <button
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
          >
            Fechar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useToast } from '@/composables/useToast'

export default {
  name: 'DocumentViewModal',
  props: {
    document: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'edit'],
  setup(props) {
    const { showToast } = useToast()
    
    const signatureStats = ref(null)
    const recentSignatures = ref([])
    const imageError = ref(false)
    
    const getStatusClass = (status) => {
      const classes = {
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-red-100 text-red-800',
        draft: 'bg-gray-100 text-gray-800'
      }
      return classes[status] || classes.draft
    }
    
    const getStatusLabel = (status) => {
      const labels = {
        active: 'Ativo',
        inactive: 'Inativo',
        draft: 'Rascunho'
      }
      return labels[status] || status
    }
    
    const getTypeClass = (type) => {
      const classes = {
        contract: 'bg-blue-100 text-blue-800',
        policy: 'bg-green-100 text-green-800',
        manual: 'bg-purple-100 text-purple-800',
        form: 'bg-orange-100 text-orange-800',
        other: 'bg-gray-100 text-gray-800'
      }
      return classes[type] || classes.other
    }
    
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
    
    const getSignatureStatusClass = (status) => {
      const classes = {
        signed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        rejected: 'bg-red-100 text-red-800'
      }
      return classes[status] || classes.pending
    }
    
    const getSignatureStatusLabel = (status) => {
      const labels = {
        signed: 'Assinado',
        pending: 'Pendente',
        rejected: 'Rejeitado'
      }
      return labels[status] || status
    }
    
    const formatFileSize = (bytes) => {
      if (!bytes) return 'N/A'
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(1024))
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
    }
    
    const formatDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('pt-AO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    const isImage = (mimeType) => {
      return mimeType && mimeType.startsWith('image/')
    }
    
    const getFileUrl = (document) => {
      return `/api/documents/${document.id}/download`
    }
    
    const downloadDocument = async () => {
      try {
        const response = await fetch(`/api/documents/${props.document.id}/download`, {
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
        a.download = props.document.file_name
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
        document.body.removeChild(a)
        
        showToast('Download iniciado com sucesso!', 'success')
      } catch (error) {
        showToast('Erro ao baixar documento: ' + error.message, 'error')
      }
    }
    
    const viewSignatures = () => {
      // TODO: Implement signature management modal
      showToast('Funcionalidade de gerenciamento de assinaturas será implementada em breve', 'info')
    }
    
    const loadSignatureStats = async () => {
      if (!props.document.requires_signature) return
      
      try {
        const response = await fetch(`/api/documents/${props.document.id}/signatures/stats`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          signatureStats.value = data.data || data
        }
      } catch (error) {
        console.error('Erro ao carregar estatísticas de assinatura:', error)
      }
    }
    
    const loadRecentSignatures = async () => {
      if (!props.document.requires_signature) return
      
      try {
        const response = await fetch(`/api/documents/${props.document.id}/signatures?limit=5`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          recentSignatures.value = data.data || data
        }
      } catch (error) {
        console.error('Erro ao carregar assinaturas recentes:', error)
      }
    }
    
    onMounted(() => {
      loadSignatureStats()
      loadRecentSignatures()
    })
    
    return {
      signatureStats,
      recentSignatures,
      imageError,
      getStatusClass,
      getStatusLabel,
      getTypeClass,
      getTypeLabel,
      getSignatureStatusClass,
      getSignatureStatusLabel,
      formatFileSize,
      formatDate,
      isImage,
      getFileUrl,
      downloadDocument,
      viewSignatures
    }
  }
}
</script>