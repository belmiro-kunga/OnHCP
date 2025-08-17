<template>
  <div class="space-y-6 overflow-x-hidden">
    <!-- Header -->
    <div class="card">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Gestão de Departamentos</h3>
        <div class="flex flex-wrap gap-2">
          <button @click="loadDepartments" class="btn-secondary">
            🔄 Atualizar
          </button>
          <button v-if="can('departments.manage')" @click="showAddDepartmentModal = true" class="btn-primary">
            + Novo Departamento
          </button>
        </div>
      </div>

      <!-- Search and Filters -->
      <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <input 
            v-model="searchTerm" 
            type="text" 
            placeholder="Procurar departamentos..." 
            class="input"
          />
        </div>
        <div>
          <select v-model="statusFilter" class="input">
            <option value="">Todos os Status</option>
            <option value="true">Ativos</option>
            <option value="false">Inativos</option>
          </select>
        </div>
        <div>
          <select v-model="perPage" class="input">
            <option value="10">10 por página</option>
            <option value="25">25 por página</option>
            <option value="50">50 por página</option>
          </select>
        </div>
      </div>

      <!-- Departments Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Nome
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Código
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Gerente
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Utilizadores
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Ações
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="loading" class="text-center">
              <td colspan="6" class="px-6 py-4 text-gray-500">
                Carregando...
              </td>
            </tr>
            <tr v-else-if="departments.length === 0" class="text-center">
              <td colspan="6" class="px-6 py-4 text-gray-500">
                Nenhum departamento encontrado
              </td>
            </tr>
            <tr v-else v-for="department in departments" :key="department.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ department.name }}</div>
                <div class="text-sm text-gray-500">{{ department.description || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ department.code }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ department.manager ? department.manager.name : 'Sem gerente' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                  {{ department.users_count || 0 }} usuários
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  department.is_active 
                    ? 'bg-green-100 text-green-800' 
                    : 'bg-red-100 text-red-800'
                ]">
                  {{ department.is_active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button v-if="can('departments.view')" @click="editDepartment(department)" class="text-blue-600 hover:text-blue-900">
                    ✏️ Editar
                  </button>
                  <button v-if="can('departments.manage')" @click="toggleDepartmentStatus(department)" :class="[
                    department.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'
                  ]">
                    {{ department.is_active ? '🚫 Desativar' : '✅ Ativar' }}
                  </button>
                  <button v-if="can('departments.manage')" @click="deleteDepartment(department)" class="text-red-600 hover:text-red-900">
                    🗑️ Excluir
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.last_page > 1" class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Mostrando {{ pagination.from }} a {{ pagination.to }} de {{ pagination.total }} resultados
        </div>
        <div class="flex space-x-2">
          <button 
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Anterior
          </button>
          <button 
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="btn-secondary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Próximo
          </button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Department Modal -->
    <div v-if="showAddDepartmentModal || showEditDepartmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            {{ showEditDepartmentModal ? 'Editar Departamento' : 'Adicionar Novo Departamento' }}
          </h3>
          
          <form @submit.prevent="saveDepartment">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                <input 
                  v-model="departmentForm.name" 
                  type="text" 
                  required 
                  class="input"
                  placeholder="Nome do departamento"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                <input 
                  v-model="departmentForm.code" 
                  type="text" 
                  required 
                  class="input"
                  placeholder="Código do departamento"
                />
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                <textarea 
                  v-model="departmentForm.description" 
                  class="input"
                  rows="3"
                  placeholder="Descrição do departamento"
                ></textarea>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Gerente</label>
                <select v-model="departmentForm.manager_id" class="input">
                  <option value="">Selecionar gerente</option>
                  <option v-for="manager in potentialManagers" :key="manager.id" :value="manager.id">
                    {{ manager.name }} ({{ manager.email }})
                  </option>
                </select>
              </div>
              
              <div class="flex items-center">
                <input 
                  v-model="departmentForm.is_active" 
                  type="checkbox" 
                  id="department-active"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <label for="department-active" class="ml-2 block text-sm text-gray-900">
                  Departamento ativo
                </label>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="closeModal" class="btn-secondary">
                Cancelar
              </button>
              <button type="submit" :disabled="loading" class="btn-primary">
                {{ loading ? 'Salvando...' : 'Salvar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success/Error Messages -->
    <div v-if="message" :class="[
      'fixed top-4 right-4 p-4 rounded-md shadow-lg z-50',
      messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
    ]">
      {{ message }}
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed, watch } from 'vue'
import { useDepartments } from '../../composables/useDepartments'
import { usePermissions } from '../../composables/usePermissions'

export default {
  name: 'AdminDepartments',
  setup() {
    const { can } = usePermissions()
    const {
      loading,
      error,
      listDepartments,
      createDepartment,
      updateDepartment,
      deleteDepartment: deleteDepartmentApi,
      toggleDepartmentStatus: toggleDepartmentStatusApi,
      getPotentialManagers
    } = useDepartments()

    // State
    const departments = ref([])
    const pagination = ref(null)
    const searchTerm = ref('')
    const statusFilter = ref('')
    const perPage = ref(25)
    const currentPage = ref(1)
    const potentialManagers = ref([])
    
    // Modals
    const showAddDepartmentModal = ref(false)
    const showEditDepartmentModal = ref(false)
    const editingDepartment = ref(null)
    
    // Form
    const departmentForm = ref({
      name: '',
      code: '',
      description: '',
      manager_id: '',
      is_active: true
    })
    
    // Messages
    const message = ref('')
    const messageType = ref('success')

    // Methods
    const loadDepartments = async () => {
      try {
        const params = {
          page: currentPage.value,
          per_page: perPage.value
        }
        
        if (searchTerm.value) params.search = searchTerm.value
        if (statusFilter.value !== '') params.active = statusFilter.value
        
        const response = await listDepartments(params)
        departments.value = response.data
        pagination.value = {
          current_page: response.current_page,
          last_page: response.last_page,
          from: response.from,
          to: response.to,
          total: response.total
        }
      } catch (err) {
        showMessage('Erro ao carregar departamentos', 'error')
      }
    }

    const loadPotentialManagers = async () => {
      try {
        const response = await getPotentialManagers()
        potentialManagers.value = response
      } catch (err) {
        console.error('Erro ao carregar gerentes potenciais:', err)
      }
    }

    const saveDepartment = async () => {
      try {
        const formData = { ...departmentForm.value }
        if (!formData.manager_id) {
          delete formData.manager_id
        }
        
        if (showEditDepartmentModal.value) {
          await updateDepartment(editingDepartment.value.id, formData)
          showMessage('Departamento atualizado com sucesso!')
        } else {
          await createDepartment(formData)
          showMessage('Departamento criado com sucesso!')
        }
        
        closeModal()
        loadDepartments()
      } catch (err) {
        showMessage(error.value || 'Erro ao salvar departamento', 'error')
      }
    }

    const editDepartment = async (department) => {
      editingDepartment.value = department
      departmentForm.value = {
        name: department.name,
        code: department.code,
        description: department.description || '',
        manager_id: department.manager_id || '',
        is_active: department.is_active
      }
      await loadPotentialManagers()
      showEditDepartmentModal.value = true
    }

    const deleteDepartment = async (department) => {
      if (!confirm(`Tem certeza que deseja excluir o departamento "${department.name}"?`)) return
      
      try {
        await deleteDepartmentApi(department.id)
        showMessage('Departamento excluído com sucesso!')
        loadDepartments()
      } catch (err) {
        showMessage(error.value || 'Erro ao excluir departamento', 'error')
      }
    }

    const toggleDepartmentStatus = async (department) => {
      try {
        await toggleDepartmentStatusApi(department.id)
        showMessage(`Departamento ${department.is_active ? 'desativado' : 'ativado'} com sucesso!`)
        loadDepartments()
      } catch (err) {
        showMessage(error.value || 'Erro ao alterar status do departamento', 'error')
      }
    }

    const changePage = (page) => {
      currentPage.value = page
      loadDepartments()
    }

    const closeModal = () => {
      showAddDepartmentModal.value = false
      showEditDepartmentModal.value = false
      editingDepartment.value = null
      departmentForm.value = {
        name: '',
        code: '',
        description: '',
        manager_id: '',
        is_active: true
      }
    }

    const showMessage = (msg, type = 'success') => {
      message.value = msg
      messageType.value = type
      setTimeout(() => {
        message.value = ''
      }, 5000)
    }

    // Watchers
    watch([searchTerm, statusFilter, perPage], () => {
      currentPage.value = 1
      loadDepartments()
    }, { debounce: 300 })

    watch(showAddDepartmentModal, (newVal) => {
      if (newVal) {
        loadPotentialManagers()
      }
    })

    // Lifecycle
    onMounted(() => {
      loadDepartments()
    })

    return {
      // Permissions
      can,
      
      // State
      departments,
      pagination,
      searchTerm,
      statusFilter,
      perPage,
      loading,
      potentialManagers,
      
      // Modals
      showAddDepartmentModal,
      showEditDepartmentModal,
      departmentForm,
      
      // Messages
      message,
      messageType,
      
      // Methods
      loadDepartments,
      saveDepartment,
      editDepartment,
      deleteDepartment,
      toggleDepartmentStatus,
      changePage,
      closeModal
    }
  }
}
</script>

<style scoped>
.card {
  @apply bg-white shadow rounded-lg p-6;
}

.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors;
}

.btn-secondary {
  @apply bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition-colors;
}

.input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
}
</style>