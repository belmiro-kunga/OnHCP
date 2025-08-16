<template>
  <div class="card">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Gerir Utilizadores</h3>
      <button @click="showAddUserModal = true" class="btn-primary">
        + Novo Utilizador
      </button>
    </div>
    
    <!-- Search and Filters -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <input 
          v-model="searchTerm" 
          type="text" 
          placeholder="Procurar utilizadores..." 
          class="form-input"
        />
      </div>
      <div>
        <select v-model="statusFilter" class="form-input">
          <option value="">Todos os Estados</option>
          <option value="Ativo">Ativo</option>
          <option value="Pendente">Pendente</option>
          <option value="Inativo">Inativo</option>
        </select>
      </div>
      <div>
        <select v-model="progressFilter" class="form-input">
          <option value="">Todos os Progressos</option>
          <option value="completed">Completo (100%)</option>
          <option value="inProgress">Em Progresso (1-99%)</option>
          <option value="notStarted">Não Iniciado (0%)</option>
        </select>
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progresso</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Registro</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in filteredUsers" :key="user.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.role }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="[
                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                user.status === 'Ativo' ? 'bg-green-100 text-green-800' :
                user.status === 'Pendente' ? 'bg-yellow-100 text-yellow-800' :
                'bg-red-100 text-red-800'
              ]">
                {{ user.status }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              <div class="flex items-center">
                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                  <div 
                    class="h-2 rounded-full"
                    :class="user.progress === 100 ? 'bg-green-600' : user.progress > 50 ? 'bg-blue-600' : 'bg-yellow-600'"
                    :style="{ width: user.progress + '%' }"
                  ></div>
                </div>
                <span class="text-xs">{{ user.progress }}%</span>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.registrationDate }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <button @click="editUser(user)" class="text-primary-600 hover:text-primary-900 mr-3">Editar</button>
              <button @click="viewUser(user)" class="text-blue-600 hover:text-blue-900 mr-3">Ver</button>
              <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900">Excluir</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex items-center justify-between">
      <div class="text-sm text-gray-700">
        A mostrar {{ (currentPage - 1) * itemsPerPage + 1 }} a {{ Math.min(currentPage * itemsPerPage, filteredUsers.length) }} de {{ filteredUsers.length }} utilizadores
      </div>
      <div class="flex space-x-2">
        <button 
          @click="currentPage--" 
          :disabled="currentPage === 1"
          class="px-3 py-1 border rounded-md text-sm"
          :class="currentPage === 1 ? 'bg-gray-100 text-gray-400' : 'bg-white text-gray-700 hover:bg-gray-50'"
        >
          Anterior
        </button>
        <button 
          @click="currentPage++" 
          :disabled="currentPage * itemsPerPage >= filteredUsers.length"
          class="px-3 py-1 border rounded-md text-sm"
          :class="currentPage * itemsPerPage >= filteredUsers.length ? 'bg-gray-100 text-gray-400' : 'bg-white text-gray-700 hover:bg-gray-50'"
        >
          Próximo
        </button>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Novo Utilizador</h3>
          <form @submit.prevent="addUser">
            <div class="mb-4">
              <label class="form-label">Nome Completo</label>
              <input v-model="newUser.name" type="text" class="form-input" required />
            </div>
            <div class="mb-4">
              <label class="form-label">Email</label>
              <input v-model="newUser.email" type="email" class="form-input" required />
            </div>
            <div class="mb-4">
              <label class="form-label">Cargo</label>
              <select v-model="newUser.role" class="form-input" required>
                <option value="">Selecione um cargo</option>
                <option value="Médico">Médico</option>
                <option value="Enfermeiro">Enfermeiro</option>
                <option value="Técnico">Técnico</option>
                <option value="Administrativo">Administrativo</option>
              </select>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showAddUserModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                Cancelar
              </button>
              <button type="submit" class="btn-primary">
                Adicionar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminUsers',
  data() {
    return {
      searchTerm: '',
      statusFilter: '',
      progressFilter: '',
      currentPage: 1,
      itemsPerPage: 10,
      showAddUserModal: false,
      newUser: {
        name: '',
        email: '',
        role: ''
      },
      users: [
        { id: 1, name: 'Dr. João Silva', email: 'joao@hospital.com', role: 'Médico', status: 'Ativo', progress: 100, registrationDate: '15/01/2024' },
        { id: 2, name: 'Dra. Maria Santos', email: 'maria@hospital.com', role: 'Médico', status: 'Ativo', progress: 85, registrationDate: '12/01/2024' },
        { id: 3, name: 'Dr. Pedro Costa', email: 'pedro@hospital.com', role: 'Médico', status: 'Pendente', progress: 45, registrationDate: '10/01/2024' },
        { id: 4, name: 'Dra. Ana Lima', email: 'ana@hospital.com', role: 'Enfermeiro', status: 'Inativo', progress: 20, registrationDate: '08/01/2024' },
        { id: 5, name: 'Carlos Oliveira', email: 'carlos@hospital.com', role: 'Técnico', status: 'Ativo', progress: 90, registrationDate: '05/01/2024' },
        { id: 6, name: 'Fernanda Silva', email: 'fernanda@hospital.com', role: 'Administrativo', status: 'Pendente', progress: 60, registrationDate: '03/01/2024' }
      ]
    }
  },
  computed: {
    filteredUsers() {
      let filtered = this.users
      
      if (this.searchTerm) {
        filtered = filtered.filter(user => 
          user.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
          user.email.toLowerCase().includes(this.searchTerm.toLowerCase())
        )
      }
      
      if (this.statusFilter) {
        filtered = filtered.filter(user => user.status === this.statusFilter)
      }
      
      if (this.progressFilter) {
        filtered = filtered.filter(user => {
          if (this.progressFilter === 'completed') return user.progress === 100
          if (this.progressFilter === 'inProgress') return user.progress > 0 && user.progress < 100
          if (this.progressFilter === 'notStarted') return user.progress === 0
          return true
        })
      }
      
      return filtered
    }
  },
  methods: {
    addUser() {
      const newId = Math.max(...this.users.map(u => u.id)) + 1
      this.users.push({
        id: newId,
        ...this.newUser,
        status: 'Pendente',
        progress: 0,
        registrationDate: new Date().toLocaleDateString('pt-PT')
      })
      this.newUser = { name: '', email: '', role: '' }
      this.showAddUserModal = false
    },
    editUser(user) {
      // Implementar edição de utilizador
      console.log('Editar utilizador:', user)
    },
    viewUser(user) {
      // Implementar visualização de utilizador
      console.log('Ver utilizador:', user)
    },
    deleteUser(user) {
      if (confirm(`Tem a certeza que deseja eliminar ${user.name}?`)) {
        this.users = this.users.filter(u => u.id !== user.id)
      }
    }
  }
}
</script>