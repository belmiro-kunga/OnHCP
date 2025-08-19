<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Gestão de Documentos</h1>
          <p class="text-gray-600 mt-1">Gerencie documentos, contratos e políticas do onboarding</p>
        </div>
        <button
          @click="openCreateModal"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Novo Documento
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nome ou descrição..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
          <select
            v-model="filters.type"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os tipos</option>
            <option value="contract">Contrato</option>
            <option value="policy">Política</option>
            <option value="manual">Manual</option>
            <option value="form">Formulário</option>
            <option value="other">Outro</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
          <select
            v-model="filters.department_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os departamentos</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
            <option value="draft">Rascunho</option>
          </select>
        </div>
      </div>
      <div class="flex justify-end mt-4">
        <button
          @click="clearFilters"
          class="text-gray-600 hover:text-gray-800 text-sm flex items-center gap-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          Limpar Filtros
        </button>
      </div>
    </div>

    <!-- Documents List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
      <!-- Loading State -->
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Carregando documentos...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="filteredDocuments.length === 0" class="p-8 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum documento encontrado</h3>
        <p class="text-gray-600 mb-4">Não há documentos que correspondam aos filtros aplicados.</p>
        <button
          @click="clearFilters"
          class="text-blue-600 hover:text-blue-700 font-medium"
        >
          Limpar filtros
        </button>
      </div>

      <!-- Documents Table -->
      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Documento
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tipo
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Departamento
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Tamanho
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Criado em
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ações
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="document in paginatedDocuments" :key="document.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center">
                      <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ document.name }}</div>
                    <div class="text-sm text-gray-500">{{ document.file_name }}</div>
                    <div v-if="document.description" class="text-xs text-gray-400 mt-1">{{ document.description }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getTypeClass(document.type)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getTypeLabel(document.type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ document.department?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(document.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                  {{ getStatusLabel(document.status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatFileSize(document.file_size) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatDate(document.created_at) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end gap-2">
                  <button
                    @click="downloadDocument(document)"
                    class="text-blue-600 hover:text-blue-900 p-1 rounded"
                    title="Download"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </button>
                  <button
                    @click="openViewModal(document)"
                    class="text-green-600 hover:text-green-900 p-1 rounded"
                    title="Visualizar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button
                    @click="openEditModal(document)"
                    class="text-yellow-600 hover:text-yellow-900 p-1 rounded"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    @click="deleteDocument(document)"
                    class="text-red-600 hover:text-red-900 p-1 rounded"
                    title="Excluir"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="px-6 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Mostrando {{ (pagination.current_page - 1) * pagination.per_page + 1 }} a 
            {{ Math.min(pagination.current_page * pagination.per_page, filteredDocuments.length) }} 
            de {{ filteredDocuments.length }} documentos
          </div>
          <div class="flex gap-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page === 1"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Anterior
            </button>
            <span class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md">
              {{ pagination.current_page }}
            </span>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page === totalPages"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Próxima
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <DocumentModal
      v-if="showCreateModal"
      :departments="departments"
      @close="closeModal"
      @saved="handleModalSaved"
    />
    
    <DocumentModal
      v-if="showEditModal"
      :document="selectedDocument"
      :departments="departments"
      @close="closeModal"
      @saved="handleModalSaved"
    />
    
    <DocumentViewModal
      v-if="showViewModal"
      :document="selectedDocument"
      @close="closeModal"
      @edit="handleEditFromView"
    />
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useToast } from '@/composables/useToast'
import DocumentModal from './DocumentModal.vue'
import DocumentViewModal from './DocumentViewModal.vue'

export default {
  name: 'DocumentManagement',
  components: {
    DocumentModal,
    DocumentViewModal
  },
  setup() {
    const { showToast } = useToast()
    
    const loading = ref(false)
    const documents = ref([])
    const departments = ref([])
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const showViewModal = ref(false)
    const selectedDocument = ref(null)
    
    const filters = reactive({
      search: '',
      type: '',
      department_id: '',
      status: ''
    })
    
    const pagination = reactive({
      current_page: 1,
      per_page: 10
    })
    
    const filteredDocuments = computed(() => {
      let filtered = documents.value
      
      if (filters.search) {
        const search = filters.search.toLowerCase()
        filtered = filtered.filter(doc => 
          doc.name.toLowerCase().includes(search) ||
          (doc.description && doc.description.toLowerCase().includes(search)) ||
          doc.file_name.toLowerCase().includes(search)
        )
      }
      
      if (filters.type) {
        filtered = filtered.filter(doc => doc.type === filters.type)
      }
      
      if (filters.department_id) {
        filtered = filtered.filter(doc => doc.department_id == filters.department_id)
      }
      
      if (filters.status) {
        filtered = filtered.filter(doc => doc.status === filters.status)
      }
      
      return filtered
    })
    
    const totalPages = computed(() => {
      return Math.ceil(filteredDocuments.value.length / pagination.per_page)
    })
    
    const paginatedDocuments = computed(() => {
      const start = (pagination.current_page - 1) * pagination.per_page
      const end = start + pagination.per_page
      return filteredDocuments.value.slice(start, end)
    })
    
    const loadDocuments = async () => {
      loading.value = true
      try {
        const response = await fetch('/api/documents', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (!response.ok) {
          throw new Error('Erro ao carregar documentos')
        }
        
        const data = await response.json()
        documents.value = data.data || data
      } catch (error) {
        showToast('Erro ao carregar documentos: ' + error.message, 'error')
      } finally {
        loading.value = false
      }
    }
    
    const loadDepartments = async () => {
      try {
        const response = await fetch('/api/documents/departments', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (!response.ok) {
          throw new Error('Erro ao carregar departamentos')
        }
        
        const data = await response.json()
        departments.value = data.data || data
      } catch (error) {
        console.error('Erro ao carregar departamentos:', error)
      }
    }
    
    const openCreateModal = () => {
      selectedDocument.value = null
      showCreateModal.value = true
    }
    
    const openEditModal = (document) => {
      selectedDocument.value = document
      showEditModal.value = true
    }
    
    const openViewModal = (document) => {
      selectedDocument.value = document
      showViewModal.value = true
    }
    
    const closeModal = () => {
      showCreateModal.value = false
      showEditModal.value = false
      showViewModal.value = false
      selectedDocument.value = null
    }
    
    const handleModalSaved = () => {
      closeModal()
      loadDocuments()
    }
    
    const handleEditFromView = (document) => {
      selectedDocument.value = document
      showViewModal.value = false
      showModal.value = true
      isEditing.value = true
      showEditModal.value = true
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
    
    const deleteDocument = async (document) => {
      if (!confirm(`Tem certeza que deseja excluir o documento "${document.name}"?`)) {
        return
      }
      
      try {
        const response = await fetch(`/api/documents/${document.id}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (!response.ok) {
          throw new Error('Erro ao excluir documento')
        }
        
        showToast('Documento excluído com sucesso!', 'success')
        loadDocuments()
      } catch (error) {
        showToast('Erro ao excluir documento: ' + error.message, 'error')
      }
    }
    
    const changePage = (page) => {
      if (page >= 1 && page <= totalPages.value) {
        pagination.current_page = page
      }
    }
    
    const clearFilters = () => {
      Object.assign(filters, {
        search: '',
        type: '',
        department_id: '',
        status: ''
      })
      pagination.current_page = 1
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
    
    const formatFileSize = (bytes) => {
      if (!bytes) return 'N/A'
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(1024))
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
    }
    
    const formatDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('pt-AO')
    }
    
    onMounted(() => {
      loadDocuments()
      loadDepartments()
    })
    
    return {
      loading,
      documents,
      departments,
      filters,
      pagination,
      showCreateModal,
      showEditModal,
      showViewModal,
      selectedDocument,
      filteredDocuments,
      totalPages,
      paginatedDocuments,
      loadDocuments,
      loadDepartments,
      openCreateModal,
      openEditModal,
      openViewModal,
      closeModal,
      handleModalSaved,
      handleEditFromView,
      downloadDocument,
      deleteDocument,
      changePage,
      clearFilters,
      getTypeClass,
      getTypeLabel,
      getStatusClass,
      getStatusLabel,
      formatFileSize,
      formatDate
    }
  }
}
</script>