<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Templates</h1>
        <p class="text-gray-600 mt-1">Gerencie templates de documentos e formulários</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Novo Template
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nome do template..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
          <select
            v-model="filters.category"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todas as categorias</option>
            <option value="documento">Documentos</option>
            <option value="formulario">Formulários</option>
            <option value="contrato">Contratos</option>
            <option value="politica">Políticas</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
            <option value="rascunho">Rascunho</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="template in filteredTemplates"
        :key="template.id"
        class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow"
      >
        <div class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ template.name }}</h3>
              <p class="text-sm text-gray-600">{{ template.description }}</p>
            </div>
            <span :class="getStatusClass(template.status)" class="px-2 py-1 text-xs font-medium rounded-full">
              {{ getStatusLabel(template.status) }}
            </span>
          </div>
          
          <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
            <span>{{ template.category }}</span>
            <span>{{ formatDate(template.updated_at) }}</span>
          </div>
          
          <div class="flex gap-2">
            <button
              @click="editTemplate(template)"
              class="flex-1 bg-blue-50 text-blue-600 px-3 py-2 rounded-md hover:bg-blue-100 transition-colors text-sm font-medium"
            >
              Editar
            </button>
            <button
              @click="duplicateTemplate(template)"
              class="flex-1 bg-gray-50 text-gray-600 px-3 py-2 rounded-md hover:bg-gray-100 transition-colors text-sm font-medium"
            >
              Duplicar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredTemplates.length === 0" class="text-center py-12">
      <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum template encontrado</h3>
      <p class="text-gray-600 mb-4">Comece criando seu primeiro template</p>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
      >
        Criar Template
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'AdminTemplates',
  setup() {
    const templates = ref([])
    const showCreateModal = ref(false)
    const filters = ref({
      search: '',
      category: '',
      status: ''
    })

    const filteredTemplates = computed(() => {
      return templates.value.filter(template => {
        const matchesSearch = !filters.value.search || 
          template.name.toLowerCase().includes(filters.value.search.toLowerCase()) ||
          template.description.toLowerCase().includes(filters.value.search.toLowerCase())
        
        const matchesCategory = !filters.value.category || template.category === filters.value.category
        const matchesStatus = !filters.value.status || template.status === filters.value.status
        
        return matchesSearch && matchesCategory && matchesStatus
      })
    })

    const getStatusClass = (status) => {
      const classes = {
        ativo: 'bg-green-100 text-green-800',
        inativo: 'bg-red-100 text-red-800',
        rascunho: 'bg-yellow-100 text-yellow-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const getStatusLabel = (status) => {
      const labels = {
        ativo: 'Ativo',
        inativo: 'Inativo',
        rascunho: 'Rascunho'
      }
      return labels[status] || status
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('pt-AO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
      })
    }

    const editTemplate = (template) => {
      console.log('Editar template:', template)
    }

    const duplicateTemplate = (template) => {
      console.log('Duplicar template:', template)
    }

    // Mock data for demonstration
    onMounted(() => {
      templates.value = [
        {
          id: 1,
          name: 'Contrato de Trabalho',
          description: 'Template padrão para contratos de trabalho',
          category: 'contrato',
          status: 'ativo',
          updated_at: '2024-01-15'
        },
        {
          id: 2,
          name: 'Formulário de Avaliação',
          description: 'Template para avaliações de desempenho',
          category: 'formulario',
          status: 'ativo',
          updated_at: '2024-01-10'
        },
        {
          id: 3,
          name: 'Política de Segurança',
          description: 'Template para políticas internas',
          category: 'politica',
          status: 'rascunho',
          updated_at: '2024-01-05'
        }
      ]
    })

    return {
      templates,
      showCreateModal,
      filters,
      filteredTemplates,
      getStatusClass,
      getStatusLabel,
      formatDate,
      editTemplate,
      duplicateTemplate
    }
  }
}
</script>