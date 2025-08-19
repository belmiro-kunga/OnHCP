<template>
  <div class="p-6 bg-white rounded-lg shadow-sm">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Controle de Permissões e Perfis</h2>
      <p class="text-gray-600">Gerencie papéis, permissões e controle de acesso do sistema</p>
    </div>

    <!-- Tabs Navigation -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === tab.id
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          {{ tab.name }}
        </button>
      </nav>
    </div>

    <!-- Roles Management Tab -->
    <div v-if="activeTab === 'roles'" class="space-y-6">
      <!-- Actions Bar -->
      <div class="flex justify-between items-center">
        <div class="flex space-x-4">
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Pesquisar papéis..."
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <select
            v-model="roleFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todos os Tipos</option>
            <option value="system">Sistema</option>
            <option value="department">Departamento</option>
            <option value="custom">Personalizado</option>
          </select>
        </div>
        <button
          @click="showAddRoleModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <span>Novo Papel</span>
        </button>
      </div>

      <!-- Roles Table -->
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Papel</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hierarquia</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizadores</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissões</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="role in filteredRoles" :key="role.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div :class="getRoleIconClass(role.type)" class="h-10 w-10 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                    <div class="text-sm text-gray-500">{{ role.description }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getRoleTypeClass(role.type)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ getRoleTypeLabel(role.type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ role.hierarchy || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ role.userCount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ role.permissionCount }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="role.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ role.active ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                <button @click="editRole(role)" class="text-blue-600 hover:text-blue-900">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
                <button @click="managePermissions(role)" class="text-green-600 hover:text-green-900">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                  </svg>
                </button>
                <button @click="deleteRole(role)" class="text-red-600 hover:text-red-900">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Permissions Management Tab -->
    <div v-if="activeTab === 'permissions'" class="space-y-6">
      <!-- Module Permissions -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div v-for="module in modules" :key="module.id" class="bg-gray-50 rounded-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">{{ module.name }}</h3>
            <span class="text-sm text-gray-500">{{ module.permissions.length }} permissões</span>
          </div>
          <div class="space-y-3">
            <div v-for="permission in module.permissions" :key="permission.id" class="flex items-center justify-between">
              <div>
                <div class="text-sm font-medium text-gray-900">{{ permission.name }}</div>
                <div class="text-xs text-gray-500">{{ permission.description }}</div>
              </div>
              <div class="flex items-center space-x-2">
                <span :class="permission.critical ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'" class="px-2 py-1 text-xs rounded-full">
                  {{ permission.critical ? 'Crítica' : 'Normal' }}
                </span>
                <button @click="editPermission(permission)" class="text-blue-600 hover:text-blue-900">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- RBAC Configuration Tab -->
    <div v-if="activeTab === 'rbac'" class="space-y-6">
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-lg font-medium text-blue-900 mb-2">Controle de Acesso Baseado em Papéis (RBAC)</h3>
        <p class="text-blue-700">Configure permissões baseadas em papéis de utilizador</p>
      </div>

      <!-- Role Permission Matrix -->
      <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
          <h4 class="text-lg font-medium text-gray-900">Matriz de Permissões por Papel</h4>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Módulo/Permissão</th>
                <th v-for="role in roles" :key="role.id" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ role.name }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="module in modules" :key="module.id">
                <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 bg-gray-50">
                  {{ module.name }}
                </td>
                <td v-for="role in roles" :key="role.id" class="px-3 py-4 text-center">
                  <input
                    type="checkbox"
                    :checked="hasModuleAccess(role.id, module.id)"
                    @change="toggleModuleAccess(role.id, module.id)"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                </td>
              </tr>
              <template v-for="module in modules" :key="'perms-' + module.id">
                <tr v-for="permission in module.permissions" :key="permission.id" class="bg-gray-25">
                  <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700 pl-12">
                    {{ permission.name }}
                  </td>
                  <td v-for="role in roles" :key="role.id" class="px-3 py-3 text-center">
                    <input
                      type="checkbox"
                      :checked="hasPermission(role.id, permission.id)"
                      @change="togglePermission(role.id, permission.id)"
                      class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                    />
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ABAC Configuration Tab -->
    <div v-if="activeTab === 'abac'" class="space-y-6">
      <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
        <h3 class="text-lg font-medium text-purple-900 mb-2">Controle de Acesso Baseado em Atributos (ABAC)</h3>
        <p class="text-purple-700">Configure regras dinâmicas baseadas em atributos</p>
      </div>

      <!-- Actions Bar -->
      <div class="flex justify-between items-center">
        <h4 class="text-lg font-medium text-gray-900">Regras ABAC</h4>
        <button
          @click="showAddRuleModal = true"
          class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center space-x-2"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
          </svg>
          <span>Adicionar Regra</span>
        </button>
      </div>

      <!-- ABAC Rules List -->
      <div class="space-y-4">
        <div v-for="rule in abacRules" :key="rule.id" class="bg-white border border-gray-200 rounded-lg p-6">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-2">
                <h5 class="text-lg font-medium text-gray-900">{{ rule.name }}</h5>
                <span :class="rule.active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs rounded-full">
                  {{ rule.active ? 'Ativa' : 'Inativa' }}
                </span>
              </div>
              <p class="text-gray-600 mb-4">{{ rule.description }}</p>
              
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                  <span class="text-sm font-medium text-gray-500">Sujeito:</span>
                  <p class="text-sm text-gray-900">{{ rule.subject }}</p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-500">Recurso:</span>
                  <p class="text-sm text-gray-900">{{ rule.resource }}</p>
                </div>
                <div>
                  <span class="text-sm font-medium text-gray-500">Ação:</span>
                  <p class="text-sm text-gray-900">{{ rule.action }}</p>
                </div>
              </div>
              
              <div>
                <span class="text-sm font-medium text-gray-500 mb-2 block">Condições:</span>
                <div class="space-y-2">
                  <div v-for="condition in rule.conditions" :key="condition.id" class="flex items-center space-x-2 text-sm">
                    <span class="bg-gray-100 px-2 py-1 rounded">{{ condition.attribute }}</span>
                    <span class="text-gray-500">{{ condition.operator }}</span>
                    <span class="bg-blue-100 px-2 py-1 rounded">{{ condition.value }}</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="flex space-x-2">
              <button @click="editRule(rule)" class="text-blue-600 hover:text-blue-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
              </button>
              <button @click="deleteRule(rule)" class="text-red-600 hover:text-red-900">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Hierarchy Management Tab -->
    <div v-if="activeTab === 'hierarchy'" class="space-y-6">
      <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <h3 class="text-lg font-medium text-green-900 mb-2">Hierarquia de Permissões</h3>
        <p class="text-green-700">Gerencie a estrutura organizacional e hierarquia de permissões</p>
      </div>

      <!-- Organization Hierarchy Tree -->
      <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Estrutura Organizacional</h4>
        
        <div v-for="org in organizationHierarchy" :key="org.id" class="space-y-4">
          <!-- Organization Level -->
          <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
            <div :class="getHierarchyIconClass(org.type)" class="w-8 h-8 rounded-full flex items-center justify-center">
              <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4zM18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z"></path>
              </svg>
            </div>
            <div class="flex-1">
              <div class="flex items-center justify-between">
                <div>
                  <h5 class="font-medium text-gray-900">{{ org.name }}</h5>
                  <span class="text-sm text-gray-500">{{ getHierarchyTypeLabel(org.type) }} • {{ org.userCount }} utilizadores</span>
                </div>
                <button @click="editHierarchy(org)" class="text-blue-600 hover:text-blue-900">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>
          
          <!-- Department Level -->
          <div v-if="org.children" class="ml-8 space-y-3">
            <div v-for="dept in org.children" :key="dept.id" class="space-y-2">
              <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                <div :class="getHierarchyIconClass(dept.type)" class="w-6 h-6 rounded-full flex items-center justify-center">
                  <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <div>
                      <h6 class="font-medium text-gray-900">{{ dept.name }}</h6>
                      <span class="text-sm text-gray-500">{{ getHierarchyTypeLabel(dept.type) }} • {{ dept.userCount }} utilizadores</span>
                    </div>
                    <button @click="editHierarchy(dept)" class="text-green-600 hover:text-green-900">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
              
              <!-- Team Level -->
              <div v-if="dept.children" class="ml-6 space-y-2">
                <div v-for="team in dept.children" :key="team.id" class="flex items-center space-x-3 p-2 bg-purple-50 rounded-lg">
                  <div :class="getHierarchyIconClass(team.type)" class="w-5 h-5 rounded-full flex items-center justify-center">
                    <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <circle cx="10" cy="10" r="10"></circle>
                    </svg>
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center justify-between">
                      <div>
                        <span class="font-medium text-gray-900">{{ team.name }}</span>
                        <span class="text-sm text-gray-500 ml-2">{{ getHierarchyTypeLabel(team.type) }} • {{ team.userCount }} utilizadores</span>
                      </div>
                      <button @click="editHierarchy(team)" class="text-purple-600 hover:text-purple-900">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Role Modal -->
    <div v-if="showAddRoleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Novo Papel</h3>
          <form @submit.prevent="addRole">
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nome</label>
                <input v-model="newRole.name" type="text" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Descrição</label>
                <textarea v-model="newRole.description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Tipo</label>
                <select v-model="newRole.type" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                  <option value="custom">Personalizado</option>
                  <option value="department">Departamento</option>
                  <option value="system">Sistema</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Hierarquia</label>
                <input v-model="newRole.hierarchy" type="text" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500" />
              </div>
            </div>
            <div class="flex justify-end space-x-3 mt-6">
              <button type="button" @click="showAddRoleModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                Cancelar
              </button>
              <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                Adicionar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Add ABAC Rule Modal -->
    <div v-if="showAddRuleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-2/3 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Nova Regra ABAC</h3>
          <form @submit.prevent="addRule">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Nome da Regra</label>
                <input v-model="newRule.name" type="text" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-purple-500 focus:border-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Sujeito</label>
                <input v-model="newRule.subject" type="text" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-purple-500 focus:border-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Recurso</label>
                <input v-model="newRule.resource" type="text" required class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-purple-500 focus:border-purple-500" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Ação</label>
                <select v-model="newRule.action" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                  <option value="read">Ler</option>
                  <option value="write">Escrever</option>
                  <option value="execute">Executar</option>
                  <option value="delete">Eliminar</option>
                </select>
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Descrição</label>
              <textarea v-model="newRule.description" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-purple-500 focus:border-purple-500"></textarea>
            </div>
            
            <div class="mb-4">
              <div class="flex items-center justify-between mb-2">
                <label class="block text-sm font-medium text-gray-700">Condições</label>
                <button type="button" @click="addCondition" class="text-purple-600 hover:text-purple-900 text-sm">
                  + Adicionar Condição
                </button>
              </div>
              <div class="space-y-2">
                <div v-for="(condition, index) in newRule.conditions" :key="index" class="flex items-center space-x-2">
                  <input v-model="condition.attribute" placeholder="Atributo" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" />
                  <select v-model="condition.operator" class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500">
                    <option value="equals">Igual</option>
                    <option value="not_equals">Diferente</option>
                    <option value="greater_than">Maior que</option>
                    <option value="less_than">Menor que</option>
                    <option value="contains">Contém</option>
                  </select>
                  <input v-model="condition.value" placeholder="Valor" class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500" />
                  <button type="button" @click="removeCondition(index)" class="text-red-600 hover:text-red-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showAddRuleModal = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                Cancelar
              </button>
              <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-md hover:bg-purple-700">
                Adicionar Regra
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
  name: 'AdminPermissions',
  data() {
    return {
      activeTab: 'roles',
      searchTerm: '',
      roleFilter: '',
      showAddRoleModal: false,
      showAddRuleModal: false,
      showAddHierarchyModal: false,
      
      tabs: [
        { id: 'roles', name: 'Papéis' },
        { id: 'permissions', name: 'Permissões' },
        { id: 'rbac', name: 'RBAC' },
        { id: 'abac', name: 'ABAC' },
        { id: 'hierarchy', name: 'Hierarquia' }
      ],
      
      newRole: {
        name: '',
        description: '',
        type: 'custom',
        hierarchy: '',
        active: true
      },
      
      newRule: {
        name: '',
        description: '',
        subject: '',
        resource: '',
        action: 'read',
        conditions: [{
          attribute: '',
          operator: 'equals',
          value: ''
        }],
        active: true
      },
      
      roles: [],
      
      modules: [],
      
      rolePermissions: {
        1: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21], // Admin - todas
        2: [5, 6, 7, 9, 10, 11, 12, 13, 15], // Médico
        3: [7, 9, 10, 12, 15], // Enfermeiro
        4: [9, 10, 11, 12, 4], // Rececionista
        5: [7, 12, 15] // Convidado
      },
      
      policies: [],
      
      abacRules: [],
      
      organizationHierarchy: []
    }
  },
  computed: {
    filteredRoles() {
      return this.roles.filter(role => {
        const matchesSearch = role.name.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            role.description.toLowerCase().includes(this.searchTerm.toLowerCase())
        const matchesFilter = !this.roleFilter || role.type === this.roleFilter
        return matchesSearch && matchesFilter
      })
    }
  },
  methods: {
    // Role Management Methods
    addRole() {
      const newRole = {
        id: this.roles.length + 1,
        ...this.newRole,
        userCount: 0,
        permissionCount: 0
      }
      this.roles.push(newRole)
      this.showAddRoleModal = false
      this.resetNewRole()
    },
    
    editRole(role) {
      // Implementar edição de papel
      console.log('Editar papel:', role)
    },
    
    deleteRole(role) {
      const index = this.roles.findIndex(r => r.id === role.id)
      if (index > -1) {
        this.roles.splice(index, 1)
      }
    },
    
    managePermissions(role) {
      // Implementar gestão de permissões
      console.log('Gerir permissões para:', role)
    },
    
    resetNewRole() {
      this.newRole = {
        name: '',
        description: '',
        type: 'custom',
        hierarchy: '',
        active: true
      }
    },
    
    // Permission Management Methods
    editPermission(permission) {
      // Implementar edição de permissão
      console.log('Editar permissão:', permission)
    },
    
    // RBAC Methods
    hasModuleAccess(roleId, moduleId) {
      const rolePermissions = this.rolePermissions[roleId] || []
      const module = this.modules.find(m => m.id === moduleId)
      if (!module) return false
      
      return module.permissions.some(permission => 
        rolePermissions.includes(permission.id)
      )
    },
    
    hasPermission(roleId, permissionId) {
      const rolePermissions = this.rolePermissions[roleId] || []
      return rolePermissions.includes(permissionId)
    },
    
    toggleModuleAccess(roleId, moduleId) {
      const module = this.modules.find(m => m.id === moduleId)
      if (!module) return
      
      const hasAccess = this.hasModuleAccess(roleId, moduleId)
      
      if (!this.rolePermissions[roleId]) {
        this.rolePermissions[roleId] = []
      }
      
      if (hasAccess) {
        // Remove todas as permissões do módulo
        module.permissions.forEach(permission => {
          const index = this.rolePermissions[roleId].indexOf(permission.id)
          if (index > -1) {
            this.rolePermissions[roleId].splice(index, 1)
          }
        })
      } else {
        // Adiciona todas as permissões do módulo
        module.permissions.forEach(permission => {
          if (!this.rolePermissions[roleId].includes(permission.id)) {
            this.rolePermissions[roleId].push(permission.id)
          }
        })
      }
    },
    
    togglePermission(roleId, permissionId) {
      if (!this.rolePermissions[roleId]) {
        this.rolePermissions[roleId] = []
      }
      
      const index = this.rolePermissions[roleId].indexOf(permissionId)
      if (index > -1) {
        this.rolePermissions[roleId].splice(index, 1)
      } else {
        this.rolePermissions[roleId].push(permissionId)
      }
    },
    
    // ABAC Methods
    addRule() {
      const newRule = {
        id: this.abacRules.length + 1,
        ...this.newRule,
        conditions: this.newRule.conditions.map((condition, index) => ({
          id: Date.now() + index,
          ...condition
        }))
      }
      this.abacRules.push(newRule)
      this.showAddRuleModal = false
      this.resetNewRule()
    },
    
    editRule(rule) {
      // Implementar edição de regra
      console.log('Editar regra:', rule)
    },
    
    deleteRule(rule) {
      const index = this.abacRules.findIndex(r => r.id === rule.id)
      if (index > -1) {
        this.abacRules.splice(index, 1)
      }
    },
    
    addCondition() {
      this.newRule.conditions.push({
        attribute: '',
        operator: 'equals',
        value: ''
      })
    },
    
    removeCondition(index) {
      this.newRule.conditions.splice(index, 1)
    },
    
    resetNewRule() {
      this.newRule = {
        name: '',
        description: '',
        subject: '',
        resource: '',
        action: 'read',
        conditions: [{
          attribute: '',
          operator: 'equals',
          value: ''
        }],
        active: true
      }
    },
    
    // Hierarchy Methods
    editHierarchy(item) {
      // Implementar edição de hierarquia
      console.log('Editar hierarquia:', item)
    },
    
    // Utility Methods
    getRoleTypeClass(type) {
      const classes = {
        system: 'bg-blue-100 text-blue-800',
        department: 'bg-green-100 text-green-800',
        custom: 'bg-purple-100 text-purple-800'
      }
      return classes[type] || 'bg-gray-100 text-gray-800'
    },
    
    getRoleIconClass(type) {
      const classes = {
        system: 'bg-blue-500',
        department: 'bg-green-500',
        custom: 'bg-purple-500'
      }
      return classes[type] || 'bg-gray-500'
    },
    
    getRoleTypeLabel(type) {
      const labels = {
        system: 'Sistema',
        department: 'Departamento',
        custom: 'Personalizado'
      }
      return labels[type] || type
    },
    
    getHierarchyIconClass(type) {
      const classes = {
        organization: 'bg-blue-500',
        department: 'bg-green-500',
        team: 'bg-purple-500'
      }
      return classes[type] || 'bg-gray-500'
    },
    
    getHierarchyTypeLabel(type) {
      const labels = {
        organization: 'Organização',
        department: 'Departamento',
        team: 'Equipa'
      }
      return labels[type] || type
    }
  }
}
</script>