<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Hierarquia Organizacional</h1>
        <p class="text-gray-600 mt-1">Visualize e gerencie a estrutura hierárquica da organização</p>
      </div>
      <div class="flex gap-2">
        <button
          @click="toggleView"
          class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
          </svg>
          {{ viewMode === 'tree' ? 'Vista Lista' : 'Vista Árvore' }}
        </button>
        <button
          @click="showAddModal = true"
          class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
          </svg>
          Adicionar Posição
        </button>
      </div>
    </div>

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Nome ou cargo..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
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
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nível</label>
          <select
            v-model="selectedLevel"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os níveis</option>
            <option value="1">Nível 1 - Diretoria</option>
            <option value="2">Nível 2 - Gerência</option>
            <option value="3">Nível 3 - Coordenação</option>
            <option value="4">Nível 4 - Supervisão</option>
            <option value="5">Nível 5 - Operacional</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Tree View -->
    <div v-if="viewMode === 'tree'" class="bg-white rounded-lg shadow-sm border p-6">
      <div class="hierarchy-tree">
        <div v-for="node in hierarchyTree" :key="node.id" class="tree-node">
          <HierarchyNode :node="node" @edit="editPosition" @delete="deletePosition" />
        </div>
      </div>
    </div>

    <!-- List View -->
    <div v-else class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <table class="w-full">
        <thead class="bg-gray-50 border-b">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posição</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Funcionário</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Superior</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="position in filteredPositions" :key="position.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="text-sm font-medium text-gray-900">{{ position.title }}</div>
              <div class="text-sm text-gray-500">{{ position.description }}</div>
            </td>
            <td class="px-6 py-4">
              <div v-if="position.employee" class="flex items-center">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                  <span class="text-sm font-medium text-blue-600">{{ getInitials(position.employee.name) }}</span>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ position.employee.name }}</div>
                  <div class="text-sm text-gray-500">{{ position.employee.email }}</div>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500 italic">Posição vaga</div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">{{ position.department?.name || 'N/A' }}</td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                Nível {{ position.level }}
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-900">{{ position.manager?.name || 'N/A' }}</td>
            <td class="px-6 py-4 text-sm font-medium">
              <div class="flex gap-2">
                <button
                  @click="editPosition(position)"
                  class="text-blue-600 hover:text-blue-900"
                >
                  Editar
                </button>
                <button
                  @click="deletePosition(position)"
                  class="text-red-600 hover:text-red-900"
                >
                  Excluir
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="positions.length === 0" class="text-center py-12">
      <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma posição encontrada</h3>
      <p class="text-gray-600 mb-4">Comece definindo a estrutura hierárquica da organização</p>
      <button
        @click="showAddModal = true"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
      >
        Adicionar Primeira Posição
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'

// Componente para nó da árvore hierárquica
const HierarchyNode = {
  name: 'HierarchyNode',
  props: ['node'],
  emits: ['edit', 'delete'],
  template: `
    <div class="hierarchy-node">
      <div class="node-content bg-white border rounded-lg p-4 mb-4 shadow-sm">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
              <span class="text-sm font-medium text-blue-600">{{ getInitials(node.employee?.name || node.title) }}</span>
            </div>
            <div>
              <h3 class="text-sm font-medium text-gray-900">{{ node.title }}</h3>
              <p class="text-sm text-gray-500">{{ node.employee?.name || 'Posição vaga' }}</p>
              <p class="text-xs text-gray-400">{{ node.department?.name }}</p>
            </div>
          </div>
          <div class="flex gap-2">
            <button @click="$emit('edit', node)" class="text-blue-600 hover:text-blue-900 text-sm">Editar</button>
            <button @click="$emit('delete', node)" class="text-red-600 hover:text-red-900 text-sm">Excluir</button>
          </div>
        </div>
      </div>
      <div v-if="node.children && node.children.length > 0" class="ml-8">
        <HierarchyNode 
          v-for="child in node.children" 
          :key="child.id" 
          :node="child" 
          @edit="$emit('edit', $event)"
          @delete="$emit('delete', $event)"
        />
      </div>
    </div>
  `,
  methods: {
    getInitials(name) {
      if (!name) return '?'
      return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
    }
  }
}

