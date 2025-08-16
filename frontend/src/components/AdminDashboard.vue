<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-primary-800 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <h1 class="ml-3 text-2xl font-bold text-white">Painel Administrativo</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-primary-100">Admin: Maria Santos</span>
            <button
              @click="logout"
              class="text-sm text-red-300 hover:text-red-100 font-medium"
            >
              Sair
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Dashboard Overview -->
      <div v-if="activeTab === 'overview'">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="card">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Total Usuários</p>
                <p class="text-2xl font-bold text-gray-900">1,247</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Onboarding Completo</p>
                <p class="text-2xl font-bold text-gray-900">892</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Pendências</p>
                <p class="text-2xl font-bold text-gray-900">355</p>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-red-100 rounded-md flex items-center justify-center">
                  <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600">Problemas</p>
                <p class="text-2xl font-bold text-gray-900">23</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Atividade Recente</h3>
          <div class="space-y-4">
            <div class="flex items-center p-4 bg-blue-50 border border-blue-200 rounded-md">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">Dr. João Silva completou o onboarding</p>
                <p class="text-xs text-gray-600">Há 2 horas</p>
              </div>
            </div>

            <div class="flex items-center p-4 bg-green-50 border border-green-200 rounded-md">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">15 novos usuários registrados hoje</p>
                <p class="text-xs text-gray-600">Há 4 horas</p>
              </div>
            </div>

            <div class="flex items-center p-4 bg-yellow-50 border border-yellow-200 rounded-md">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-900">5 documentos aguardando aprovação</p>
                <p class="text-xs text-gray-600">Há 6 horas</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Management -->
      <div v-if="activeTab === 'users'">
        <div class="card">
          <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Gerenciar Usuários</h3>
            <button class="btn-primary">
              + Novo Usuário
            </button>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progresso</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in sampleUsers" :key="user.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.email }}</td>
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
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.progress }}%</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button class="text-primary-600 hover:text-primary-900 mr-3">Editar</button>
                    <button class="text-red-600 hover:text-red-900">Excluir</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Reports -->
      <div v-if="activeTab === 'reports'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Relatório de Onboarding</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Taxa de Conclusão</span>
                <span class="text-sm font-medium text-gray-900">71.5%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-green-600 h-2 rounded-full" style="width: 71.5%"></div>
              </div>
              
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Tempo Médio</span>
                <span class="text-sm font-medium text-gray-900">12 dias</span>
              </div>
              
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Documentos Pendentes</span>
                <span class="text-sm font-medium text-gray-900">127</span>
              </div>
            </div>
          </div>

          <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas do Sistema</h3>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Usuários Ativos (30 dias)</span>
                <span class="text-sm font-medium text-gray-900">1,089</span>
              </div>
              
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Novos Registros (7 dias)</span>
                <span class="text-sm font-medium text-gray-900">43</span>
              </div>
              
              <div class="flex justify-between items-center">
                <span class="text-sm text-gray-600">Taxa de Aprovação</span>
                <span class="text-sm font-medium text-gray-900">94.2%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Settings -->
      <div v-if="activeTab === 'settings'">
        <div class="card">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Configurações do Sistema</h3>
          
          <div class="space-y-6">
            <div>
              <label class="form-label">Nome da Instituição</label>
              <input type="text" class="form-input" value="Hospital Central" />
            </div>
            
            <div>
              <label class="form-label">Email de Notificações</label>
              <input type="email" class="form-input" value="admin@hospital.com" />
            </div>
            
            <div>
              <label class="form-label">Tempo Limite de Onboarding (dias)</label>
              <input type="number" class="form-input" value="30" />
            </div>
            
            <div class="flex items-center">
              <input type="checkbox" id="notifications" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" checked />
              <label for="notifications" class="ml-2 block text-sm text-gray-900">
                Enviar notificações por email
              </label>
            </div>
            
            <div class="pt-4">
              <button class="btn-primary mr-3">Salvar Configurações</button>
              <button class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                Cancelar
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'AdminDashboard',
  data() {
    return {
      activeTab: 'overview',
      tabs: [
        { id: 'overview', name: 'Visão Geral' },
        { id: 'users', name: 'Usuários' },
        { id: 'reports', name: 'Relatórios' },
        { id: 'settings', name: 'Configurações' }
      ],
      sampleUsers: [
        { id: 1, name: 'Dr. João Silva', email: 'joao@hospital.com', status: 'Ativo', progress: 100 },
        { id: 2, name: 'Dra. Maria Santos', email: 'maria@hospital.com', status: 'Ativo', progress: 85 },
        { id: 3, name: 'Dr. Pedro Costa', email: 'pedro@hospital.com', status: 'Pendente', progress: 45 },
        { id: 4, name: 'Dra. Ana Lima', email: 'ana@hospital.com', status: 'Inativo', progress: 20 }
      ]
    }
  },
  methods: {
    logout() {
      // Simular logout
      this.$router.push('/admin/login')
    }
  }
}
</script>