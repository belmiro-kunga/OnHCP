<template>
  <div class="space-y-6 overflow-x-hidden">
    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200">
      <nav class="-mb-px flex gap-4 overflow-x-auto whitespace-nowrap">
        <button
          @click="activeTab = 'users'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'users'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Gestão de Utilizadores
        </button>
        <button
          @click="activeTab = 'authentication'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'authentication'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Autenticação e Acesso
        </button>
        <button
          @click="activeTab = 'security'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'security'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Recuperação de Senhas
        </button>
        <button
          @click="activeTab = 'enterprise'; loadEnterprise()"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'enterprise'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Integração com a Empresa
        </button>
        <button
          v-if="can('roles.view')"
          @click="activeTab = 'roles'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'roles'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Cargos
        </button>
        <button
          v-if="can('departments.view')"
          @click="activeTab = 'departments'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'departments'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Departamentos
        </button>
        <button v-if="can('users.manage')"
          @click="showPermissionsModal = true"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Permissões
        </button>
        
        <button v-if="can('users.manage')"
          @click="activeTab = 'reports'"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === 'reports'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          Relatórios
        </button>
      </nav>
    </div>

    <!-- Edit User Modal -->
    <div v-if="showEditUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-600 bg-opacity-50" @click.self="showEditUserModal = false">
      <div class="mx-4 sm:mx-6 w-full max-w-5xl border shadow-lg rounded-lg bg-white p-4 sm:p-5">
        <div>
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-base sm:text-lg font-medium text-gray-900">Editar Utilizador</h3>
            <button type="button" @click="showEditUserModal = false" class="text-gray-400 hover:text-gray-600" aria-label="Fechar">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <form @submit.prevent="saveEditedUser">
            <!-- Informações Básicas -->
            <div class="mb-4">
              <h4 class="font-medium text-gray-900 border-b pb-1 mb-3">Informações Básicas</h4>
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-7">
                  <label class="form-label">Nome Completo <span class="text-red-500">*</span></label>
                  <input v-model="editedUser.name" type="text" class="form-input" required placeholder="Digite o nome completo" />
                </div>
                <div class="md:col-span-5">
                  <label class="form-label">Email <span class="text-red-500">*</span></label>
                  <input v-model="editedUser.email" type="email" class="form-input" required placeholder="exemplo@hospital.com" />
                </div>
              </div>
            </div>

            <!-- Dados Profissionais -->
            <div class="mb-4">
              <h4 class="font-medium text-gray-900 border-b pb-1 mb-3">Dados Profissionais</h4>
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                  <label class="form-label">Cargo <span class="text-red-500">*</span></label>
                  <select v-model="editedUser.role_id" class="form-input" required>
                    <option value="">Selecione um cargo</option>
                    <option 
                      v-for="role in availableRoles" 
                      :key="role.value" 
                      :value="role.value"
                    >
                      {{ role.label }}
                    </option>
                  </select>
                </div>
                <div class="md:col-span-4">
                  <label class="form-label">Departamento <span class="text-red-500">*</span></label>
                  <select v-model="editedUser.department_id" class="form-input" required>
                    <option value="">Selecione um departamento</option>
                    <option 
                      v-for="department in availableDepartments" 
                      :key="department.value" 
                      :value="department.value"
                    >
                      {{ department.label }}
                    </option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="form-label">Data de Início</label>
                  <input v-model="editedUser.startDate" type="date" class="form-input" />
                </div>
                <div class="md:col-span-2">
                  <label class="form-label">Estado</label>
                  <select v-model="editedUser.status" class="form-input" required>
                    <option value="Ativo">Ativo</option>
                    <option value="Pendente">Pendente</option>
                    <option value="Inativo">Inativo</option>
                    <option value="Bloqueado">Bloqueado</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Dados Pessoais -->
            <div class="mb-4">
              <h4 class="font-medium text-gray-900 border-b pb-1 mb-3">Dados Pessoais</h4>
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                  <label class="form-label">Telefone</label>
                  <input v-model="editedUser.phone" type="tel" class="form-input" placeholder="+351 912 345 678" />
                </div>
              </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 mt-4">
              <button type="button" @click="showEditUserModal = false" class="px-3 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 w-full sm:w-auto">
                Cancelar
              </button>
              <button type="submit" class="btn-primary w-full sm:w-auto">
                Guardar Alterações
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Users Management Tab -->
    <div v-if="activeTab === 'users'" class="card">
      <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Gerir Utilizadores</h3>
        <div class="flex flex-wrap gap-2">
          <button v-if="can('users.view')" @click="exportUsers" class="btn-secondary">
            📤 Exportar
          </button>
          <button v-if="can('users.manage')" @click="showImportModal = true" class="btn-secondary">
            📥 Importar
          </button>
          <button v-if="can('users.manage')" @click="showBulkActions = true" class="btn-secondary">
            Ações em Massa
          </button>
          <button v-if="can('users.manage')" @click="showAddUserModal = true" class="btn-primary">
            + Novo Utilizador
          </button>
        </div>
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
      <div class="flex items-center justify-between md:justify-end gap-3">
        <label class="text-sm text-gray-600">Itens por página</label>
        <select v-model.number="itemsPerPage" class="form-input w-28">
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </div>
    </div>

    <!-- Users Loading/Error Banners -->
    <div v-if="usersError" class="mb-4 p-3 rounded bg-red-50 text-red-700">{{ usersError }}</div>
    <div v-if="usersLoading" class="mb-4 p-3 rounded bg-blue-50 text-blue-700">A carregar utilizadores...</div>

    <!-- Dashboard Tab -->
    <div v-if="activeTab === 'dashboard' && can('users.manage')">
      <AdminDashboard />
    </div>

    <!-- Reports Tab -->
    <div v-if="activeTab === 'reports' && can('users.manage')">
      <AdminReports />
    </div>

    <!-- Groups/Teams Tab -->
    <div v-if="activeTab === 'groups' && can('users.manage')">
      <AdminGroups />
    </div>

    <!-- Approvals Tab -->
    <div v-if="activeTab === 'approvals' && can('users.manage')">
      <AdminApprovals />
    </div>

    
    
    <!-- Mobile list (shown on small screens) -->
    <div class="md:hidden space-y-3">
      <div v-for="user in filteredUsers" :key="user.id" class="border rounded-lg p-4 bg-white shadow-sm">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
              <span class="text-sm font-medium text-gray-700">{{ user.name.charAt(0) }}</span>
            </div>
            <div class="ml-3">
              <div class="text-sm font-semibold text-gray-900">{{ user.name }}</div>
              <div class="text-xs text-gray-500 break-all">{{ user.email }}</div>
            </div>
          </div>
          <input type="checkbox" class="form-checkbox" v-model="user.selected" />
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
          <span :class="[
            'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
            user.status === 'Ativo' ? 'bg-green-100 text-green-800' :
            user.status === 'Pendente' ? 'bg-yellow-100 text-yellow-800' :
            'bg-red-100 text-red-800'
          ]">
            {{ user.status }}
          </span>
        </div>
        
        <div class="mt-3 text-xs text-gray-500">
          Último login: {{ user.lastLogin }} · {{ user.lastLoginDevice }}
        </div>
        <div class="mt-4 flex flex-wrap gap-3">
          <button 
            v-if="can('users.manage', { targetUser: { attributes: { department: user.department, branch: 'HQ' } } })"
            @click="editUser(user)" 
            class="btn-secondary text-xs">
            Editar
          </button>
          <button 
            v-if="can('users.view', { targetUser: { attributes: { department: user.department } } })"
            @click="viewUser(user)" 
            class="btn-secondary text-xs">
            Ver
          </button>
          <button 
            v-if="can('users.manage', { targetUser: { attributes: { department: user.department, branch: 'HQ' } } })"
            @click="toggleMFA(user)" 
            class="btn-secondary text-xs">
            {{ user.mfaEnabled ? 'Desativar MFA' : 'Ativar MFA' }}
          </button>
          <button 
            v-if="can('users.manage', { targetUser: { attributes: { department: user.department, branch: 'HQ' } } })"
            @click="resetPassword(user)" 
            class="btn-secondary text-xs">
            Reset Senha
          </button>
          <button 
            v-if="can('users.manage', { targetUser: { attributes: { department: user.department, branch: 'HQ' } } })"
            @click="deleteUser(user)" 
            class="btn-secondary text-xs text-red-700">
            Excluir
          </button>
        </div>
      </div>
    </div>

    <!-- Desktop table (hidden on small screens) -->
    <div class="overflow-x-auto hidden md:block">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              <input type="checkbox" class="form-checkbox" @change="selectAll" />
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Login</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="user in filteredUsers" :key="user.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <input type="checkbox" class="form-checkbox" v-model="user.selected" />
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-8 w-8">
                  <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                    <span class="text-xs font-medium text-gray-700">{{ user.name.charAt(0) }}</span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                  <div class="text-sm text-gray-500">{{ user.registrationDate }}</div>
                </div>
              </div>
            </td>
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
              <div>
                <div class="text-sm text-gray-900">{{ user.lastLogin }}</div>
                <div class="text-sm text-gray-500">{{ user.lastLoginDevice }}</div>
              </div>
            </td>
            
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
              <div class="flex space-x-2">
                <button 
                  v-if="can('users.manage', { targetUser: { attributes: { department: user.department, branch: 'HQ' } } })"
                  @click="editUser(user)" 
                  class="text-blue-600 hover:text-blue-900" 
                  title="Editar">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button 
                  v-if="can('users.view', { targetUser: { attributes: { department: user.department } } })"
                  @click="viewUser(user)" 
                  class="text-green-600 hover:text-green-900" 
                  title="Ver Detalhes">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                  </svg>
                </button>
                <button 
                  v-if="can('users.manage', { targetUser: { attributes: { department: user.department, branch: 'HQ' } } })"
                  @click="toggleMFA(user)" 
                  :class="user.mfaEnabled ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'" 
                  :title="user.mfaEnabled ? 'Desativar MFA' : 'Ativar MFA'">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                  </svg>
                </button>
                <button 
                  v-if="can('users.manage', { targetUser: { attributes: { department: user.department, branch: 'HQ' } } })"
                  @click="resetPassword(user)" 
                  class="text-yellow-600 hover:text-yellow-900" 
                  title="Reset Senha">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2V5a2 2 0 00-2-2m0 0H9a2 2 0 00-2 2v0"></path>
                  </svg>
                </button>
                <button 
                  v-if="can('users.manage', { targetUser: { attributes: { department: user.department, branch: 'HQ' } } })"
                  @click="deleteUser(user)" 
                  class="text-red-600 hover:text-red-900" 
                  title="Excluir">
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
    
    <!-- Pagination Controls -->
    <div class="mt-4 flex items-center justify-between">
      <div class="text-sm text-gray-600 space-x-3">
        <span>Página {{ currentPage }} de {{ totalPages }}</span>
        <span>•</span>
        <span>Total: {{ totalCount }}</span>
      </div>
      <div class="space-x-2">
        <button class="btn-secondary" @click="prevPage" :disabled="currentPage <= 1">Anterior</button>
        <button class="btn-secondary" @click="nextPage" :disabled="currentPage >= totalPages">Seguinte</button>
      </div>
    </div>
    </div>

    <!-- Authentication Tab -->
    <div v-if="activeTab === 'authentication'">
      <UserAuthentication />
    </div>

    <!-- Password Recovery Tab -->
    <div v-if="activeTab === 'security'" class="card">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Recuperação e Redefinição de Senhas</h3>
        <button @click="showPasswordRecoverySettings = true" class="btn-primary">
          Configurações
        </button>
      </div>

      <!-- Password Recovery Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Solicitações Hoje</h4>
              <p class="text-2xl font-bold text-blue-600">{{ recoveryStats.today }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-full">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2V5a2 2 0 00-2-2m0 0H9a2 2 0 00-2 2v0"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Esta Semana</h4>
              <p class="text-2xl font-bold text-green-600">{{ recoveryStats.week }}</p>
            </div>
            <div class="p-3 bg-green-100 rounded-full">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Pendentes</h4>
              <p class="text-2xl font-bold text-yellow-600">{{ recoveryStats.pending }}</p>
            </div>
            <div class="p-3 bg-yellow-100 rounded-full">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center justify-between">
            <div>
              <h4 class="text-sm font-medium text-gray-900">Taxa Sucesso</h4>
              <p class="text-2xl font-bold text-purple-600">{{ recoveryStats.successRate }}%</p>
            </div>
            <div class="p-3 bg-purple-100 rounded-full">
              <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Password Recovery Requests Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizador</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Solicitação</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expira em</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="request in passwordRecoveryRequests" :key="request.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-xs font-medium text-gray-700">{{ request.user.name.charAt(0) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ request.user.name }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ request.user.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getRecoveryMethodClass(request.method)">
                  {{ request.method }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getRecoveryStatusClass(request.status)">
                  {{ request.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ request.requestDate }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ request.expiresAt }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button v-if="request.status === 'Pendente'" @click="approveRecovery(request)" class="text-green-600 hover:text-green-900 mr-3">Aprovar</button>
                <button v-if="request.status === 'Pendente'" @click="rejectRecovery(request)" class="text-red-600 hover:text-red-900 mr-3">Rejeitar</button>
                <button @click="resendRecovery(request)" class="text-blue-600 hover:text-blue-900">Reenviar</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Enterprise Integration: IP Security -->
    <div v-if="activeTab === 'enterprise'" class="card">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Integração com a Empresa - IP Security</h3>
        <div class="space-x-2">
          <button @click="refreshSecurity" class="btn-secondary">Atualizar</button>
        </div>
      </div>

      <div v-if="error" class="mb-4 p-3 rounded bg-red-50 text-red-700">{{ error }}</div>
      <div v-if="loading" class="mb-4 p-3 rounded bg-blue-50 text-blue-700">A carregar...</div>

      <!-- Add Policy -->
      <div class="mb-6">
        <h4 class="font-medium text-gray-900 mb-3">Adicionar Política de IP</h4>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
          <div>
            <label class="form-label">Tipo</label>
            <select v-model="newPolicy.type" class="form-input">
              <option value="whitelist">Whitelist</option>
              <option value="blacklist">Blacklist</option>
            </select>
          </div>
          <div>
            <label class="form-label">IP/CIDR</label>
            <input v-model="newPolicy.ip_cidr" placeholder="Ex: 192.168.0.0/24" class="form-input" />
          </div>
          <div>
            <label class="form-label">Motivo (opcional)</label>
            <input v-model="newPolicy.reason" placeholder="Motivo" class="form-input" />
          </div>
          <div>
            <button @click="addPolicy" class="btn-primary w-full" :disabled="loading">Adicionar</button>
          </div>
        </div>
      </div>

      <!-- Policies List -->
      <div class="mb-8">
        <h4 class="font-medium text-gray-900 mb-3">Políticas de IP</h4>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP/CIDR</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="p in policies" :key="p.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.id }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.type }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ p.ip_cidr }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.reason || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.created_at || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button @click="removePolicy(p.id)" class="text-red-600 hover:text-red-900">Remover</button>
                </td>
              </tr>
              <tr v-if="!policies || policies.length === 0">
                <td colspan="6" class="px-6 py-4 text-sm text-gray-500">Sem políticas.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Locks List -->
      <div>
        <div class="flex items-center justify-between mb-3">
          <h4 class="font-medium text-gray-900">IPs Bloqueados</h4>
          <button @click="refreshSecurity" class="btn-secondary">Recarregar</button>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Falhas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bloqueado até</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="l in locks" :key="l.ip + (l.locked_until || '')">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ l.ip }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ l.fail_count ?? '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ l.locked_until || '-' }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button @click="unlock(l.ip)" class="text-blue-600 hover:text-blue-900">Desbloquear</button>
                </td>
              </tr>
              <tr v-if="!locks || locks.length === 0">
                <td colspan="4" class="px-6 py-4 text-sm text-gray-500">Sem IPs bloqueados.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Roles Management Section -->
    <div v-if="activeTab === 'roles'" class="card">
      <AdminRoles />
    </div>

    <!-- Departments Management Section -->
    <div v-if="activeTab === 'departments'" class="card">
      <AdminDepartments />
    </div>

    <!-- Bulk Actions Modal -->
    <div v-if="showBulkActions" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showBulkActions = false">
      <div class="relative top-20 mx-auto p-5 border w-full max-w-md sm:max-w-lg shadow-lg rounded-md bg-white mx-4">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Ações em Massa</h3>
          <div class="space-y-4">
            <button @click="bulkAction('activate')" class="w-full btn-primary text-left">
              Ativar Utilizadores Selecionados
            </button>
            <button @click="bulkAction('deactivate')" class="w-full btn-secondary text-left">
              Desativar Utilizadores Selecionados
            </button>
            <button @click="bulkAction('resetPassword')" class="w-full btn-secondary text-left">
              Forçar Reset de Senha
            </button>
            <button @click="bulkAction('enableMFA')" class="w-full btn-secondary text-left">
              Ativar MFA
            </button>
            <button @click="bulkAction('export')" class="w-full btn-secondary text-left">
              Exportar Dados
            </button>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button @click="showBulkActions = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
              Fechar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Password Recovery Settings Modal -->
    <div v-if="showPasswordRecoverySettings" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showPasswordRecoverySettings = false">
      <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white mx-4">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações de Recuperação de Senha</h3>
          
          <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <h4 class="font-medium text-gray-900">Configurações Gerais</h4>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <label class="text-sm text-gray-700">Tempo de expiração (horas)</label>
                    <input v-model="recoverySettings.expirationHours" type="number" class="w-20 form-input text-sm" />
                  </div>
                  <div class="flex items-center justify-between">
                    <label class="text-sm text-gray-700">Máx. tentativas por dia</label>
                    <input v-model="recoverySettings.maxAttemptsPerDay" type="number" class="w-20 form-input text-sm" />
                  </div>
                  <div class="flex items-center justify-between">
                    <label class="text-sm text-gray-700">Aprovação manual</label>
                    <input v-model="recoverySettings.requireManualApproval" type="checkbox" class="form-checkbox" />
                  </div>
                </div>
              </div>

              <div class="space-y-4">
                <h4 class="font-medium text-gray-900">Métodos Permitidos</h4>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <label class="text-sm text-gray-700">Email</label>
                    <input v-model="recoverySettings.allowEmail" type="checkbox" class="form-checkbox" />
                  </div>
                  <div class="flex items-center justify-between">
                    <label class="text-sm text-gray-700">SMS</label>
                    <input v-model="recoverySettings.allowSMS" type="checkbox" class="form-checkbox" />
                  </div>
                  <div class="flex items-center justify-between">
                    <label class="text-sm text-gray-700">Perguntas de Segurança</label>
                    <input v-model="recoverySettings.allowSecurityQuestions" type="checkbox" class="form-checkbox" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button @click="showPasswordRecoverySettings = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
              Cancelar
            </button>
            <button @click="saveRecoverySettings" class="btn-primary">
              Guardar Configurações
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add User Modal -->
    <div v-if="showAddUserModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-600 bg-opacity-50" @click.self="showAddUserModal = false">
      <div class="mx-4 sm:mx-6 w-full max-w-5xl border shadow-lg rounded-lg bg-white p-4 sm:p-5">
        <div>
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-base sm:text-lg font-medium text-gray-900">Adicionar Novo Utilizador</h3>
            <button type="button" @click="showAddUserModal = false" class="text-gray-400 hover:text-gray-600" aria-label="Fechar">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <form @submit.prevent="addUser">
            <!-- Avatar Upload -->
            <div class="mb-4 text-center">
              <div class="relative inline-block align-middle">
                <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-2">
                  <img v-if="newUser.avatar" :src="newUser.avatar" class="w-16 h-16 rounded-full object-cover" />
                  <span v-else class="text-xl font-medium text-gray-700">{{ newUser.name ? newUser.name.charAt(0) : '?' }}</span>
                </div>
                <input type="file" @change="handleAvatarUpload" accept="image/*" class="hidden" ref="avatarInput" />
                <button type="button" @click="$refs.avatarInput.click()" class="absolute -bottom-1 -right-1 bg-blue-500 text-white rounded-full p-1.5 hover:bg-blue-600">
                  📷
                </button>
              </div>
            </div>

            <!-- Informações Básicas -->
            <div class="mb-4">
              <h4 class="font-medium text-gray-900 border-b pb-1 mb-3">Informações Básicas</h4>
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-7">
                  <label class="form-label">Nome Completo <span class="text-red-500">*</span></label>
                  <input v-model="newUser.name" type="text" class="form-input" required placeholder="Digite o nome completo" />
                </div>
                <div class="md:col-span-5">
                  <label class="form-label">Email <span class="text-red-500">*</span></label>
                  <input v-model="newUser.email" type="email" class="form-input" required placeholder="exemplo@hospital.com" />
                </div>
              </div>
            </div>

            <!-- Dados Profissionais -->
            <div class="mb-4">
              <h4 class="font-medium text-gray-900 border-b pb-1 mb-3">Dados Profissionais</h4>
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                  <label class="form-label">Cargo <span class="text-red-500">*</span></label>
                  <select v-model="newUser.role_id" class="form-input" required>
                    <option value="">Selecione um cargo</option>
                    <option 
                      v-for="role in availableRoles" 
                      :key="role.value" 
                      :value="role.value"
                    >
                      {{ role.label }}
                    </option>
                  </select>
                </div>
                <div class="md:col-span-4">
                  <label class="form-label">Departamento <span class="text-red-500">*</span></label>
                  <select v-model="newUser.department_id" class="form-input" required>
                    <option value="">Selecione um departamento</option>
                    <option 
                      v-for="department in availableDepartments" 
                      :key="department.value" 
                      :value="department.value"
                    >
                      {{ department.label }}
                    </option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="form-label">Data de Início</label>
                  <input v-model="newUser.startDate" type="date" class="form-input" />
                </div>
                <div class="md:col-span-2">
                  <label class="form-label">Estado</label>
                  <select v-model="newUser.status" class="form-input" required>
                    <option value="Ativo">Ativo</option>
                    <option value="Pendente">Pendente</option>
                    <option value="Inativo">Inativo</option>
                    <option value="Bloqueado">Bloqueado</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Dados Pessoais -->
            <div class="mb-4">
              <h4 class="font-medium text-gray-900 border-b pb-1 mb-3">Dados Pessoais</h4>
              <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                  <label class="form-label">Data de Nascimento</label>
                  <input v-model="newUser.birthDate" type="date" class="form-input" />
                </div>
                <div class="md:col-span-4">
                  <label class="form-label">Telefone</label>
                  <input v-model="newUser.phone" type="tel" class="form-input" placeholder="+351 912 345 678" />
                </div>
              </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 mt-4">
              <button type="button" @click="showAddUserModal = false" class="px-3 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 w-full sm:w-auto">
                Cancelar
              </button>
              <button type="submit" class="btn-primary w-full sm:w-auto">
                Adicionar Utilizador
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Import Users Modal -->
    <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showImportModal = false">
      <div class="relative top-20 mx-4 sm:mx-auto p-4 sm:p-6 border w-full max-w-2xl shadow-lg rounded-md bg-white max-h-[85vh] overflow-y-auto">
        <div class="mt-3">
          <h3 class="text-base sm:text-lg font-medium text-gray-900 mb-6">Importar Utilizadores</h3>
          
          <div class="space-y-6">
            <!-- File Upload -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
              <input type="file" @change="handleFileImport" accept=".csv,.xlsx,.xls" class="hidden" ref="fileInput" />
              <div class="space-y-2">
                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                  <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <div class="text-sm text-gray-600">
                  <button type="button" @click="$refs.fileInput.click()" class="font-medium text-blue-600 hover:text-blue-500">
                    Clique para fazer upload
                  </button>
                  ou arraste e solte
                </div>
                <p class="text-xs text-gray-500">CSV, XLSX até 10MB</p>
              </div>
            </div>

            <!-- Import Options -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Opções de Importação</h4>
              <div class="space-y-3">
                <div class="flex items-center">
                  <input v-model="importOptions.updateExisting" type="checkbox" class="form-checkbox" />
                  <label class="ml-2 text-sm text-gray-700">Atualizar utilizadores existentes</label>
                </div>
                <div class="flex items-center">
                  <input v-model="importOptions.sendWelcomeEmail" type="checkbox" class="form-checkbox" />
                  <label class="ml-2 text-sm text-gray-700">Enviar email de boas-vindas</label>
                </div>
                <div class="flex items-center">
                  <input v-model="importOptions.requireApproval" type="checkbox" class="form-checkbox" />
                  <label class="ml-2 text-sm text-gray-700">Requer aprovação do administrador</label>
                </div>
              </div>
            </div>

            <!-- Template Download -->
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
                <span class="text-sm text-blue-700">Não tem um ficheiro? </span>
                <button @click="downloadTemplate" class="text-sm font-medium text-blue-600 hover:text-blue-500 ml-1">
                  Descarregue o modelo
                </button>
              </div>
            </div>
          </div>

          <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 mt-6">
            <button @click="showImportModal = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 w-full sm:w-auto">
              Cancelar
            </button>
            <button @click="processImport" class="btn-primary w-full sm:w-auto">
              Importar Utilizadores
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- User Profile Modal -->
    <div v-if="showUserProfile" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showUserProfile = false">
      <div class="relative top-10 mx-4 sm:mx-auto p-4 sm:p-6 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-[85vh] overflow-y-auto">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-base sm:text-lg font-medium text-gray-900">Perfil do Utilizador</h3>
            <button @click="showUserProfile = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div v-if="selectedUser" class="space-y-6">
            <!-- User Header -->
            <div class="flex items-center space-x-6 bg-gray-50 p-6 rounded-lg">
              <div class="w-24 h-24 rounded-full bg-gray-300 flex items-center justify-center">
                <img v-if="selectedUser.avatar" :src="selectedUser.avatar" class="w-24 h-24 rounded-full object-cover" />
                <span v-else class="text-2xl font-medium text-gray-700">{{ selectedUser.name.charAt(0) }}</span>
              </div>
              <div class="flex-1">
                <h4 class="text-xl font-semibold text-gray-900">{{ selectedUser.name }}</h4>
                <p class="text-gray-600">{{ selectedUser.role }} - {{ selectedUser.department }}</p>
                <div class="flex items-center space-x-4 mt-2">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    selectedUser.status === 'Ativo' ? 'bg-green-100 text-green-800' :
                    selectedUser.status === 'Pendente' ? 'bg-yellow-100 text-yellow-800' :
                    'bg-red-100 text-red-800'
                  ]">
                    {{ selectedUser.status }}
                  </span>

                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Dados Pessoais -->
              <div class="space-y-4">
                <h5 class="font-medium text-gray-900 border-b pb-2">Dados Pessoais</h5>
                <div class="space-y-3">
                  <div>
                    <label class="text-sm font-medium text-gray-500">Email</label>
                    <p class="text-sm text-gray-900">{{ selectedUser.email }}</p>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-500">Data de Nascimento</label>
                    <p class="text-sm text-gray-900">{{ selectedUser.birthDate || 'Não informado' }}</p>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-500">Idade</label>
                    <p class="text-sm text-gray-900">{{ calculateAge(selectedUser.birthDate) }} anos</p>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-500">Telefone</label>
                    <p class="text-sm text-gray-900">{{ selectedUser.phone || 'Não informado' }}</p>
                  </div>
                </div>
              </div>

              <!-- Dados Profissionais -->
              <div class="space-y-4">
                <h5 class="font-medium text-gray-900 border-b pb-2">Dados Profissionais</h5>
                <div class="space-y-3">
                  <div>
                    <label class="text-sm font-medium text-gray-500">Data de Início</label>
                    <p class="text-sm text-gray-900">{{ selectedUser.startDate || 'Não informado' }}</p>
                  </div>
                  <div>
                    <label class="text-sm font-medium text-gray-500">Tempo de Trabalho</label>
                    <p class="text-sm text-gray-900">{{ calculateWorkTime(selectedUser.startDate) }}</p>
                  </div>

                  <div>
                    <label class="text-sm font-medium text-gray-500">Último Login</label>
                    <p class="text-sm text-gray-900">{{ selectedUser.lastLogin }}</p>
                    <p class="text-xs text-gray-500">{{ selectedUser.lastLoginDevice }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Progress and Statistics -->
            <div class="bg-gray-50 p-6 rounded-lg">
              <h5 class="font-medium text-gray-900 mb-4">Progresso e Estatísticas</h5>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                  <div class="text-2xl font-bold text-blue-600">{{ selectedUser.progress }}%</div>
                  <div class="text-sm text-gray-500">Progresso Geral</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-green-600">{{ selectedUser.completedCourses || 0 }}</div>
                  <div class="text-sm text-gray-500">Cursos Concluídos</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-bold text-yellow-600">{{ selectedUser.certificates || 0 }}</div>
                  <div class="text-sm text-gray-500">Certificados</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Auto-Registration Requests Modal -->
    <div v-if="showRegistrationRequests" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showRegistrationRequests = false">
      <div class="relative top-10 mx-auto p-6 border w-full max-w-6xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-medium text-gray-900">Solicitações de Auto-Registro</h3>
            <button @click="showRegistrationRequests = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="request in registrationRequests" :key="request.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ request.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ request.email }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ request.role }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ request.requestDate }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <button @click="approveRegistration(request)" class="text-green-600 hover:text-green-900">
                        ✅ Aprovar
                      </button>
                      <button @click="rejectRegistration(request)" class="text-red-600 hover:text-red-900">
                        ❌ Rejeitar
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Gestão de Utilizadores -->
    <div v-if="showUsersManagementModal" class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showUsersManagementModal = false">
      <!-- Overlay transparente -->
      <div class="absolute inset-0 bg-black bg-opacity-30" @click.self="showUsersManagementModal = false"></div>
      <!-- Conteúdo do Modal -->
      <div class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header do Modal -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-xl font-semibold text-gray-900">Gestão de Utilizadores</h3>
          <button @click="showUsersManagementModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <!-- Conteúdo -->
        <div class="p-6">
          <p class="text-sm text-gray-600">Gestão de Utilizadores em modal.</p>
        </div>
      </div>
    </div>

    <!-- Modal de Permissões -->
    <div v-if="showPermissionsModal" class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showPermissionsModal = false">
      <!-- Overlay transparente -->
      <div class="absolute inset-0 bg-black bg-opacity-30" @click.self="showPermissionsModal = false"></div>
      <!-- Conteúdo do Modal -->
      <div class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <!-- Header do Modal -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-xl font-semibold text-gray-900">Controle de Permissões e Perfis</h3>
          <button @click="showPermissionsModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        <!-- Conteúdo das Permissões -->
        <div class="p-6">
          <AdminPermissions />
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Gestão de Utilizadores -->
  <div v-if="showUsersManagementModal" class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showUsersManagementModal = false">
    <!-- Overlay transparente -->
    <div class="absolute inset-0 bg-black bg-opacity-30" @click.self="showUsersManagementModal = false"></div>
    <!-- Conteúdo do Modal -->
    <div class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header do Modal -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">Gestão de Utilizadores</h3>
        <button @click="showUsersManagementModal = false" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <!-- Conteúdo -->
      <div class="p-6">
        <p class="text-sm text-gray-600">Gestão de Utilizadores em modal.</p>
      </div>
    </div>
  </div>

  <!-- Modal de Permissões -->
  <div v-if="showPermissionsModal" class="fixed inset-0 z-50 flex items-center justify-center" @click.self="showPermissionsModal = false">
    <!-- Overlay transparente -->
    <div class="absolute inset-0 bg-black bg-opacity-30" @click="showPermissionsModal = false"></div>
    
    <!-- Conteúdo do Modal -->
    <div class="relative bg-white rounded-lg shadow-xl max-w-6xl w-full mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header do Modal -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200">
        <h3 class="text-xl font-semibold text-gray-900">Controle de Permissões e Perfis</h3>
        <button @click="showPermissionsModal = false" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      
      <!-- Conteúdo das Permissões -->
      <div class="p-6">
        <AdminPermissions />
      </div>
    </div>
  </div>
