<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Checklists</h1>
        <p class="text-gray-600 mt-1">Gerencie listas de verificação para processos e tarefas</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Novo Checklist
      </button>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Nome do checklist..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
          <select
            v-model="selectedCategory"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todas as categorias</option>
            <option value="onboarding">Integração</option>
            <option value="offboarding">Desligamento</option>
            <option value="training">Treinamento</option>
            <option value="compliance">Conformidade</option>
            <option value="safety">Segurança</option>
            <option value="quality">Qualidade</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="selectedStatus"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="active">Ativo</option>
            <option value="draft">Rascunho</option>
            <option value="archived">Arquivado</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
          <select
            v-model="selectedDepartment"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os departamentos</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">
              {{ dept.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Checklists Grid -->
    <div v-if="filteredChecklists.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="checklist in filteredChecklists"
        :key="checklist.id"
        class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow cursor-pointer"
        @click="viewChecklist(checklist)"
      >
        <div class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ checklist.name }}</h3>
              <p class="text-gray-600 text-sm mb-3">{{ checklist.description }}</p>
              
              <div class="flex items-center gap-2 mb-3">
                <span :class="getCategoryClass(checklist.category)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ getCategoryLabel(checklist.category) }}
                </span>
                <span :class="getStatusClass(checklist.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ getStatusLabel(checklist.status) }}
                </span>
              </div>
            </div>
            
            <div class="flex gap-1">
              <button
                @click.stop="editChecklist(checklist)"
                class="text-gray-400 hover:text-blue-600 p-1"
                title="Editar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </button>
              <button
                @click.stop="duplicateChecklist(checklist)"
                class="text-gray-400 hover:text-green-600 p-1"
                title="Duplicar"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
              </button>
              <button
                @click.stop="deleteChecklist(checklist)"
                class="text-gray-400 hover:text-red-600 p-1"
                title="Excluir"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
          
          <div class="space-y-2">
            <div class="flex justify-between text-sm">
              <span class="text-gray-600">Progresso</span>
              <span class="font-medium">{{ checklist.completed_items }}/{{ checklist.total_items }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: `${getCompletionPercentage(checklist)}%` }"
              ></div>
            </div>
          </div>
          
          <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
            <div class="text-xs text-gray-500">
              Criado em {{ formatDate(checklist.created_at) }}
            </div>
            <div class="text-xs text-gray-500">
              {{ checklist.department?.name || 'Geral' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-12">
      <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum checklist encontrado</h3>
      <p class="text-gray-600 mb-4">Crie seu primeiro checklist para organizar processos e tarefas</p>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
      >
        Criar Primeiro Checklist
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'AdminChecklist',
  setup() {
    const checklists = ref([])
    const departments = ref([])
    const showCreateModal = ref(false)
    const searchTerm = ref('')
    const selectedCategory = ref('')
    const selectedStatus = ref('')
    const selectedDepartment = ref('')

    const filteredChecklists = computed(() => {
      return checklists.value.filter(checklist => {
        const matchesSearch = !searchTerm.value || 
          checklist.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
          checklist.description.toLowerCase().includes(searchTerm.value.toLowerCase())
        
        const matchesCategory = !selectedCategory.value || checklist.category === selectedCategory.value
        const matchesStatus = !selectedStatus.value || checklist.status === selectedStatus.value
        const matchesDepartment = !selectedDepartment.value || checklist.department_id === selectedDepartment.value
        
        return matchesSearch && matchesCategory && matchesStatus && matchesDepartment
      })
    })

    const getCategoryClass = (category) => {
      const classes = {
        onboarding: 'bg-green-100 text-green-800',
        offboarding: 'bg-red-100 text-red-800',
        training: 'bg-blue-100 text-blue-800',
        compliance: 'bg-purple-100 text-purple-800',
        safety: 'bg-yellow-100 text-yellow-800',
        quality: 'bg-indigo-100 text-indigo-800'
      }
      return classes[category] || 'bg-gray-100 text-gray-800'
    }

    const getCategoryLabel = (category) => {
      const labels = {
        onboarding: 'Integração',
        offboarding: 'Desligamento',
        training: 'Treinamento',
        compliance: 'Conformidade',
        safety: 'Segurança',
        quality: 'Qualidade'
      }
      return labels[category] || category
    }

    const getStatusClass = (status) => {
      const classes = {
        active: 'bg-green-100 text-green-800',
        draft: 'bg-yellow-100 text-yellow-800',
        archived: 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const getStatusLabel = (status) => {
      const labels = {
        active: 'Ativo',
        draft: 'Rascunho',
        archived: 'Arquivado'
      }
      return labels[status] || status
    }

    const getCompletionPercentage = (checklist) => {
      if (checklist.total_items === 0) return 0
      return Math.round((checklist.completed_items / checklist.total_items) * 100)
    }

    const formatDate = (dateString) => {
      return new Date(dateString).toLocaleDateString('pt-AO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    }

    const viewChecklist = (checklist) => {
      console.log('Visualizar checklist:', checklist)
    }

    const editChecklist = (checklist) => {
      console.log('Editar checklist:', checklist)
    }

    const duplicateChecklist = (checklist) => {
      console.log('Duplicar checklist:', checklist)
    }

    const deleteChecklist = (checklist) => {
      if (confirm(`Tem certeza que deseja excluir o checklist "${checklist.name}"?`)) {
        console.log('Excluir checklist:', checklist)
      }
    }

    // Mock data for demonstration
    onMounted(() => {
      departments.value = [
        { id: 1, name: 'Recursos Humanos' },
        { id: 2, name: 'Tecnologia da Informação' },
        { id: 3, name: 'Financeiro' },
        { id: 4, name: 'Operações' },
        { id: 5, name: 'Qualidade' }
      ]

      checklists.value = [
        {
          id: 1,
          name: 'Integração de Novos Funcionários',
          description: 'Lista completa para integração de novos colaboradores',
          category: 'onboarding',
          status: 'active',
          department_id: 1,
          department: { name: 'Recursos Humanos' },
          total_items: 15,
          completed_items: 12,
          created_at: '2024-01-15T10:00:00Z'
        },
        {
          id: 2,
          name: 'Configuração de Equipamentos TI',
          description: 'Checklist para configuração de equipamentos de TI',
          category: 'training',
          status: 'active',
          department_id: 2,
          department: { name: 'Tecnologia da Informação' },
          total_items: 8,
          completed_items: 8,
          created_at: '2024-01-10T14:30:00Z'
        },
        {
          id: 3,
          name: 'Auditoria de Segurança',
          description: 'Verificações de segurança mensal',
          category: 'safety',
          status: 'draft',
          department_id: 5,
          department: { name: 'Qualidade' },
          total_items: 20,
          completed_items: 5,
          created_at: '2024-01-20T09:15:00Z'
        },
        {
          id: 4,
          name: 'Processo de Desligamento',
          description: 'Etapas para desligamento de funcionários',
          category: 'offboarding',
          status: 'active',
          department_id: 1,
          department: { name: 'Recursos Humanos' },
          total_items: 12,
          completed_items: 0,
          created_at: '2024-01-05T16:45:00Z'
        },
        {
          id: 5,
          name: 'Conformidade GDPR',
          description: 'Verificações de conformidade com proteção de dados',
          category: 'compliance',
          status: 'active',
          department_id: 2,
          department: { name: 'Tecnologia da Informação' },
          total_items: 25,
          completed_items: 18,
          created_at: '2024-01-12T11:20:00Z'
        },
        {
          id: 6,
          name: 'Controle de Qualidade',
          description: 'Checklist de qualidade para produtos',
          category: 'quality',
          status: 'archived',
          department_id: 5,
          department: { name: 'Qualidade' },
          total_items: 30,
          completed_items: 30,
          created_at: '2023-12-20T13:10:00Z'
        }
      ]
    })

    return {
      checklists,
      departments,
      showCreateModal,
      searchTerm,
      selectedCategory,
      selectedStatus,
      selectedDepartment,
      filteredChecklists,
      getCategoryClass,
      getCategoryLabel,
      getStatusClass,
      getStatusLabel,
      getCompletionPercentage,
      formatDate,
      viewChecklist,
      editChecklist,
      duplicateChecklist,
      deleteChecklist
    }
  }
}
</script>