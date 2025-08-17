<template>
  <div class="space-y-6">
    <!-- Toasts -->
    <div class="fixed top-4 right-4 z-50 space-y-2">
      <div v-for="(t, i) in toasts" :key="i" class="flex items-center px-4 py-2 rounded shadow text-white"
           :class="t.type === 'success' ? 'bg-green-600' : 'bg-red-600'">
        <span class="mr-2">{{ t.type === 'success' ? '✔️' : '⚠️' }}</span>
        <span>{{ t.message }}</span>
      </div>
    </div>

    <!-- Configure Method Modal -->
    <div v-if="showConfigureMethod" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="cancelConfigureMethod" role="dialog" aria-modal="true" aria-labelledby="configureMethodTitle">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" tabindex="-1">
        <div class="mt-3">
          <h3 id="configureMethodTitle" class="text-lg font-medium text-gray-900 mb-4">Configurar Método</h3>
          <div v-if="configureTarget" class="space-y-4">
            <div>
              <label class="form-label">Tipo</label>
              <input class="form-input" :value="getMethodTypeLabel(configureTarget.type)" disabled />
            </div>
            <div>
              <label class="form-label">Nome</label>
              <input v-model="configureTarget.name" class="form-input" />
            </div>
            <div>
              <label class="form-label">Descrição</label>
              <textarea v-model="configureTarget.description" class="form-input" rows="3"></textarea>
            </div>
            <div class="flex items-center justify-between">
              <label class="text-sm text-gray-700">Ativo</label>
              <input v-model="configureTarget.enabled" type="checkbox" class="form-checkbox" />
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-6">
            <button type="button" @click="cancelConfigureMethod" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Cancelar</button>
            <button type="button" @click="applyConfigureMethod" class="btn-primary">Guardar</button>
          </div>
        </div>
      </div>
    </div>
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
            <tr v-if="loading.methods">
              <td colspan="6" class="px-6 py-6 text-sm text-gray-500">A carregar métodos…</td>
            </tr>
            <tr v-else-if="!filteredAuthMethods.length">
              <td colspan="6" class="px-6 py-6 text-sm text-gray-500">Sem métodos configurados.</td>
            </tr>
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
                <button
                  @click="toggleMethod(method)"
                  :disabled="loading.toggleMethod && loading.toggleMethod[method.id]"
                  :class="[
                    method.enabled ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900',
                    (loading.toggleMethod && loading.toggleMethod[method.id]) ? 'opacity-60 cursor-not-allowed' : ''
                  ]"
                >
                  {{ (loading.toggleMethod && loading.toggleMethod[method.id]) ? 'A atualizar…' : (method.enabled ? 'Desativar' : 'Ativar') }}
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
        <button @click="refreshSessions" class="btn-secondary text-sm" :disabled="loading.sessions">
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
                      <span class="text-xs font-medium text-gray-700">{{ (session.user && session.user.name) ? session.user.name.charAt(0) : '?' }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ (session.user && session.user.name) ? session.user.name : '—' }}</div>
                    <div class="text-sm text-gray-500">{{ (session.user && session.user.email) ? session.user.email : '' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.ipAddress }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.device }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.startTime }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ session.lastActivity }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button @click="requestForceLogout(session)" class="text-red-600 hover:text-red-900">
                  Forçar Logout
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Add Authentication Method Modal -->
    <div v-if="showAddAuthMethod" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showAddAuthMethod = false" role="dialog" aria-modal="true" aria-labelledby="addMethodTitle">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" tabindex="-1">
        <div class="mt-3">
          <h3 id="addMethodTitle" class="text-lg font-medium text-gray-900 mb-4">Adicionar Método de Autenticação</h3>
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
              <button type="submit" class="btn-primary" :disabled="loading.addMethod">
                {{ loading.addMethod ? 'A adicionar…' : 'Adicionar' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Security Settings Modal -->
    <div v-if="showSecuritySettings" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @click.self="closeSecurityModal"
         role="dialog" aria-modal="true" aria-labelledby="securitySettingsTitle">
      <div class="relative top-10 mx-auto p-5 border w-2/3 max-w-4xl shadow-lg rounded-md bg-white" tabindex="-1" ref="securityModal">
        <div class="mt-3">
          <h3 id="securitySettingsTitle" class="text-lg font-medium text-gray-900 mb-4">Configurações de Segurança</h3>
          
          <div v-if="loading.securitySettings" class="py-10 text-center text-sm text-gray-600">A carregar configurações...</div>
          <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Password Policy -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Política de Senhas</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Comprimento mínimo</label>
                  <div>
                    <input v-model.number="securitySettings.passwordPolicy.minLength" type="number" class="w-20 form-input text-sm" :disabled="loading.saveSettings || loading.securitySettings" />
                    <p v-if="formErrors.passwordPolicy && formErrors.passwordPolicy.minLength" class="text-xs text-red-600 mt-1">{{ formErrors.passwordPolicy.minLength }}</p>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Exigir maiúsculas</label>
                  <input v-model="securitySettings.passwordPolicy.requireUppercase" type="checkbox" class="form-checkbox" :disabled="loading.saveSettings || loading.securitySettings" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Exigir números</label>
                  <input v-model="securitySettings.passwordPolicy.requireNumbers" type="checkbox" class="form-checkbox" :disabled="loading.saveSettings || loading.securitySettings" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Exigir símbolos</label>
                  <input v-model="securitySettings.passwordPolicy.requireSymbols" type="checkbox" class="form-checkbox" :disabled="loading.saveSettings || loading.securitySettings" />
                </div>
              </div>
            </div>

            <!-- Session Management -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Gestão de Sessões</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Timeout de sessão (min)</label>
                  <div>
                    <input v-model.number="securitySettings.sessionManagement.timeout" type="number" class="w-20 form-input text-sm" :disabled="loading.saveSettings || loading.securitySettings" />
                    <p v-if="formErrors.sessionManagement && formErrors.sessionManagement.timeout" class="text-xs text-red-600 mt-1">{{ formErrors.sessionManagement.timeout }}</p>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Máx. sessões simultâneas</label>
                  <div>
                    <input v-model.number="securitySettings.sessionManagement.maxSessions" type="number" class="w-20 form-input text-sm" :disabled="loading.saveSettings || loading.securitySettings" />
                    <p v-if="formErrors.sessionManagement && formErrors.sessionManagement.maxSessions" class="text-xs text-red-600 mt-1">{{ formErrors.sessionManagement.maxSessions }}</p>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Logout automático</label>
                  <input v-model="securitySettings.sessionManagement.autoLogout" type="checkbox" class="form-checkbox" :disabled="loading.saveSettings || loading.securitySettings" />
                </div>
              </div>
            </div>

            <!-- MFA Settings -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Configurações MFA</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">MFA obrigatório</label>
                  <input v-model="securitySettings.mfa.required" type="checkbox" class="form-checkbox" :disabled="loading.saveSettings || loading.securitySettings" />
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Métodos permitidos</label>
                  <div>
                    <select v-model="securitySettings.mfa.allowedMethods" multiple class="form-input text-sm" :disabled="loading.saveSettings || loading.securitySettings">
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                    <option value="app">App Autenticador</option>
                    </select>
                    <p v-if="formErrors.mfa && formErrors.mfa.allowedMethods" class="text-xs text-red-600 mt-1">{{ formErrors.mfa.allowedMethods }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Account Lockout -->
            <div class="space-y-4">
              <h4 class="font-medium text-gray-900">Bloqueio de Conta</h4>
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Tentativas máximas</label>
                  <div>
                    <input v-model.number="securitySettings.accountLockout.maxAttempts" type="number" class="w-20 form-input text-sm" :disabled="loading.saveSettings || loading.securitySettings" />
                    <p v-if="formErrors.accountLockout && formErrors.accountLockout.maxAttempts" class="text-xs text-red-600 mt-1">{{ formErrors.accountLockout.maxAttempts }}</p>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <label class="text-sm text-gray-700">Duração bloqueio (min)</label>
                  <div>
                    <input v-model.number="securitySettings.accountLockout.lockoutDuration" type="number" class="w-20 form-input text-sm" :disabled="loading.saveSettings || loading.securitySettings" />
                    <p v-if="formErrors.accountLockout && formErrors.accountLockout.lockoutDuration" class="text-xs text-red-600 mt-1">{{ formErrors.accountLockout.lockoutDuration }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- LDAP / Active Directory -->
            <div class="space-y-4 md:col-span-2">
              <h4 class="font-medium text-gray-900">Configuração LDAP / Active Directory</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="form-label">Host</label>
                  <input v-model="directoryConfig.host" type="text" class="form-input" placeholder="ex: ldap.example.com" :disabled="loading.ldapSave || loading.ldapConfig" />
                </div>
                <div>
                  <label class="form-label">Base DN</label>
                  <input v-model="directoryConfig.base_dn" type="text" class="form-input" placeholder="ex: DC=example,DC=com" :disabled="loading.ldapSave || loading.ldapConfig" />
                </div>
                <div>
                  <label class="form-label">Bind DN (utilizador)</label>
                  <input v-model="directoryConfig.bind_dn" type="text" class="form-input" placeholder="ex: CN=svc_ldap,OU=Service,DC=example,DC=com" :disabled="loading.ldapSave || loading.ldapConfig" />
                </div>
                <div>
                  <label class="form-label">Password</label>
                  <input v-model="directoryConfig.bind_password" type="password" class="form-input" autocomplete="new-password" :disabled="loading.ldapSave || loading.ldapConfig" />
                  <p class="text-xs text-gray-500 mt-1">Nunca exibimos a password guardada. Preencha para atualizar.</p>
                </div>
                <div class="flex items-center space-x-6">
                  <label class="inline-flex items-center">
                    <input v-model="directoryConfig.use_ssl" type="checkbox" class="form-checkbox" :disabled="loading.ldapSave || loading.ldapConfig" />
                    <span class="ml-2 text-sm text-gray-700">Usar SSL</span>
                  </label>
                  <label class="inline-flex items-center">
                    <input v-model="directoryConfig.use_tls" type="checkbox" class="form-checkbox" :disabled="loading.ldapSave || loading.ldapConfig" />
                    <span class="ml-2 text-sm text-gray-700">Usar StartTLS</span>
                  </label>
                </div>
              </div>

              <div class="flex items-center space-x-3">
                <button type="button" @click="testLdapConnection" class="btn-secondary" :disabled="loading.ldapTest || loading.ldapConfig">
                  {{ loading.ldapTest ? 'A testar…' : 'Testar Conexão' }}
                </button>
                <button type="button" @click="saveDirectoryConfig" class="btn-primary" :disabled="loading.ldapSave || loading.ldapConfig">
                  {{ loading.ldapSave ? 'A guardar…' : 'Guardar LDAP' }}
                </button>
                <span v-if="ldapTest.ok !== null" :class="ldapTest.ok ? 'text-green-700' : 'text-red-700'" class="text-sm">
                  {{ ldapTest.message }}
                </span>
              </div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 mt-6">
            <button
              type="button"
              @click="closeSecurityModal"
              :class="[
                'px-4 py-2 rounded-md bg-gray-300 text-gray-700',
                (loading.saveSettings || loading.securitySettings)
                  ? 'opacity-50 cursor-not-allowed pointer-events-none'
                  : 'hover:bg-gray-400'
              ]"
              :disabled="loading.saveSettings || loading.securitySettings"
              :aria-disabled="(loading.saveSettings || loading.securitySettings) ? 'true' : 'false'"
              :title="(loading.saveSettings || loading.securitySettings) ? 'Aguarde…' : 'Cancelar'"
            >
              Cancelar
            </button>
            <button
              type="button"
              @click="saveSecuritySettings"
              :class="[
                'btn-primary',
                (loading.saveSettings || loading.securitySettings) ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
              ]"
              :disabled="loading.saveSettings || loading.securitySettings"
              :aria-disabled="(loading.saveSettings || loading.securitySettings) ? 'true' : 'false'"
              :title="loading.saveSettings ? 'A guardar…' : ((loading.securitySettings) ? 'Aguarde…' : 'Guardar Configurações')"
            >
              {{ loading.saveSettings ? 'A guardar...' : 'Guardar Configurações' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Force Logout Modal -->
    <div v-if="showForceLogoutModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-32 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-2">Confirmar Logout</h3>
          <p class="text-sm text-gray-600 mb-4">
            Tem a certeza que pretende forçar o logout de
            <strong>{{ (forceLogoutTarget && forceLogoutTarget.user && forceLogoutTarget.user.name) ? forceLogoutTarget.user.name : '' }}</strong>?
          </p>
          <div class="flex justify-end space-x-3">
            <button @click="cancelForceLogout" class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">Cancelar</button>
            <button @click="confirmForceLogout" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700" :disabled="loading.forceLogout">
              {{ loading.forceLogout ? 'A terminar...' : 'Confirmar' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { getAuthStats, getAuthMethods, createAuthMethod, toggleAuthMethod, updateAuthMethod,
         getSessions, forceLogoutSession, getSecuritySettings, saveSecuritySettings } from '@/composables/useAuth'
import { getDirectoryConfig, updateDirectoryConfig, testDirectoryConnection } from '@/composables/useDirectory'
export default {
  name: 'UserAuthentication',
  data() {
    return {
      showAddAuthMethod: false,
      showSecuritySettings: false,
      showForceLogoutModal: false,
      forceLogoutTarget: null,
      methodFilter: '',
      newAuthMethod: {
        type: '',
        name: '',
        description: ''
      },
    async fetchDirectoryConfig() {
      this.loading.ldapConfig = true
      try {
        const cfg = await getDirectoryConfig()
        this.directoryConfig = {
          host: cfg.host || '',
          base_dn: cfg.base_dn || '',
          bind_dn: cfg.bind_dn || '',
          bind_password: '', // never prefill secrets from backend response
          use_ssl: Boolean(cfg.use_ssl),
          use_tls: Boolean(cfg.use_tls),
        }
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao carregar configuração LDAP')
      } finally {
        this.loading.ldapConfig = false
      }
    },
    async saveDirectoryConfig() {
      this.loading.ldapSave = true
      try {
        const payload = { ...this.directoryConfig }
        if (!payload.bind_password) delete payload.bind_password
        await updateDirectoryConfig(payload)
        this.showSuccess('Configuração LDAP guardada')
        // Clear password field after save
        this.directoryConfig.bind_password = ''
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao guardar configuração LDAP')
      } finally {
        this.loading.ldapSave = false
      }
    },
    async testLdapConnection() {
      this.loading.ldapTest = true
      this.ldapTest = { ok: null, message: '' }
      try {
        const payload = { ...this.directoryConfig }
        if (!payload.bind_password) delete payload.bind_password
        const res = await testDirectoryConnection(payload)
        this.ldapTest = { ok: !!res.ok, message: res.message || (res.ok ? 'Conexão bem-sucedida' : 'Falha na conexão') }
        if (this.ldapTest.ok) this.showSuccess(this.ldapTest.message)
        else this.showError(this.ldapTest.message)
      } catch (e) {
        const msg = e?.response?.data?.message || 'Falha no teste de conexão LDAP'
        this.ldapTest = { ok: false, message: msg }
        this.showError(msg)
      } finally {
        this.loading.ldapTest = false
      }
    },
      loading: {
        stats: false,
        methods: false,
        sessions: false,
        saveSettings: false,
        securitySettings: false,
        addMethod: false,
        toggleMethod: {},
        forceLogout: false,
        updateMethod: false,
        ldapConfig: false,
        ldapSave: false,
        ldapTest: false,
      },
      toasts: [],
      authStats: {
        traditional: 0,
        mfa: 0,
        sso: 0
      },
      authMethods: [],
      // Configure Method Modal state
      showConfigureMethod: false,
      configureTarget: null,
      activeSessions: [],
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
      },
      // LDAP/AD Directory Config
      directoryConfig: {
        host: '',
        base_dn: '',
        bind_dn: '',
        bind_password: '',
        use_ssl: false,
        use_tls: false,
      },
      ldapTest: {
        ok: null,
        message: '',
      },
      formErrors: {
        passwordPolicy: {},
        sessionManagement: {},
        mfa: {},
        accountLockout: {}
      },
      securitySettingsOriginal: null,
    }
  },
  mounted() {
    this.bootstrap()
  },
  beforeUnmount() {
    document.removeEventListener('keydown', this.onEsc)
  },
  computed: {
    filteredAuthMethods() {
      if (!this.methodFilter) return this.authMethods
      return this.authMethods.filter(method => method.type === this.methodFilter)
    }
  },
  watch: {
    showSecuritySettings(val) {
      if (val) {
        // snapshot for dirty check and prepare UI
        this.securitySettingsOriginal = JSON.parse(JSON.stringify(this.securitySettings))
        this.formErrors = { passwordPolicy: {}, sessionManagement: {}, mfa: {}, accountLockout: {} }
        this.$nextTick(() => {
          document.addEventListener('keydown', this.onEsc)
          if (this.$refs.securityModal) {
            this.$refs.securityModal.focus()
          }
        })
      } else {
        document.removeEventListener('keydown', this.onEsc)
      }
    }
  },
  methods: {
    async bootstrap() {
      await Promise.all([
        this.fetchStats(),
        this.fetchMethods(),
        this.refreshSessions(),
        this.fetchSecuritySettings(),
        this.fetchDirectoryConfig(),
      ])
    },
    // Toast helpers
    showSuccess(msg) { this.pushToast(msg, 'success') },
    showError(msg) { this.pushToast(msg, 'error') },
    pushToast(message, type) {
      const t = { message, type }
      this.toasts.push(t)
      setTimeout(() => {
        const idx = this.toasts.indexOf(t)
        if (idx !== -1) this.toasts.splice(idx, 1)
      }, 3500)
    },

    async fetchStats() {
      this.loading.stats = true
      try {
        const stats = await getAuthStats()
        // Map keys if needed
        this.authStats = {
          traditional: stats.traditional ?? stats.password ?? 0,
          mfa: stats.mfa ?? stats.two_factor ?? 0,
          sso: stats.sso ?? stats.social ?? 0,
        }
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao carregar estatísticas')
      } finally {
        this.loading.stats = false
      }
    },
    async fetchMethods() {
      this.loading.methods = true
      try {
        const methods = await getAuthMethods()
        // Expect an array of {id,name,type,description,enabled,userCount,lastUpdated}
        const arr = Array.isArray(methods) ? methods : []
        // normalize
        this.authMethods = arr.map((m, idx) => ({
          id: m?.id ?? `m-${idx}`,
          name: (m?.name ?? '').toString(),
          type: ['password','mfa','sso','social'].includes(m?.type) ? m.type : 'password',
          description: (m?.description ?? '').toString(),
          enabled: Boolean(m?.enabled),
          userCount: Number.isFinite(Number(m?.userCount)) ? Number(m.userCount) : 0,
          lastUpdated: (m?.lastUpdated ?? '').toString()
        }))
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao carregar métodos')
      } finally {
        this.loading.methods = false
      }
    },
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
    async addAuthMethod() {
      // validação
      if (!this.newAuthMethod.type || !this.newAuthMethod.name?.trim()) {
        return this.showError('Tipo e Nome são obrigatórios')
      }
      this.loading.addMethod = true
      try {
        await createAuthMethod(this.newAuthMethod)
        this.showSuccess('Método criado com sucesso')
        this.showAddAuthMethod = false
        this.newAuthMethod = { type: '', name: '', description: '' }
        await this.fetchMethods()
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao criar método')
      } finally {
        this.loading.addMethod = false
      }
    },
    configureMethod(method) {
      console.log('Configurar método:', method)
      // Implementar configuração específica do método
    },
    async toggleMethod(method) {
      const targetState = !method.enabled
      this.$set ? this.$set(this.loading.toggleMethod, method.id, true) : (this.loading.toggleMethod[method.id] = true)
      try {
        await toggleAuthMethod(method.id, targetState)
        method.enabled = targetState
        method.lastUpdated = new Date().toLocaleDateString('pt-PT')
        this.showSuccess(`Método ${targetState ? 'ativado' : 'desativado'}`)
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao atualizar método')
      } finally {
        this.$set ? this.$set(this.loading.toggleMethod, method.id, false) : (this.loading.toggleMethod[method.id] = false)
      }
    },
    async refreshSessions() {
      this.loading.sessions = true
      try {
        const sessions = await getSessions()
        const arr = Array.isArray(sessions) ? sessions : []
        this.activeSessions = arr.map((s, idx) => ({
          id: s?.id ?? `s-${idx}`,
          user: (s && s.user && (typeof s.user === 'object')) ? s.user : null,
          ipAddress: (s?.ipAddress ?? '').toString(),
          device: (s?.device ?? '').toString(),
          startTime: (s?.startTime ?? '').toString(),
          lastActivity: (s?.lastActivity ?? '').toString()
        }))
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao carregar sessões')
      } finally {
        this.loading.sessions = false
      }
    },
    configureMethod(method) {
      this.configureTarget = JSON.parse(JSON.stringify(method))
      this.showConfigureMethod = true
    },
    async applyConfigureMethod() {
      if (!this.configureTarget) return
      const payload = {
        name: this.configureTarget.name,
        description: this.configureTarget.description,
        enabled: this.configureTarget.enabled,
      }
      this.loading.updateMethod = true
      try {
        const updated = await updateAuthMethod(this.configureTarget.id, payload)
        const idx = this.authMethods.findIndex(m => m.id === this.configureTarget.id)
        if (idx !== -1) {
          const merged = { ...this.authMethods[idx], ...updated }
          this.$set ? this.$set(this.authMethods, idx, merged) : (this.authMethods[idx] = merged)
        }
        this.showSuccess('Método atualizado com sucesso')
        this.showConfigureMethod = false
        this.configureTarget = null
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao atualizar método')
      } finally {
        this.loading.updateMethod = false
      }
    },
    cancelConfigureMethod() {
      this.showConfigureMethod = false
      this.configureTarget = null
    },
    requestForceLogout(session) {
      this.forceLogoutTarget = session
      this.showForceLogoutModal = true
    },
    cancelForceLogout() {
      this.forceLogoutTarget = null
      this.showForceLogoutModal = false
    },
    async confirmForceLogout() {
      if (!this.forceLogoutTarget?.id) return this.cancelForceLogout()
      this.loading.forceLogout = true
      try {
        await forceLogoutSession(this.forceLogoutTarget.id)
        this.activeSessions = this.activeSessions.filter(s => s.id !== this.forceLogoutTarget.id)
        this.showSuccess('Sessão terminada com sucesso')
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao terminar sessão')
      } finally {
        this.loading.forceLogout = false
        this.cancelForceLogout()
      }
    },
    async fetchSecuritySettings() {
      this.loading.securitySettings = true
      try {
        const s = await getSecuritySettings()
        // Normalize and merge with defaults to avoid undefined bindings in template
        const defaults = {
          passwordPolicy: { minLength: 8, requireUppercase: true, requireNumbers: true, requireSymbols: true },
          sessionManagement: { timeout: 30, maxSessions: 3, autoLogout: true },
          mfa: { required: false, allowedMethods: [] },
          accountLockout: { maxAttempts: 5, lockoutDuration: 15 },
        }
        const incoming = (s && typeof s === 'object') ? s : {}
        const normalized = {
          passwordPolicy: { ...defaults.passwordPolicy, ...(incoming.passwordPolicy || {}) },
          sessionManagement: { ...defaults.sessionManagement, ...(incoming.sessionManagement || {}) },
          mfa: { ...defaults.mfa, ...(incoming.mfa || {}) },
          accountLockout: { ...defaults.accountLockout, ...(incoming.accountLockout || {}) },
        }
        // Ensure allowedMethods is always an array for <select multiple v-model>
        if (!Array.isArray(normalized.mfa.allowedMethods)) {
          normalized.mfa.allowedMethods = []
        }
        this.securitySettings = normalized
      } catch (e) {
        this.showError(e?.response?.data?.message || 'Falha ao carregar configurações de segurança')
      } finally {
        this.loading.securitySettings = false
      }
    },
    onEsc(e) {
      if (e.key === 'Escape') this.closeSecurityModal()
    },
    closeSecurityModal() {
      if (this.loading.saveSettings || this.loading.securitySettings) return
      const dirty = this.securitySettingsOriginal && JSON.stringify(this.securitySettings) !== JSON.stringify(this.securitySettingsOriginal)
      if (dirty) {
        this.showError('Existem alterações por guardar.')
        return
      }
      this.showSecuritySettings = false
    },
    async saveSecuritySettings() {
      // validações simples
      const p = this.securitySettings.passwordPolicy
      const sm = this.securitySettings.sessionManagement
      const al = this.securitySettings.accountLockout
      if ((p?.minLength ?? 0) < 6) return this.showError('Comprimento mínimo de senha deve ser ≥ 6')
      if ((sm?.timeout ?? 0) < 5) return this.showError('Timeout de sessão deve ser ≥ 5')
      if ((sm?.maxSessions ?? 0) < 1) return this.showError('Máx. sessões deve ser ≥ 1')
      if ((al?.maxAttempts ?? 0) < 1) return this.showError('Tentativas máximas deve ser ≥ 1')

      this.loading.saveSettings = true
      try {
        this.formErrors = { passwordPolicy: {}, sessionManagement: {}, mfa: {}, accountLockout: {} }
        await saveSecuritySettings(this.securitySettings)
        this.showSuccess('Configurações salvas')
        this.showSecuritySettings = false
        this.securitySettingsOriginal = JSON.parse(JSON.stringify(this.securitySettings))
      } catch (e) {
        const resp = e?.response?.data
        if (resp?.errors && typeof resp.errors === 'object') {
          // Mapear erros de validação por campo, se vierem estruturados
          this.formErrors = {
            passwordPolicy: resp.errors.passwordPolicy || {},
            sessionManagement: resp.errors.sessionManagement || {},
            mfa: resp.errors.mfa || {},
            accountLockout: resp.errors.accountLockout || {}
          }
        }
        this.showError(resp?.message || 'Falha ao salvar configurações')
      } finally {
        this.loading.saveSettings = false
      }
    }
  }
}
</script>