</template>

<script>
import UserAuthentication from './UserAuthentication.vue'
import AdminPermissions from './AdminPermissions.vue'
import AdminDashboard from './AdminDashboard.vue'
import AdminReports from './AdminReports.vue'
import AdminGroups from './AdminGroups.vue'
import AdminApprovals from './AdminApprovals.vue'
import AdminRoles from './AdminRoles.vue'
import AdminDepartments from './AdminDepartments.vue'
import { usePermissions } from '../../composables/usePermissions'
import { useSecurityIp } from '../../composables/useSecurityIp'
import { useUsers } from '../../composables/useUsers'
import { useRoles } from '../../composables/useRoles'
import { useDepartments } from '../../composables/useDepartments'

export default {
  name: 'AdminUsers',
  components: {
    UserAuthentication,
    AdminPermissions,
    AdminDashboard,
    AdminReports,
    AdminGroups,
    AdminApprovals,
    AdminRoles,
    AdminDepartments
  },
  setup() {
    const { can } = usePermissions()
    const {
      loading,
      error,
      policies,
      locks,
      listPolicies,
      createPolicy,
      deletePolicy,
      listLocks,
      unlockIp,
    } = useSecurityIp()
    // Users API
    const {
      loading: usersLoading,
      error: usersError,
      listUsers,
      getUser,
      createUser,
      updateUser,
      deleteUser: apiDeleteUser,
      resetPassword: apiResetPassword,
      enableMfa,
      disableMfa,
      bulkAction: apiBulkAction,
      importUsers: apiImportUsers,
      exportUsers: apiExportUsers,
    } = useUsers()
    
    // Roles and Departments API
    const { getRoleOptions } = useRoles()
    const { getDepartmentOptions } = useDepartments()

    return { 
      can, loading, error, policies, locks, listPolicies, createPolicy, deletePolicy, listLocks, unlockIp,
      // users api
      usersLoading, usersError, listUsers, getUser, createUser, updateUser, apiDeleteUser, apiResetPassword, enableMfa, disableMfa, apiBulkAction, apiImportUsers, apiExportUsers,
      // roles and departments api
      getRoleOptions, getDepartmentOptions
    }
  },
  data() {
    return {
      activeTab: 'users',
      searchTerm: '',
      statusFilter: '',
      progressFilter: '',
      currentPage: 1,
      itemsPerPage: 10,
      showAddUserModal: false,
      showEditUserModal: false,
      showBulkActions: false,
      showPasswordRecoverySettings: false,
      showPermissionsModal: false,
      showUsersManagementModal: false,
      newUser: {
        name: '',
        email: '',
        role_id: '',
        birthDate: '',
        phone: '',
        status: 'Pendente',
        department_id: '',
        startDate: '',
        avatar: null
      },
      editedUser: {
        id: null,
        name: '',
        email: '',
        role_id: '',
        department_id: '',
        phone: '',
        status: 'Pendente',
        startDate: '',
      },
      showImportModal: false,
      showUserProfile: false,
      showRegistrationRequests: false,
      importFile: null,
      selectedUser: null,
      fetchTimer: null,
      totalCount: 0,
      // Enterprise - new policy form state
      newPolicy: {
        type: 'whitelist',
        ip_cidr: '',
        reason: ''
      },
      importOptions: {
        updateExisting: false,
        sendWelcomeEmail: true,
        requireApproval: true
      },
      // Arrays dinâmicos para cargos e departamentos
      availableRoles: [],
      availableDepartments: [],
      registrationRequests: [
        {
          id: 1,
          name: 'Ana Costa',
          email: 'ana.costa@hospital.com',
          role: 'Enfermeira',
          requestDate: '16/01/2024 09:30'
        },
        {
          id: 2,
          name: 'Pedro Almeida',
          email: 'pedro.almeida@hospital.com',
          role: 'Técnico',
          requestDate: '16/01/2024 11:15'
        }
      ],
      recoveryStats: {
        today: 12,
        week: 45,
        pending: 8,
        successRate: 94
      },
      passwordRecoveryRequests: [
        {
          id: 1,
          user: { name: 'Dr. João Silva', email: 'joao@hospital.com' },
          method: 'Email',
          status: 'Pendente',
          requestDate: '15/01/2024 14:30',
          expiresAt: '16/01/2024 14:30'
        },
        {
          id: 2,
          user: { name: 'Dra. Maria Santos', email: 'maria@hospital.com' },
          method: 'SMS',
          status: 'Aprovado',
          requestDate: '15/01/2024 10:15',
          expiresAt: '16/01/2024 10:15'
        },
        {
          id: 3,
          user: { name: 'Carlos Oliveira', email: 'carlos@hospital.com' },
          method: 'Email',
          status: 'Expirado',
          requestDate: '14/01/2024 16:45',
          expiresAt: '15/01/2024 16:45'
        }
      ],
      recoverySettings: {
        expirationHours: 24,
        maxAttemptsPerDay: 3,
        requireManualApproval: false,
        allowEmail: true,
        allowSMS: true,
        allowSecurityQuestions: false
      },
      users: [
        { 
          id: 1, 
          name: 'Dr. João Silva', 
          email: 'joao@hospital.com', 
          role: 'Médico', 
          status: 'Ativo', 
          progress: 100, 
          registrationDate: '15/01/2024',
          mfaEnabled: true,
          lastLogin: '15/01/2024 14:30',
          lastLoginDevice: 'Chrome/Windows',
          selected: false,
          birthDate: '1980-05-15',
          phone: '+351 912 345 678',
          department: 'Cardiologia',
          startDate: '2020-03-01',

          avatar: null,
          completedCourses: 12,
          certificates: 8
        },
        { 
          id: 2, 
          name: 'Dra. Maria Santos', 
          email: 'maria@hospital.com', 
          role: 'Médico', 
          status: 'Ativo', 
          progress: 85, 
          registrationDate: '12/01/2024',
          mfaEnabled: true,
          lastLogin: '15/01/2024 10:15',
          lastLoginDevice: 'Safari/macOS',
          selected: false
        },
        { 
          id: 3, 
          name: 'Dr. Pedro Costa', 
          email: 'pedro@hospital.com', 
          role: 'Médico', 
          status: 'Pendente', 
          progress: 45, 
          registrationDate: '10/01/2024',
          mfaEnabled: false,
          lastLogin: '14/01/2024 16:45',
          lastLoginDevice: 'Firefox/Linux',
          selected: false
        },
        { 
          id: 4, 
          name: 'Dra. Ana Lima', 
          email: 'ana@hospital.com', 
          role: 'Enfermeiro', 
          status: 'Inativo', 
          progress: 20, 
          registrationDate: '08/01/2024',
          mfaEnabled: false,
          lastLogin: '10/01/2024 09:30',
          lastLoginDevice: 'Chrome/Android',
          selected: false
        },
        { 
          id: 5, 
          name: 'Carlos Oliveira', 
          email: 'carlos@hospital.com', 
          role: 'Técnico', 
          status: 'Ativo', 
          progress: 90, 
          registrationDate: '05/01/2024',
          mfaEnabled: true,
          lastLogin: '15/01/2024 08:20',
          lastLoginDevice: 'Edge/Windows',
          selected: false
        },
        { 
          id: 6, 
          name: 'Fernanda Silva', 
          email: 'fernanda@hospital.com', 
          role: 'Administrativo', 
          status: 'Pendente', 
          progress: 60, 
          registrationDate: '03/01/2024',
          mfaEnabled: false,
          lastLogin: '12/01/2024 15:10',
          lastLoginDevice: 'Chrome/Windows',
          selected: false
        }
      ]
    }
  },
  async mounted() {
    await Promise.all([
      this.fetchUsers(),
      this.loadRolesAndDepartments()
    ])
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
    },
    totalPages() {
      const per = Number(this.itemsPerPage) || 10
      return Math.max(1, Math.ceil(this.totalCount / per))
    }
  },
  watch: {
    searchTerm() {
      this.debounceFetch()
    },
    statusFilter() {
      this.debounceFetch()
    },
    currentPage() {
      this.fetchUsers()
    },
    itemsPerPage() {
      this.currentPage = 1
      this.fetchUsers()
    }
  },
  methods: {
    prevPage() {
      if (this.currentPage > 1) this.currentPage -= 1
    },
    nextPage() {
      if (this.currentPage < this.totalPages) this.currentPage += 1
    },
    debounceFetch() {
      if (this.fetchTimer) clearTimeout(this.fetchTimer)
      this.fetchTimer = setTimeout(() => {
        this.currentPage = 1
        this.fetchUsers()
      }, 300)
    },
    async fetchUsers() {
      try {
        const res = await this.listUsers({ 
          q: this.searchTerm || undefined, 
          status: this.statusFilter || undefined,
          page: this.currentPage,
          per_page: this.itemsPerPage,
        })
        // Normalizar formatos comuns de API
        // Possíveis formatos:
        // - Array direto: [ ... ]
        // - { users: [...], total }
        // - { data: [...], total }
        // - { data: { data: [...], total/meta } } (Laravel paginator)
        let users = []
        let total = 0
        if (Array.isArray(res)) {
          users = res
          total = res.length
        } else if (Array.isArray(res?.users)) {
          users = res.users
          total = res?.total ?? res?.totalCount ?? res?.meta?.total ?? users.length
        } else if (Array.isArray(res?.data)) {
          users = res.data
          total = res?.total ?? res?.totalCount ?? res?.meta?.total ?? users.length
        } else if (Array.isArray(res?.data?.data)) {
          users = res.data.data
          total = res?.data?.total ?? res?.data?.totalCount ?? res?.data?.meta?.total ?? users.length
        } else if (Array.isArray(res?.data?.users)) {
          users = res.data.users
          total = res?.data?.total ?? res?.data?.totalCount ?? res?.data?.meta?.total ?? users.length
        } else if (Array.isArray(res?.data?.data?.data)) {
          // Ex: { data: { data: { data: [...], total/meta } } }
          users = res.data.data.data
          const inner = res.data.data
          total = inner?.total ?? inner?.totalCount ?? inner?.meta?.total ?? users.length
        }
        this.users = users
        this.totalCount = total
      } catch (e) {
        // erro já está em usersError
      }
    },
    // Enterprise - loaders and actions
    async loadEnterprise() {
      try {
        await Promise.all([this.listPolicies(), this.listLocks()])
      } catch (e) {
        // error is handled in composable state
      }
    },
    async refreshSecurity() {
      await this.loadEnterprise()
    },
    async addPolicy() {
      if (!this.newPolicy.ip_cidr || !this.newPolicy.type) return
      try {
        await this.createPolicy({
          type: this.newPolicy.type,
          ip_cidr: this.newPolicy.ip_cidr,
          reason: this.newPolicy.reason || undefined,
        })
        // reset minimal
        this.newPolicy.ip_cidr = ''
        this.newPolicy.reason = ''
      } catch (e) {}
    },
    async removePolicy(id) {
      try { await this.deletePolicy(id) } catch (e) {}
    },
    async unlock(ip) {
      try { await this.unlockIp(ip) } catch (e) {}
    },
    async loadRolesAndDepartments() {
      try {
        const [rolesResponse, departmentsResponse] = await Promise.all([
          this.getRoleOptions(),
          this.getDepartmentOptions()
        ])
        
        this.availableRoles = rolesResponse.map(role => ({
          value: role.id,
          label: role.name
        }))
        
        this.availableDepartments = departmentsResponse.map(department => ({
          value: department.id,
          label: department.name
        }))
      } catch (e) {
        console.error('Erro ao carregar cargos e departamentos:', e)
      }
    },
    async addUser() {
      try {
        if (!this.validateRoleAndDepartment()) return
        await this.createUser(this.newUser)
        await this.fetchUsers()
        this.newUser = {
          name: '',
          email: '',
          role_id: '',
          birthDate: '',
          phone: '',
          status: 'Pendente',
          department_id: '',
          startDate: '',
          avatar: null
        }
        this.showAddUserModal = false
      } catch (e) {
        console.error('Erro ao adicionar utilizador:', e)
      }
    },
    async editUser(user) {
      try {
        // Carregar detalhes completos
        const res = await this.getUser(user.id)
        const u = res?.data || res || user
        this.editedUser = {
          id: u.id,
          name: u.name || '',
          email: u.email || '',
          role_id: u.role_id ?? u.role?.id ?? u.roleId ?? '',
          department_id: u.department_id ?? u.department?.id ?? u.departmentId ?? '',
          phone: u.phone || '',
          status: u.status || 'Pendente',
          startDate: u.startDate || u.start_date || '',
        }
        this.showEditUserModal = true
      } catch (e) {
        console.error('Erro ao carregar utilizador para edição:', e)
        alert('Não foi possível carregar os dados do utilizador.')
      }
    },
    
    async deleteUser(user) {
      if (!confirm(`Tem a certeza que deseja eliminar ${user.name}?`)) return
      try {
        await this.apiDeleteUser(user.id)
        await this.fetchUsers()
        alert('Utilizador eliminado com sucesso.')
      } catch (e) {
        console.error('Erro ao eliminar utilizador:', e)
        alert('Falha ao eliminar o utilizador.')
      }
    },
    async toggleMFA(user) {
      try {
        if (user.mfaEnabled) {
          if (!confirm(`Desativar MFA para ${user.name}?`)) return
          await this.disableMfa(user.id)
          user.mfaEnabled = false
          alert('MFA desativado.')
        } else {
          if (!confirm(`Ativar MFA para ${user.name}?`)) return
          await this.enableMfa(user.id)
          user.mfaEnabled = true
          alert('MFA ativado.')
        }
      } catch (e) {
        console.error('Erro ao alternar MFA:', e)
        alert('Falha ao alterar o estado do MFA.')
      }
    },
    async resetPassword(user) {
      if (!confirm(`Resetar a senha de ${user.name}?`)) return
      try {
        await this.apiResetPassword(user.id)
        alert('Instruções de redefinição de senha enviadas.')
      } catch (e) {
        console.error('Erro ao resetar senha:', e)
        alert('Falha ao resetar a senha.')
      }
    },
    // Validação do formulário de edição (cargo e departamento)
    validateEditedRoleAndDepartment() {
      const isValidRole = this.availableRoles.some(role => role.value === this.editedUser.role_id)
      const isValidDepartment = this.availableDepartments.some(dept => dept.value === this.editedUser.department_id)

      if (!isValidRole && this.editedUser.role_id) {
        console.warn('Cargo inválido:', this.editedUser.role_id)
        alert('Selecione um cargo válido.')
        return false
      }

      if (!isValidDepartment && this.editedUser.department_id) {
        console.warn('Departamento inválido:', this.editedUser.department_id)
        alert('Selecione um departamento válido.')
        return false
      }

      return true
    },
    async saveEditedUser() {
      try {
        if (!this.editedUser.id) return
        if (!this.validateEditedRoleAndDepartment()) return
        const payload = {
          name: this.editedUser.name,
          email: this.editedUser.email,
          role_id: this.editedUser.role_id,
          department_id: this.editedUser.department_id,
          phone: this.editedUser.phone || undefined,
          status: this.editedUser.status,
          startDate: this.editedUser.startDate || undefined,
        }
        await this.updateUser(this.editedUser.id, payload)
        await this.fetchUsers()
        this.showEditUserModal = false
        alert('Utilizador atualizado com sucesso.')
      } catch (e) {
        console.error('Erro ao guardar alterações:', e)
        alert('Falha ao atualizar o utilizador.')
      }
    },
    // Bulk Actions Methods
    async bulkAction(action) {
      try {
        const ids = this.users.filter(u => u.selected).map(u => u.id)
        if (ids.length === 0) return (this.showBulkActions = false)
        await this.apiBulkAction({ action, ids })
        await this.fetchUsers()
      } catch (e) {
      } finally {
        this.showBulkActions = false
      }
    },
    // Password Recovery Methods
    getRecoveryMethodClass(method) {
      const classes = {
        'Email': 'bg-blue-100 text-blue-800',
        'SMS': 'bg-green-100 text-green-800',
        'Perguntas de Segurança': 'bg-purple-100 text-purple-800'
      }
      return classes[method] || 'bg-gray-100 text-gray-800'
    },
    getRecoveryStatusClass(status) {
      const classes = {
        'Pendente': 'bg-yellow-100 text-yellow-800',
        'Aprovado': 'bg-green-100 text-green-800',
        'Rejeitado': 'bg-red-100 text-red-800',
        'Expirado': 'bg-gray-100 text-gray-800',
        'Concluído': 'bg-blue-100 text-blue-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    },
    approveRecovery(request) {
      request.status = 'Aprovado'
      console.log('Solicitação aprovada:', request)
      // Implementar aprovação
    },
    rejectRecovery(request) {
      request.status = 'Rejeitado'
      console.log('Solicitação rejeitada:', request)
      // Implementar rejeição
    },
    resendRecovery(request) {
      console.log('Reenviar solicitação:', request)
      // Implementar reenvio
    },
    saveRecoverySettings() {
      console.log('Configurações de recuperação guardadas:', this.recoverySettings)
      this.showPasswordRecoverySettings = false
    },
    
    // Avatar upload
    handleAvatarUpload(event) {
      const file = event.target.files[0]
      if (file) {
        const reader = new FileReader()
        reader.onload = (e) => {
          this.newUser.avatar = e.target.result
        }
        reader.readAsDataURL(file)
      }
    },
    
    // Export users
    exportUsers() {
      // usa API para exportação (download gerido no composable)
      this.apiExportUsers({ format: 'csv', params: { q: this.searchTerm || undefined, status: this.statusFilter || undefined } })
    },
    
    generateCSV(data) {
      const headers = ['Nome', 'Email', 'Cargo', 'Departamento', 'Status', 'Data Nascimento', 'Telefone', 'Data Início']
      const csvRows = []
      csvRows.push(headers.join(','))
      
      data.forEach(user => {
        const row = [
          user.name,
          user.email,
          user.role,
          user.department || '',
          user.status,
          user.birthDate || '',
          user.phone || '',
          user.startDate || ''
        ]
        csvRows.push(row.join(','))
      })
      
      return csvRows.join('\n')
    },
    
    // Import users
    handleFileImport(event) {
      const file = event.target.files[0]
      if (file) {
        this.importFile = file
      }
    },
    
    downloadTemplate() {
      const templateData = [{
        name: 'Exemplo Nome',
        email: 'exemplo@hospital.com',
        role: 'Médico',
        department: 'Cardiologia',
        status: 'Ativo',
        birthDate: '1980-01-01',
        phone: '+351 912 345 678',
        startDate: '2024-01-01'
      }]
      
      const csvContent = this.generateCSV(templateData)
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', 'modelo_utilizadores.csv')
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    },
    
    async processImport() {
      if (!this.importFile) return
      try {
        await this.apiImportUsers(this.importFile, this.importOptions)
        await this.fetchUsers()
        this.showImportModal = false
        this.importFile = null
      } catch (e) {}
    },
    
    // User profile
    viewUser(user) {
      this.selectedUser = user
      this.showUserProfile = true
    },
    
    calculateAge(birthDate) {
      if (!birthDate) return 'N/A'
      const today = new Date()
      const birth = new Date(birthDate)
      let age = today.getFullYear() - birth.getFullYear()
      const monthDiff = today.getMonth() - birth.getMonth()
      if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--
      }
      return age
    },
    
    calculateWorkTime(startDate) {
      if (!startDate) return 'N/A'
      const today = new Date()
      const start = new Date(startDate)
      const diffTime = Math.abs(today - start)
      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
      const years = Math.floor(diffDays / 365)
      const months = Math.floor((diffDays % 365) / 30)
      
      if (years > 0) {
        return `${years} ano${years > 1 ? 's' : ''} e ${months} mês${months !== 1 ? 'es' : ''}`
      } else {
        return `${months} mês${months !== 1 ? 'es' : ''}`
      }
    },
    
    // Auto-registration
    approveRegistration(request) {
      console.log('Aprovando registro:', request)
      // Adicionar usuário à lista principal
      const newUser = {
        id: Math.max(...this.users.map(u => u.id)) + 1,
        name: request.name,
        email: request.email,
        role: request.role,
        status: 'Ativo',
        progress: 0,
        registrationDate: new Date().toLocaleDateString('pt-PT'),
        mfaEnabled: false,
        lastLogin: 'Nunca',
        lastLoginDevice: 'N/A',
        selected: false
      }
      this.users.push(newUser)
      
      // Remover da lista de solicitações
      this.registrationRequests = this.registrationRequests.filter(r => r.id !== request.id)
    },
    
    rejectRegistration(request) {
       console.log('Rejeitando registro:', request)
       this.registrationRequests = this.registrationRequests.filter(r => r.id !== request.id)
     },

    // Métodos para gerenciar cargos e departamentos dinamicamente
    addRole(newRole) {
      if (newRole && !this.availableRoles.find(role => role.value === newRole)) {
        this.availableRoles.push({
          value: newRole,
          label: newRole
        })
      }
    },

    removeRole(roleValue) {
      this.availableRoles = this.availableRoles.filter(role => role.value !== roleValue)
    },

    addDepartment(newDepartment) {
      if (newDepartment && !this.availableDepartments.find(dept => dept.value === newDepartment)) {
        this.availableDepartments.push({
          value: newDepartment,
          label: newDepartment
        })
      }
    },

    removeDepartment(departmentValue) {
      this.availableDepartments = this.availableDepartments.filter(dept => dept.value !== departmentValue)
    },

    // Método para validar se cargo e departamento são válidos
    validateRoleAndDepartment() {
      const isValidRole = this.availableRoles.some(role => role.value === this.newUser.role_id)
      const isValidDepartment = this.availableDepartments.some(dept => dept.value === this.newUser.department_id)

      if (!isValidRole && this.newUser.role_id) {
        console.warn('Cargo inválido:', this.newUser.role_id)
        return false
      }

      if (!isValidDepartment && this.newUser.department_id) {
        console.warn('Departamento inválido:', this.newUser.department_id)
        return false
      }

      return true
    }
   }
 }
</script>