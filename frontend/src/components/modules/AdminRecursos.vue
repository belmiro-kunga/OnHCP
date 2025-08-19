<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Recursos</h1>
        <p class="text-gray-600 mt-1">Gerencie recursos, materiais e equipamentos da organização</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Adicionar Recurso
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
            placeholder="Nome ou código..."
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
            <option value="equipment">Equipamentos</option>
            <option value="software">Software</option>
            <option value="furniture">Mobiliário</option>
            <option value="vehicle">Veículos</option>
            <option value="material">Materiais</option>
            <option value="tool">Ferramentas</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="selectedStatus"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="available">Disponível</option>
            <option value="in_use">Em Uso</option>
            <option value="maintenance">Manutenção</option>
            <option value="retired">Aposentado</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Localização</label>
          <select
            v-model="selectedLocation"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todas as localizações</option>
            <option v-for="location in locations" :key="location.id" :value="location.id">
              {{ location.name }}
            </option>
          </select>
        </div>
      </div>
    </div>

    <!-- Resources Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recurso</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responsável</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="resource in filteredResources" :key="resource.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="getResourceIcon(resource.category)"></path>
                    </svg>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ resource.name }}</div>
                    <div class="text-sm text-gray-500">{{ resource.code }}</div>
                    <div class="text-xs text-gray-400">{{ resource.description }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <span :class="getCategoryClass(resource.category)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ getCategoryLabel(resource.category) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span :class="getStatusClass(resource.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                  {{ getStatusLabel(resource.status) }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">{{ resource.location?.name || 'N/A' }}</td>
              <td class="px-6 py-4">
                <div v-if="resource.assigned_to" class="flex items-center">
                  <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mr-2">
                    <span class="text-xs font-medium text-blue-600">{{ getInitials(resource.assigned_to.name) }}</span>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ resource.assigned_to.name }}</div>
                    <div class="text-xs text-gray-500">{{ resource.assigned_to.department }}</div>
                  </div>
                </div>
                <div v-else class="text-sm text-gray-500 italic">Não atribuído</div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900">
                {{ formatCurrency(resource.value) }}
              </td>
              <td class="px-6 py-4 text-sm font-medium">
                <div class="flex gap-2">
                  <button
                    @click="viewResource(resource)"
                    class="text-blue-600 hover:text-blue-900"
                    title="Visualizar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                  </button>
                  <button
                    @click="editResource(resource)"
                    class="text-green-600 hover:text-green-900"
                    title="Editar"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </button>
                  <button
                    @click="assignResource(resource)"
                    class="text-purple-600 hover:text-purple-900"
                    title="Atribuir"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </button>
                  <button
                    @click="deleteResource(resource)"
                    class="text-red-600 hover:text-red-900"
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
    </div>

    <!-- Empty State -->
    <div v-if="filteredResources.length === 0" class="text-center py-12">
      <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum recurso encontrado</h3>
      <p class="text-gray-600 mb-4">Comece adicionando recursos para gerenciar o inventário</p>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
      >
        Adicionar Primeiro Recurso
      </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
      <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
          </div>
          <div>
            <div class="text-2xl font-bold text-gray-900">{{ resources.length }}</div>
            <div class="text-sm text-gray-600">Total de Recursos</div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <div class="text-2xl font-bold text-gray-900">{{ getResourcesByStatus('available').length }}</div>
            <div class="text-sm text-gray-600">Disponíveis</div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div>
            <div class="text-2xl font-bold text-gray-900">{{ getResourcesByStatus('maintenance').length }}</div>
            <div class="text-sm text-gray-600">Em Manutenção</div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-lg shadow-sm border p-4">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(getTotalValue()) }}</div>
            <div class="text-sm text-gray-600">Valor Total</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

export default {
  name: 'AdminRecursos',
  setup() {
    const resources = ref([])
    const locations = ref([])
    const showCreateModal = ref(false)
    const searchTerm = ref('')
    const selectedCategory = ref('')
    const selectedStatus = ref('')
    const selectedLocation = ref('')

    const filteredResources = computed(() => {
      return resources.value.filter(resource => {
        const matchesSearch = !searchTerm.value || 
          resource.name.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
          resource.code.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
          resource.description.toLowerCase().includes(searchTerm.value.toLowerCase())
        
        const matchesCategory = !selectedCategory.value || resource.category === selectedCategory.value
        const matchesStatus = !selectedStatus.value || resource.status === selectedStatus.value
        const matchesLocation = !selectedLocation.value || resource.location_id === selectedLocation.value
        
        return matchesSearch && matchesCategory && matchesStatus && matchesLocation
      })
    })

    const getCategoryClass = (category) => {
      const classes = {
        equipment: 'bg-blue-100 text-blue-800',
        software: 'bg-purple-100 text-purple-800',
        furniture: 'bg-green-100 text-green-800',
        vehicle: 'bg-red-100 text-red-800',
        material: 'bg-yellow-100 text-yellow-800',
        tool: 'bg-indigo-100 text-indigo-800'
      }
      return classes[category] || 'bg-gray-100 text-gray-800'
    }

    const getCategoryLabel = (category) => {
      const labels = {
        equipment: 'Equipamento',
        software: 'Software',
        furniture: 'Mobiliário',
        vehicle: 'Veículo',
        material: 'Material',
        tool: 'Ferramenta'
      }
      return labels[category] || category
    }

    const getStatusClass = (status) => {
      const classes = {
        available: 'bg-green-100 text-green-800',
        in_use: 'bg-blue-100 text-blue-800',
        maintenance: 'bg-yellow-100 text-yellow-800',
        retired: 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const getStatusLabel = (status) => {
      const labels = {
        available: 'Disponível',
        in_use: 'Em Uso',
        maintenance: 'Manutenção',
        retired: 'Aposentado'
      }
      return labels[status] || status
    }

    const getResourceIcon = (category) => {
      const icons = {
        equipment: 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        software: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
        furniture: 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2z',
        vehicle: 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z',
        material: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        tool: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'
      }
      return icons[category] || 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'
    }

    const getInitials = (name) => {
      if (!name) return '?'
      return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
    }

    const formatCurrency = (value) => {
      return new Intl.NumberFormat('pt-AO', {
        style: 'currency',
        currency: 'AOA'
      }).format(value)
    }

    const getResourcesByStatus = (status) => {
      return resources.value.filter(resource => resource.status === status)
    }

    const getTotalValue = () => {
      return resources.value.reduce((total, resource) => total + resource.value, 0)
    }

    const viewResource = (resource) => {
      console.log('Visualizar recurso:', resource)
    }

    const editResource = (resource) => {
      console.log('Editar recurso:', resource)
    }

    const assignResource = (resource) => {
      console.log('Atribuir recurso:', resource)
    }

    const deleteResource = (resource) => {
      if (confirm(`Tem certeza que deseja excluir o recurso "${resource.name}"?`)) {
        console.log('Excluir recurso:', resource)
      }
    }

    // Mock data for demonstration
    onMounted(() => {
      locations.value = [
        { id: 1, name: 'Sede Principal - Luanda' },
        { id: 2, name: 'Filial Benguela' },
        { id: 3, name: 'Escritório Huambo' },
        { id: 4, name: 'Depósito Central' },
        { id: 5, name: 'Sala de TI' }
      ]

      resources.value = [
        {
          id: 1,
          name: 'Laptop Dell Latitude 5520',
          code: 'EQ-001',
          description: 'Laptop para desenvolvimento',
          category: 'equipment',
          status: 'in_use',
          location_id: 1,
          location: { name: 'Sede Principal - Luanda' },
          assigned_to: { name: 'João Silva', department: 'TI' },
          value: 850000,
          created_at: '2024-01-15T10:00:00Z'
        },
        {
          id: 2,
          name: 'Microsoft Office 365',
          code: 'SW-001',
          description: 'Licença anual Office 365',
          category: 'software',
          status: 'available',
          location_id: 5,
          location: { name: 'Sala de TI' },
          assigned_to: null,
          value: 120000,
          created_at: '2024-01-10T14:30:00Z'
        },
        {
          id: 3,
          name: 'Mesa de Escritório',
          code: 'MOB-001',
          description: 'Mesa executiva em madeira',
          category: 'furniture',
          status: 'available',
          location_id: 4,
          location: { name: 'Depósito Central' },
          assigned_to: null,
          value: 45000,
          created_at: '2024-01-20T09:15:00Z'
        },
        {
          id: 4,
          name: 'Toyota Hilux 2023',
          code: 'VEI-001',
          description: 'Veículo para transporte',
          category: 'vehicle',
          status: 'maintenance',
          location_id: 1,
          location: { name: 'Sede Principal - Luanda' },
          assigned_to: { name: 'Carlos Santos', department: 'Operações' },
          value: 12500000,
          created_at: '2024-01-05T16:45:00Z'
        },
        {
          id: 5,
          name: 'Papel A4 - Caixa',
          code: 'MAT-001',
          description: 'Caixa com 5000 folhas A4',
          category: 'material',
          status: 'available',
          location_id: 4,
          location: { name: 'Depósito Central' },
          assigned_to: null,
          value: 8500,
          created_at: '2024-01-12T11:20:00Z'
        },
        {
          id: 6,
          name: 'Furadeira Bosch',
          code: 'FER-001',
          description: 'Furadeira elétrica profissional',
          category: 'tool',
          status: 'in_use',
          location_id: 2,
          location: { name: 'Filial Benguela' },
          assigned_to: { name: 'Ana Costa', department: 'Manutenção' },
          value: 25000,
          created_at: '2023-12-20T13:10:00Z'
        },
        {
          id: 7,
          name: 'Servidor HP ProLiant',
          code: 'EQ-002',
          description: 'Servidor para aplicações',
          category: 'equipment',
          status: 'retired',
          location_id: 5,
          location: { name: 'Sala de TI' },
          assigned_to: null,
          value: 2500000,
          created_at: '2023-11-15T08:30:00Z'
        }
      ]
    })

    return {
      resources,
      locations,
      showCreateModal,
      searchTerm,
      selectedCategory,
      selectedStatus,
      selectedLocation,
      filteredResources,
      getCategoryClass,
      getCategoryLabel,
      getStatusClass,
      getStatusLabel,
      getResourceIcon,
      getInitials,
      formatCurrency,
      getResourcesByStatus,
      getTotalValue,
      viewResource,
      editResource,
      assignResource,
      deleteResource
    }
  }
}
</script>