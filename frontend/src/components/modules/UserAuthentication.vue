<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <h3 class="text-lg font-semibold text-gray-900">Autenticação e Acesso</h3>
      <div class="flex space-x-3">
        <button @click="showSecuritySettings = true" class="btn-secondary">
          Configurações de Segurança
        </button>
        <button @click="showAddAuthMethod = true" class="btn-primary">
          + Novo Método de Autenticação
        </button>
      </div>
    </div>

    <!-- Authentication Methods Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-sm font-medium text-gray-900">Login Tradicional</h4>
            <p class="text-2xl font-bold text-blue-600">{{ authStats.traditional }}</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Utilizadores com login padrão</p>
      </div>

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-sm font-medium text-gray-900">MFA Ativo</h4>
            <p class="text-2xl font-bold text-green-600">{{ authStats.mfa }}</p>
          </div>
          <div class="p-3 bg-green-100 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Utilizadores com 2FA</p>
      </div>

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <h4 class="text-sm font-medium text-gray-900">SSO/Social</h4>
            <p class="text-2xl font-bold text-purple-600">{{ authStats.sso }}</p>
          </div>
          <div class="p-3 bg-purple-100 rounded-full">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
            </svg>
          </div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Login via SSO/Social</p>
      </div>
    </div>

    <!-- Authentication Methods Table -->
    <div class="card">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-md font-semibold text-gray-900">Métodos de Autenticação Configurados</h4>
        <div class="flex space-x-2">
          <select v-model="methodFilter" class="form-input text-sm">
            <option value="">Todos os Métodos</option>
            <option value="password">Senha</option>
            <option value="mfa">MFA/2FA</option>
            <option value="sso">SSO</option>
            <option value="social">Social</option>
          </select>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizadores</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atualização</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="method in filteredAuthMethods" :key="method.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full flex items-center justify-center" :class="getMethodIconClass(method.type)">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path v-if="method.type === 'password'" d="M10 2L3 7v3c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-7-5z"/>
                        <path v-else-if="method.type === 'mfa'" d="M10 12a2 2 0 100-4 2 2 0 000 4z M10 2C5.58 2 2 5.58 2 10s3.58 8 8 8 8-3.58 8-8-3.58-8-8-8z"/>
                        <path v-else-if="method.type === 'sso'" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        <path v-else d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2z"/>
                      </svg>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ method.name }}</div>
                    <div class="text-sm text-gray-500">{{ method.description }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getMethodTypeClass(method.type)">
                  {{ getMethodTypeLabel(method.type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="method.enabled ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                  {{ method.enabled ? 'Ativo' : 'Inativo' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ method.userCount }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ method.lastUpdated }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="configureMethod(method)" class="text-blue-600 hover:text-blue-900 mr-3">Configurar</button>
                <button @click="toggleMethod(method)" :class="method.enabled ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'">
                  {{ method.enabled ? 'Desativar' : 'Ativar' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Active Sessions Management -->
    <div class="card">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-md font-semibold text-gray-900">Sessões Ativas</h4>
        <button @click="refreshSessions" class="btn-secondary text-sm">
          Atualizar
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizador</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dispositivo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Início da Sessão</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atividade</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="session in activeSessions" :key="session.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center">
                      <span class="text-xs font-medium text-gray-700">{{ session.user.name.charAt(0) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ session.user.name }}</div>
                    <div class="text-sm text-gray-500">{{ session.user.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.ipAddress }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.device }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.startTime }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.lastActivity }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="forceLogout(session)" class="text-red-600 hover:text-red-900">
                  Forçar Logout
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Authentication Method Modal -->
    <div v-if="showAddAuthMethod" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Adicionar Método de Autenticação</h3>
          <form @submit.prevent="addAuthMethod">
            <div class="mb-4">
              <label class="form-label">Tipo de Autenticação</label>
              <select v-model="newAuthMethod.type" class="form-input" required>
                <option value="">Selecione um tipo</option>
                <option value="password">Login com Senha</option>
                <option value="mfa">Autenticação Multifator (MFA)</option>
                <option value="sso">Single Sign-On (SSO)</option>
                <option value="social">Login Social</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="form-label">Nome do Método</label>
              <input v-model="newAuthMethod.name" type="text" class="form-input" required />
            </div>
            <div class="mb-4">
              <label class="form-label">Descrição</label>
              <textarea v-model="newAuthMethod.description" class="form-input" rows="3"></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showAddAuthMethod = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
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

    <!-- Security Settings Modal -->
    <div v-if="showSecuritySettings" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-2/3 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações de Segurança</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Password Policy -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Política de Senhas</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Comprimento mínimo</label>
                  <input v-model="securitySettings.passwordPolicy.minLength" type="number" class="w-20 form-input text-sm" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Exigir maiúsculas</label>
                  <input v-model="securitySettings.passwordPolicy.requireUppercase" type="checkbox" class="form-checkbox" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Exigir números</label>
                  <input v-model="securitySettings.passwordPolicy.requireNumbers" type="checkbox" class="form-checkbox" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Exigir símbolos</label>
                  <input v-model="securitySettings.passwordPolicy.requireSymbols" type="checkbox" class="form-checkbox" />
                </div>
              </div>
            </div>

            <!-- Session Management -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Gestão de Sessões</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Timeout de sessão (min)</label>
                  <input v-model="securitySettings.sessionManagement.timeout" type="number" class="w-20 form-input text-sm" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Máx. sessões simultâneas</label>
                  <input v-model="securitySettings.sessionManagement.maxSessions" type="number" class="w-20 form-input text-sm" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Logout automático</label>
                  <input v-model="securitySettings.sessionManagement.autoLogout" type="checkbox" class="form-checkbox" />
                </div>
              </div>
            </div>

            <!-- MFA Settings -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Configurações MFA</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">MFA obrigatório</label>
                  <input v-model="securitySettings.mfa.required" type="checkbox" class="form-checkbox" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Métodos permitidos</label>
                  <select v-model="securitySettings.mfa.allowedMethods" multiple class="form-input text-sm">
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                    <option value="app">App Autenticador</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Account Lockout -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Bloqueio de Conta</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Tentativas máximas</label>
                  <input v-model="securitySettings.accountLockout.maxAttempts" type="number" class="w-20 form-input text-sm" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Duração bloqueio (min)</label>
                  <input v-model="securitySettings.accountLockout.lockoutDuration" type="number" class="w-20 form-input text-sm" />
                </div>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" @click="showSecuritySettings = false" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
              Cancelar
            </button>
            <button @click="saveSecuritySettings" class="btn-primary">
              Guardar Configurações
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserAuthentication',
  data() {
    return {
      showAddAuthMethod: false,
      showSecuritySettings: false,
      methodFilter: '',
      newAuthMethod: {
        type: '',
        name: '',
        description: ''
      },
      authStats: {
        traditional: 156,
        mfa: 89,
        sso: 34
      },
      authMethods: [
        {
          id: 1,
          name: 'Login Email/Senha',
          type: 'password',
          description: 'Autenticação tradicional com email e senha',
          enabled: true,
          userCount: 156,
          lastUpdated: '15/01/2024'
        },
        {
          id: 2,
          name: 'Autenticação por SMS',
          type: 'mfa',
          description: 'Código de verificação via SMS',
          enabled: true,
          userCount: 89,
          lastUpdated: '14/01/2024'
        },
        {
          id: 3,
          name: 'Google Authenticator',
          type: 'mfa',
          description: 'App autenticador TOTP',
          enabled: true,
          userCount: 67,
          lastUpdated: '13/01/2024'
        },
        {
          id: 4,
          name: 'Active Directory',
          type: 'sso',
          description: 'Integração com AD corporativo',
          enabled: true,
          userCount: 34,
          lastUpdated: '12/01/2024'
        },
        {
          id: 5,
          name: 'Login Google',
          type: 'social',
          description: 'Autenticação via Google OAuth2',
          enabled: false,
          userCount: 0,
          lastUpdated: '10/01/2024'
        }
      ],
      activeSessions: [
        {
          id: 1,
          user: { name: 'Dr. João Silva', email: 'joao@hospital.com' },
          ipAddress: '192.168.1.100',
          device: 'Chrome/Windows',
          startTime: '09:30',
          lastActivity: '14:25'
        },
        {
          id: 2,
          user: { name: 'Dra. Maria Santos', email: 'maria@hospital.com' },
          ipAddress: '192.168.1.101',
          device: 'Safari/macOS',
          startTime: '08:15',
          lastActivity: '14:20'
        },
        {
          id: 3,
          user: { name: 'Carlos Oliveira', email: 'carlos@hospital.com' },
          ipAddress: '192.168.1.102',
          device: 'Firefox/Linux',
          startTime: '10:45',
          lastActivity: '14:18'
        }
      ],
      securitySettings: {
        passwordPolicy: {
          minLength: 8,
          requireUppercase: true,
          requireNumbers: true,
          requireSymbols: true
        },
        sessionManagement: {
          timeout: 30,
          maxSessions: 3,
          autoLogout: true
        },
        mfa: {
          required: false,
          allowedMethods: ['sms', 'app']
        },
        accountLockout: {
          maxAttempts: 5,
          lockoutDuration: 15
        }
      }
    }
  },
  computed: {
    filteredAuthMethods() {
      if (!this.methodFilter) return this.authMethods
      return this.authMethods.filter(method => method.type === this.methodFilter)
    }
  },
  methods: {
    getMethodIconClass(type) {
      const classes = {
        password: 'bg-blue-100 text-blue-600',
        mfa: 'bg-green-100 text-green-600',
        sso: 'bg-purple-100 text-purple-600',
        social: 'bg-orange-100 text-orange-600'
      }
      return classes[type] || 'bg-gray-100 text-gray-600'
    },
    getMethodTypeClass(type) {
      const classes = {
        password: 'bg-blue-100 text-blue-800',
        mfa: 'bg-green-100 text-green-800',
        sso: 'bg-purple-100 text-purple-800',
        social: 'bg-orange-100 text-orange-800'
      }
      return classes[type] || 'bg-gray-100 text-gray-800'
    },
    getMethodTypeLabel(type) {
      const labels = {
        password: 'Senha',
        mfa: 'MFA',
        sso: 'SSO',
        social: 'Social'
      }
      return labels[type] || 'Outro'
    },
    addAuthMethod() {
      const newId = Math.max(...this.authMethods.map(m => m.id)) + 1
      this.authMethods.push({
        id: newId,
        ...this.newAuthMethod,
        enabled: false,
        userCount: 0,
        lastUpdated: new Date().toLocaleDateString('pt-PT')
      })
      this.newAuthMethod = { type: '', name: '', description: '' }
      this.showAddAuthMethod = false
    },
    configureMethod(method) {
      console.log('Configurar método:', method)
      // Implementar configuração específica do método
    },
    toggleMethod(method) {
      method.enabled = !method.enabled
      method.lastUpdated = new Date().toLocaleDateString('pt-PT')
    },
    refreshSessions() {
      console.log('Atualizando sessões ativas...')
      // Implementar refresh das sessões
    },
    forceLogout(session) {
      if (confirm(`Forçar logout de ${session.user.name}?`)) {
        this.activeSessions = this.activeSessions.filter(s => s.id !== session.id)
        console.log('Logout forçado:', session)
      }
    },
    saveSecuritySettings() {
      console.log('Configurações de segurança salvas:', this.securitySettings)
      this.showSecuritySettings = false
      // Implementar salvamento das configurações
    }
  }
}
</script>