export default {
  name: 'AdminHierarquia',
  components: {
    HierarchyNode
  },
  setup() {
    const positions = ref([])
    const departments = ref([])
    const viewMode = ref('tree')
    const showAddModal = ref(false)
    const searchTerm = ref('')
    const selectedDepartment = ref('')
    const selectedLevel = ref('')

    const filteredPositions = computed(() => {
      return positions.value.filter(position => {
        const matchesSearch = !searchTerm.value || 
          position.title.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
          position.employee?.name.toLowerCase().includes(searchTerm.value.toLowerCase())
        
        const matchesDepartment = !selectedDepartment.value || position.department_id === selectedDepartment.value
        const matchesLevel = !selectedLevel.value || position.level === parseInt(selectedLevel.value)
        
        return matchesSearch && matchesDepartment && matchesLevel
      })
    })

    const hierarchyTree = computed(() => {
      const buildTree = (parentId = null) => {
        return filteredPositions.value
          .filter(pos => pos.parent_id === parentId)
          .map(pos => ({
            ...pos,
            children: buildTree(pos.id)
          }))
      }
      return buildTree()
    })

    const toggleView = () => {
      viewMode.value = viewMode.value === 'tree' ? 'list' : 'tree'
    }

    const getInitials = (name) => {
      if (!name) return '?'
      return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
    }

    const editPosition = (position) => {
      console.log('Editar posição:', position)
    }

    const deletePosition = (position) => {
      if (confirm(`Tem certeza que deseja excluir a posição "${position.title}"?`)) {
        console.log('Excluir posição:', position)
      }
    }

    // Mock data for demonstration
    onMounted(() => {
      departments.value = [
        { id: 1, name: 'Diretoria Executiva' },
        { id: 2, name: 'Recursos Humanos' },
        { id: 3, name: 'Tecnologia da Informação' },
        { id: 4, name: 'Financeiro' },
        { id: 5, name: 'Operações' }
      ]

      positions.value = [
        {
          id: 1,
          title: 'Diretor Executivo',
          description: 'Responsável pela gestão geral da empresa',
          level: 1,
          department_id: 1,
          department: { name: 'Diretoria Executiva' },
          parent_id: null,
          employee: { name: 'João Silva', email: 'joao@empresa.com' },
          manager: null
        },
        {
          id: 2,
          title: 'Gerente de RH',
          description: 'Gestão de recursos humanos',
          level: 2,
          department_id: 2,
          department: { name: 'Recursos Humanos' },
          parent_id: 1,
          employee: { name: 'Maria Santos', email: 'maria@empresa.com' },
          manager: { name: 'João Silva' }
        },
        {
          id: 3,
          title: 'Gerente de TI',
          description: 'Gestão de tecnologia',
          level: 2,
          department_id: 3,
          department: { name: 'Tecnologia da Informação' },
          parent_id: 1,
          employee: { name: 'Pedro Costa', email: 'pedro@empresa.com' },
          manager: { name: 'João Silva' }
        },
        {
          id: 4,
          title: 'Coordenador de Desenvolvimento',
          description: 'Coordenação da equipe de desenvolvimento',
          level: 3,
          department_id: 3,
          department: { name: 'Tecnologia da Informação' },
          parent_id: 3,
          employee: null,
          manager: { name: 'Pedro Costa' }
        }
      ]
    })

    return {
      positions,
      departments,
      viewMode,
      showAddModal,
      searchTerm,
      selectedDepartment,
      selectedLevel,
      filteredPositions,
      hierarchyTree,
      toggleView,
      getInitials,
      editPosition,
      deletePosition
    }
  }
}
</script>

<style scoped>
.hierarchy-tree {
  font-family: 'Inter', sans-serif;
}

.hierarchy-node {
  position: relative;
}

.hierarchy-node:not(:last-child)::before {
  content: '';
  position: absolute;
  left: 20px;
  top: 60px;
  bottom: -20px;
  width: 2px;
  background-color: #e5e7eb;
}

.hierarchy-node::after {
  content: '';
  position: absolute;
  left: 20px;
  top: 60px;
  width: 20px;
  height: 2px;
  background-color: #e5e7eb;
}
</style>