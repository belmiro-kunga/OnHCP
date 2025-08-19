<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Roteiros de Onboarding</h1>
        <p class="text-gray-600 mt-1">Gerencie os roteiros de integração para novos funcionários</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Novo Roteiro
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
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
            <option value="draft">Rascunho</option>
            <option value="active">Ativo</option>
            <option value="inactive">Inativo</option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="clearFilters"
            class="w-full px-3 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
          >
            Limpar Filtros
          </button>
        </div>
      </div>
    </div>

    <!-- Roadmaps List -->
    <div class="bg-white rounded-lg shadow-sm border">
      <div v-if="loading" class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-2">Carregando roteiros...</p>
      </div>

      <div v-else-if="roadmaps.length === 0" class="p-8 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <p class="text-gray-600">Nenhum roteiro encontrado</p>
        <button
          @click="showCreateModal = true"
          class="mt-4 text-blue-600 hover:text-blue-700 font-medium"
        >
          Criar primeiro roteiro
        </button>
      </div>

      <div v-else>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roteiro</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duração</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado por</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="roadmap in roadmaps" :key="roadmap.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ roadmap.name }}</div>
                    <div class="text-sm text-gray-500">{{ roadmap.description }}</div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-900">{{ roadmap.department?.name || 'N/A' }}</span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-900">{{ roadmap.estimated_duration_days }} dias</span>
                </td>
                <td class="px-6 py-4">
                  <span :class="getStatusClass(roadmap.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                    {{ getStatusLabel(roadmap.status) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm text-gray-900">{{ roadmap.creator?.name || 'N/A' }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="viewRoadmap(roadmap)"
                      class="text-blue-600 hover:text-blue-700 p-1"
                      title="Visualizar"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                      </svg>
                    </button>
                    <button
                      @click="editRoadmap(roadmap)"
                      class="text-green-600 hover:text-green-700 p-1"
                      title="Editar"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                    <button
                      @click="duplicateRoadmap(roadmap)"
                      class="text-purple-600 hover:text-purple-700 p-1"
                      title="Duplicar"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                      </svg>
                    </button>
                    <button
                      @click="deleteRoadmap(roadmap)"
                      class="text-red-600 hover:text-red-700 p-1"
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
        <div v-if="pagination.total > pagination.per_page" class="px-6 py-4 border-t">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
            </div>
            <div class="flex gap-2">
              <button
                @click="changePage(pagination.current_page - 1)"
                :disabled="pagination.current_page <= 1"
                class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
              >
                Anterior
              </button>
              <button
                @click="changePage(pagination.current_page + 1)"
                :disabled="pagination.current_page >= pagination.last_page"
                class="px-3 py-1 text-sm border rounded disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
              >
                Próximo
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <RoadmapModal
      v-if="showCreateModal || showEditModal"
      :roadmap="selectedRoadmap"
      :departments="departments"
      @close="closeModal"
      @saved="handleRoadmapSaved"
    />

    <!-- Modals -->
    <RoadmapModal
      v-if="showCreateModal"
      :departments="departments"
      @close="closeModal"
      @saved="handleModalSaved"
    />
    
    <RoadmapModal
      v-if="showEditModal"
      :roadmap="selectedRoadmap"
      :departments="departments"
      @close="closeModal"
      @saved="handleModalSaved"
    />
    
    <RoadmapViewModal
      v-if="showViewModal"
      :roadmap="selectedRoadmap"
      @close="closeModal"
      @edit="handleEditFromView"
    />
  </div>
</template>

<script>
import { ref, reactive, onMounted, watch } from 'vue'
import { useToast } from '@/composables/useToast'
import RoadmapModal from './RoadmapModal.vue'
import RoadmapViewModal from './RoadmapViewModal.vue'

export default {
  name: 'OnboardingRoadmaps',
  components: {
    RoadmapModal,
    RoadmapViewModal
  },
  setup() {
    const { showToast } = useToast()
    
    const loading = ref(false)
    const roadmaps = ref([])
    const departments = ref([])
    const selectedRoadmap = ref(null)
    const showCreateModal = ref(false)
    const showEditModal = ref(false)
    const showViewModal = ref(false)
    
    const filters = reactive({
      search: '',
      department_id: '',
      status: ''
    })
    
    const pagination = reactive({
      current_page: 1,
      per_page: 15,
      total: 0,
      from: 0,
      to: 0,
      last_page: 1
    })
    
    const fetchRoadmaps = async (page = 1) => {
      loading.value = true
      try {
        const params = new URLSearchParams({
          page: page.toString(),
          per_page: pagination.per_page.toString(),
          ...Object.fromEntries(Object.entries(filters).filter(([_, v]) => v !== ''))
        })
        
        const response = await fetch(`/api/onboarding-roadmaps?${params}`, {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (!response.ok) throw new Error('Erro ao carregar roteiros')
        
        const data = await response.json()
        roadmaps.value = data.data
        Object.assign(pagination, {
          current_page: data.current_page,
          per_page: data.per_page,
          total: data.total,
          from: data.from,
          to: data.to,
          last_page: data.last_page
        })
      } catch (error) {
        showToast('Erro ao carregar roteiros: ' + error.message, 'error')
      } finally {
        loading.value = false
      }
    }
    
    const fetchDepartments = async () => {
      try {
        const response = await fetch('/api/onboarding-roadmaps/departments', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (!response.ok) throw new Error('Erro ao carregar departamentos')
        
        departments.value = await response.json()
      } catch (error) {
        showToast('Erro ao carregar departamentos: ' + error.message, 'error')
      }
    }
    
    const viewRoadmap = (roadmap) => {
      selectedRoadmap.value = roadmap
      showViewModal.value = true
    }
    
    const editRoadmap = (roadmap) => {
      selectedRoadmap.value = roadmap
      showViewModal.value = false
      showEditModal.value = true
    }
    
    const duplicateRoadmap = async (roadmap) => {
      try {
        const response = await fetch(`/api/onboarding-roadmaps/${roadmap.id}/duplicate`, {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (!response.ok) throw new Error('Erro ao duplicar roteiro')
        
        showToast('Roteiro duplicado com sucesso!', 'success')
        fetchRoadmaps(pagination.current_page)
      } catch (error) {
        showToast('Erro ao duplicar roteiro: ' + error.message, 'error')
      }
    }
    
    const deleteRoadmap = async (roadmap) => {
      if (!confirm(`Tem certeza que deseja excluir o roteiro "${roadmap.name}"?`)) return
      
      try {
        const response = await fetch(`/api/onboarding-roadmaps/${roadmap.id}`, {
          method: 'DELETE',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          }
        })
        
        if (!response.ok) {
          const error = await response.json()
          throw new Error(error.message || 'Erro ao excluir roteiro')
        }
        
        showToast('Roteiro excluído com sucesso!', 'success')
        fetchRoadmaps(pagination.current_page)
      } catch (error) {
        showToast('Erro ao excluir roteiro: ' + error.message, 'error')
      }
    }
    
    const closeModal = () => {
      showCreateModal.value = false
      showEditModal.value = false
      selectedRoadmap.value = null
    }
    
    const handleRoadmapSaved = () => {
      closeModal()
      fetchRoadmaps(pagination.current_page)
    }
    
    const changePage = (page) => {
      if (page >= 1 && page <= pagination.last_page) {
        fetchRoadmaps(page)
      }
    }
    
    const clearFilters = () => {
      filters.search = ''
      filters.department_id = ''
      filters.status = ''
    }
    
    const getStatusClass = (status) => {
      const classes = {
        draft: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }
    
    const getStatusLabel = (status) => {
      const labels = {
        draft: 'Rascunho',
        active: 'Ativo',
        inactive: 'Inativo'
      }
      return labels[status] || status
    }
    
    // Watch filters for auto-search
    watch(filters, () => {
      fetchRoadmaps(1)
    }, { deep: true })
    
    onMounted(() => {
      fetchRoadmaps()
      fetchDepartments()
    })
    
    const handleModalSaved = () => {
      closeModal()
      fetchRoadmaps(pagination.current_page)
    }
    
    const handleEditFromView = (roadmap) => {
      showViewModal.value = false
      selectedRoadmap.value = roadmap
      showEditModal.value = true
    }
    
    return {
      loading,
      roadmaps,
      departments,
      selectedRoadmap,
      showCreateModal,
      showEditModal,
      showViewModal,
      filters,
      pagination,
      viewRoadmap,
      editRoadmap,
      duplicateRoadmap,
      deleteRoadmap,
      closeModal,
      handleRoadmapSaved,
      changePage,
      clearFilters,
      getStatusClass,
      getStatusLabel,
      handleModalSaved,
      handleEditFromView
    }
  }
}
</script>