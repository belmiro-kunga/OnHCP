<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Assinaturas de Documentos</h1>
      <p class="text-gray-600 mt-1">Gerencie assinaturas de documentos e acompanhe o status</p>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nome do documento..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="pending">Pendente</option>
            <option value="signed">Assinado</option>
            <option value="rejected">Rejeitado</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
          <select
            v-model="filters.department"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os departamentos</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="clearFilters"
            class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
          >
            Limpar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-blue-50 p-4 rounded-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-blue-600">Total de Documentos</p>
            <p class="text-2xl font-bold text-blue-900">{{ stats.total || 0 }}</p>
          </div>
          <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
          </svg>
        </div>
      </div>
      <div class="bg-yellow-50 p-4 rounded-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-yellow-600">Pendentes</p>
            <p class="text-2xl font-bold text-yellow-900">{{ stats.pending || 0 }}</p>
          </div>
          <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
      <div class="bg-green-50 p-4 rounded-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-green-600">Assinados</p>
            <p class="text-2xl font-bold text-green-900">{{ stats.signed || 0 }}</p>
          </div>
          <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
      <div class="bg-red-50 p-4 rounded-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-red-600">Rejeitados</p>
            <p class="text-2xl font-bold text-red-900">{{ stats.rejected || 0 }}</p>
          </div>
          <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
      </div>
    </div>

    <!-- Documents List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900">Documentos para Assinatura</h2>
      </div>
      
      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="mt-2 text-gray-600">Carregando documentos...</p>
      </div>
      
      <div v-else-if="filteredSignatures.length === 0" class="p-8 text-center text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p>Nenhum documento encontrado</p>
      </div>
      
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="signature in paginatedSignatures" :key="signature.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ signature.document?.name }}</p>
                    <p class="text-sm text-gray-500">{{ signature.document?.file_name }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getTypeClass(signature.document?.type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getTypeLabel(signature.document?.type) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ signature.document?.department?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(signature.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getStatusLabel(signature.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-500">
                {{ formatDate(signature.signed_at || signature.created_at) }}
              </td>
              <td class="px-6 py-4">
                <div class="flex space-x-2">
                  <button
                    @click="viewDocument(signature)"
                    class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                    title="Visualizar documento"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button
                    v-if="signature.status === 'pending'"
                    @click="signDocument(signature)"
                    class="text-green-600 hover:text-green-900 text-sm font-medium"
                    title="Assinar documento"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </button>
                  <button
                    v-if="signature.status === 'pending'"
                    @click="rejectSignature(signature)"
                    class="text-red-600 hover:text-red-900 text-sm font-medium"
                    title="Rejeitar assinatura"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                  </button>
                  <button
                    @click="downloadDocument(signature.document)"
                    class="text-purple-600 hover:text-purple-900 text-sm font-medium"
                    title="Download documento"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination -->
      <div v-if="totalPages > 1" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ (currentPage - 1) * itemsPerPage + 1 }} a {{ Math.min(currentPage * itemsPerPage, filteredSignatures.length) }} de {{ filteredSignatures.length }} resultados
          </div>
          <div class="flex space-x-2">
            <button
              @click="currentPage--"
              :disabled="currentPage === 1"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <span class="px-3 py-1 text-sm bg-blue-50 text-blue-600 border border-blue-200 rounded-md">
              {{ currentPage }} de {{ totalPages }}
            </span>
            <button
              @click="currentPage++"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Próximo
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Signature Modal -->
    <SignatureModal
      v-if="showSignatureModal"
      :signature="selectedSignature"
      @close="closeSignatureModal"
      @signed="handleSignatureSigned"
    />

    <!-- Document View Modal -->
    <DocumentViewModal
      v-if="showDocumentModal"
      :document="selectedDocument"
      @close="closeDocumentModal"
    />
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import SignatureModal from './SignatureModal.vue'
import DocumentViewModal from './DocumentViewModal.vue'

export default {
  name: 'DocumentSignatures',
  components: {
    SignatureModal,
    DocumentViewModal
  },
  setup() {
    const { showToast } = useToast()
    
    const loading = ref(false)
    const signatures = ref([])
    const departments = ref([])
    const stats = ref({})
    const selectedSignature = ref(null)
    const selectedDocument = ref(null)
    const showSignatureModal = ref(false)
    const showDocumentModal = ref(false)
    
    const filters = ref({
      search: '',
      status: '',
      department: ''
    })
    
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    
    const filteredSignatures = computed(() => {
      let filtered = signatures.value
      
      if (filters.value.search) {
        const search = filters.value.search.toLowerCase()
        filtered = filtered.filter(signature => 
          signature.document?.name?.toLowerCase().includes(search) ||
          signature.document?.file_name?.toLowerCase().includes(search)
        )
      }
      
      if (filters.value.status) {
        filtered = filtered.filter(signature => signature.status === filters.value.status)
      }
      
      if (filters.value.department) {
        filtered = filtered.filter(signature => signature.document?.department_id == filters.value.department)
      }
      
      return filtered
    })
    
    const totalPages = computed(() => {
      return Math.ceil(filteredSignatures.value.length / itemsPerPage.value)
    })
    
    const paginatedSignatures = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value
      const end = start + itemsPerPage.value
      return filteredSignatures.value.slice(start, end)
    })
    
    const getStatusClass = (status) => {
      const classes = {
        pending: 'bg-yellow-100 text-yellow-800',
        signed: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
      }
      return classes[status] || classes.pending
    }
    
    const getStatusLabel = (status) => {
      const labels = {
        pending: 'Pendente',
        signed: 'Assinado',
        rejected: 'Rejeitado'
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
    
    const formatDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('pt-AO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
    
    const loadSignatures = async () => {
      loading.value = true
      try {
        const response = await fetch('/api/document-signatures/user', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          signatures.value = data.data || data
        } else {
          throw new Error('Erro ao carregar assinaturas')
        }
      } catch (error) {
        showToast('Erro ao carregar assinaturas: ' + error.message, 'error')
      } finally {
        loading.value = false
      }
    }
    
    const loadDepartments = async () => {
      try {
        const response = await fetch('/api/departments', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          departments.value = data.data || data
        }
      } catch (error) {
        console.error('Erro ao carregar departamentos:', error)
      }
    }
    
    const loadStats = async () => {
      try {
        const response = await fetch('/api/document-signatures/stats', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          stats.value = data.data || data
        }
      } catch (error) {
        console.error('Erro ao carregar estatísticas:', error)
      }
    }
    
    const clearFilters = () => {
      filters.value = {
        search: '',
        status: '',
        department: ''
      }
      currentPage.value = 1
    }
    
    const viewDocument = (signature) => {
      selectedDocument.value = signature.document
      showDocumentModal.value = true
    }
    
    const signDocument = (signature) => {
      selectedSignature.value = signature
      showSignatureModal.value = true
    }
    
    const rejectSignature = async (signature) => {
      if (!confirm('Tem certeza que deseja rejeitar esta assinatura?')) {
        return
      }
      
      try {
        const response = await fetch(`/api/document-signatures/${signature.id}/reject`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (response.ok) {
          showToast('Assinatura rejeitada com sucesso!', 'success')
          loadSignatures()
          loadStats()
        } else {
          throw new Error('Erro ao rejeitar assinatura')
        }
      } catch (error) {
        showToast('Erro ao rejeitar assinatura: ' + error.message, 'error')
      }
    }
    
    const downloadDocument = async (document) => {
      try {
        const response = await fetch(`/api/documents/${document.id}/download`, {
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
        a.download = document.file_name
        document.body.appendChild(a)
        a.click()
        window.URL.revokeObjectURL(url)
        document.body.removeChild(a)
        
        showToast('Download iniciado com sucesso!', 'success')
      } catch (error) {
        showToast('Erro ao baixar documento: ' + error.message, 'error')
      }
    }
    
    const closeSignatureModal = () => {
      showSignatureModal.value = false
      selectedSignature.value = null
    }
    
    const closeDocumentModal = () => {
      showDocumentModal.value = false
      selectedDocument.value = null
    }
    
    const handleSignatureSigned = () => {
      closeSignatureModal()
      loadSignatures()
      loadStats()
    }
    
    onMounted(() => {
      loadSignatures()
      loadDepartments()
      loadStats()
    })
    
    return {
      loading,
      signatures,
      departments,
      stats,
      filters,
      currentPage,
      itemsPerPage,
      filteredSignatures,
      totalPages,
      paginatedSignatures,
      selectedSignature,
      selectedDocument,
      showSignatureModal,
      showDocumentModal,
      getStatusClass,
      getStatusLabel,
      getTypeClass,
      getTypeLabel,
      formatDate,
      clearFilters,
      viewDocument,
      signDocument,
      rejectSignature,
      downloadDocument,
      closeSignatureModal,
      closeDocumentModal,
      handleSignatureSigned
    }
  }
}
</script>