<template>
  <div class="space-y-8">
    <!-- System Configuration -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Configurações do Sistema</h3>
      
      <form @submit.prevent="saveSystemSettings">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="form-label">Nome da Instituição</label>
            <input v-model="systemSettings.institutionName" type="text" class="form-input" required />
          </div>
          
          <div>
            <label class="form-label">E-mail de Notificações</label>
            <input v-model="systemSettings.notificationEmail" type="email" class="form-input" required />
          </div>
          
          <div>
            <label class="form-label">Tempo Limite de Integração (dias)</label>
            <input v-model="systemSettings.onboardingTimeLimit" type="number" min="1" max="365" class="form-input" required />
          </div>
          
          <div>
            <label class="form-label">Fuso Horário</label>
            <select v-model="systemSettings.timezone" class="form-input">
              <option value="Africa/Luanda">Luanda (UTC+1)</option>
              <option value="Europe/Lisbon">Lisboa (UTC+0/+1)</option>
              <option value="America/Sao_Paulo">Brasília (UTC-3)</option>
            </select>
          </div>
          
          <div>
            <label class="form-label">Idioma do Sistema</label>
            <select v-model="systemSettings.language" class="form-input">
              <option value="pt-PT">Português (Portugal)</option>
              <option value="en-US">English (US)</option>
              <option value="es-ES">Español</option>
            </select>
          </div>
          
          <div>
            <label class="form-label">Máximo de Tentativas de Início de Sessão</label>
            <input v-model="systemSettings.maxLoginAttempts" type="number" min="3" max="10" class="form-input" required />
          </div>
        </div>
        
        <div class="mt-6 space-y-4">
          <div class="flex items-center">
            <input v-model="systemSettings.emailNotifications" type="checkbox" id="emailNotifications" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
            <label for="emailNotifications" class="ml-2 block text-sm text-gray-900">
              Enviar notificações por e-mail
            </label>
          </div>
          
          <div class="flex items-center">
            <input v-model="systemSettings.autoApproval" type="checkbox" id="autoApproval" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
            <label for="autoApproval" class="ml-2 block text-sm text-gray-900">
              Aprovação automática de documentos básicos
            </label>
          </div>
          
          <div class="flex items-center">
            <input v-model="systemSettings.maintenanceMode" type="checkbox" id="maintenanceMode" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
            <label for="maintenanceMode" class="ml-2 block text-sm text-gray-900">
              Modo de manutenção (bloqueia acesso de usuários)
            </label>
          </div>
        </div>
        
        <div class="pt-6">
          <button type="submit" class="btn-primary mr-3">Salvar Configurações</button>
          <button type="button" @click="resetSystemSettings" class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
            Restaurar Padrões
          </button>
        </div>
      </form>
    </div>

    <!-- Email Templates -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Modelos de Email</h3>
      
      <div class="space-y-6">
        <div>
          <label class="form-label">E-mail de Boas-vindas</label>
        <textarea v-model="emailTemplates.welcome" rows="4" class="form-input" placeholder="Modelo do e-mail de boas-vindas..."></textarea>
        </div>
        
        <div>
          <label class="form-label">E-mail de Lembrete</label>
        <textarea v-model="emailTemplates.reminder" rows="4" class="form-input" placeholder="Modelo do e-mail de lembrete..."></textarea>
        </div>
        
        <div>
          <label class="form-label">E-mail de Conclusão</label>
        <textarea v-model="emailTemplates.completion" rows="4" class="form-input" placeholder="Modelo do e-mail de conclusão..."></textarea>
        </div>
        
        <button @click="saveEmailTemplates" class="btn-primary">
          Salvar Modelos
        </button>
      </div>
    </div>

    <!-- User Roles & Permissions -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Funções e Permissões</h3>
      
      <div class="space-y-6">
        <div v-for="role in userRoles" :key="role.id" class="border border-gray-200 rounded-lg p-4">
          <div class="flex justify-between items-center mb-4">
            <h4 class="text-md font-medium text-gray-900">{{ role.name }}</h4>
            <button @click="editRole(role)" class="text-primary-600 hover:text-primary-900 text-sm">
              Editar
            </button>
          </div>
          
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="permission in role.permissions" :key="permission.id" class="flex items-center">
              <input 
                v-model="permission.enabled" 
                type="checkbox" 
                :id="`${role.id}-${permission.id}`" 
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" 
              />
              <label :for="`${role.id}-${permission.id}`" class="ml-2 block text-sm text-gray-700">
                {{ permission.name }}
              </label>
            </div>
          </div>
        </div>
        
        <button @click="saveRolePermissions" class="btn-primary">
          Salvar Permissões
        </button>
      </div>
    </div>

    <!-- Security Settings -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Configurações de Segurança</h3>
      
      <form @submit.prevent="saveSecuritySettings">
        <div class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="form-label">Tempo de Sessão (minutos)</label>
              <input v-model="securitySettings.sessionTimeout" type="number" min="15" max="480" class="form-input" required />
            </div>
            
            <div>
              <label class="form-label">Complexidade de Senha</label>
              <select v-model="securitySettings.passwordComplexity" class="form-input">
                <option value="low">Baixa (mínimo 6 caracteres)</option>
                <option value="medium">Média (8 caracteres, letras e números)</option>
                <option value="high">Alta (12 caracteres, letras, números e símbolos)</option>
              </select>
            </div>
          </div>
          
          <div class="space-y-4">
            <div class="flex items-center">
              <input v-model="securitySettings.twoFactorAuth" type="checkbox" id="twoFactorAuth" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
              <label for="twoFactorAuth" class="ml-2 block text-sm text-gray-900">
                Autenticação de dois fatores obrigatória
              </label>
            </div>
            
            <div class="flex items-center">
              <input v-model="securitySettings.ipWhitelist" type="checkbox" id="ipWhitelist" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
              <label for="ipWhitelist" class="ml-2 block text-sm text-gray-900">
                Restringir acesso por IP
              </label>
            </div>
            
            <div class="flex items-center">
              <input v-model="securitySettings.auditLog" type="checkbox" id="auditLog" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" />
              <label for="auditLog" class="ml-2 block text-sm text-gray-900">
                Log de auditoria detalhado
              </label>
            </div>
          </div>
          
          <button type="submit" class="btn-primary">
            Salvar Configurações de Segurança
          </button>
        </div>
      </form>
    </div>

    <!-- System Backup -->
    <div class="card">
      <h3 class="text-lg font-semibold text-gray-900 mb-6">Backup do Sistema</h3>
      
      <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="form-label">Frequência de Backup</label>
            <select v-model="backupSettings.frequency" class="form-input">
              <option value="daily">Diário</option>
              <option value="weekly">Semanal</option>
              <option value="monthly">Mensal</option>
            </select>
          </div>
          
          <div>
            <label class="form-label">Horário do Backup</label>
            <input v-model="backupSettings.time" type="time" class="form-input" />
          </div>
        </div>
        
        <div class="flex space-x-4">
          <button @click="createBackup" class="btn-primary">
            Criar Backup Agora
          </button>
          <button @click="restoreBackup" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
            Restaurar Backup
          </button>
        </div>
        
        <div class="mt-4">
          <h4 class="text-sm font-medium text-gray-900 mb-2">Últimos Backups</h4>
          <div class="space-y-2">
            <div v-for="backup in recentBackups" :key="backup.id" class="flex justify-between items-center p-2 bg-gray-50 rounded">
              <span class="text-sm text-gray-700">{{ backup.date }} - {{ backup.size }}</span>
              <button @click="downloadBackup(backup)" class="text-primary-600 hover:text-primary-900 text-sm">
                Download
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminSettings',
  data() {
    return {
      systemSettings: {
        institutionName: 'Hospital Central',
        notificationEmail: 'admin@hospital.com',
        onboardingTimeLimit: 30,
        timezone: 'Africa/Luanda',
        language: 'pt-PT',
        maxLoginAttempts: 5,
        emailNotifications: true,
        autoApproval: false,
        maintenanceMode: false
      },
      emailTemplates: {
        welcome: 'Bem-vindo ao sistema OnHCP! O seu processo de integração foi iniciado.',
        reminder: 'Lembrete: Possui pendências no seu processo de integração.',
        completion: 'Parabéns! Concluiu com sucesso o processo de integração.'
      },
      userRoles: [
        {
          id: 'admin',
          name: 'Administrador',
          permissions: [
            { id: 'users_create', name: 'Criar Utilizadores', enabled: true },
        { id: 'users_edit', name: 'Editar Utilizadores', enabled: true },
        { id: 'users_delete', name: 'Eliminar Utilizadores', enabled: true },
            { id: 'reports_view', name: 'Ver Relatórios', enabled: true },
            { id: 'settings_edit', name: 'Editar Configurações', enabled: true },
            { id: 'backup_create', name: 'Criar Backups', enabled: true }
          ]
        },
        {
          id: 'manager',
          name: 'Gerente',
          permissions: [
            { id: 'users_create', name: 'Criar Usuários', enabled: true },
            { id: 'users_edit', name: 'Editar Usuários', enabled: true },
            { id: 'users_delete', name: 'Excluir Usuários', enabled: false },
            { id: 'reports_view', name: 'Ver Relatórios', enabled: true },
            { id: 'settings_edit', name: 'Editar Configurações', enabled: false },
            { id: 'backup_create', name: 'Criar Backups', enabled: false }
          ]
        },
        {
          id: 'user',
          name: 'Usuário',
          permissions: [
            { id: 'users_create', name: 'Criar Usuários', enabled: false },
            { id: 'users_edit', name: 'Editar Usuários', enabled: false },
            { id: 'users_delete', name: 'Excluir Usuários', enabled: false },
            { id: 'reports_view', name: 'Ver Relatórios', enabled: false },
            { id: 'settings_edit', name: 'Editar Configurações', enabled: false },
            { id: 'backup_create', name: 'Criar Backups', enabled: false }
          ]
        }
      ],
      securitySettings: {
        sessionTimeout: 120,
        passwordComplexity: 'medium',
        twoFactorAuth: false,
        ipWhitelist: false,
        auditLog: true
      },
      backupSettings: {
        frequency: 'daily',
        time: '02:00'
      },
      recentBackups: [
        { id: 1, date: '2024-01-20 02:00', size: '45.2 MB' },
        { id: 2, date: '2024-01-19 02:00', size: '44.8 MB' },
        { id: 3, date: '2024-01-18 02:00', size: '44.1 MB' }
      ]
    }
  },
  methods: {
    saveSystemSettings() {
      console.log('Salvando configurações do sistema:', this.systemSettings)
      alert('Configurações do sistema salvas com sucesso!')
    },
    resetSystemSettings() {
      if (confirm('Tem certeza que deseja restaurar as configurações padrão?')) {
        // Restaurar valores padrão
        this.systemSettings = {
          institutionName: 'Hospital Central',
          notificationEmail: 'admin@hospital.com',
          onboardingTimeLimit: 30,
          timezone: 'Africa/Luanda',
          language: 'pt-PT',
          maxLoginAttempts: 5,
          emailNotifications: true,
          autoApproval: false,
          maintenanceMode: false
        }
      }
    },
    saveEmailTemplates() {
      console.log('Salvando modelos de email:', this.emailTemplates)
      alert('Modelos de email salvos com sucesso!')
    },
    editRole(role) {
      console.log('Editando função:', role)
    },
    saveRolePermissions() {
      console.log('Salvando permissões:', this.userRoles)
      alert('Permissões salvas com sucesso!')
    },
    saveSecuritySettings() {
      console.log('Salvando configurações de segurança:', this.securitySettings)
      alert('Configurações de segurança salvas com sucesso!')
    },
    createBackup() {
      console.log('Criando backup...')
      alert('Backup criado com sucesso!')
      // Simular adição de novo backup
      const now = new Date()
      const newBackup = {
        id: this.recentBackups.length + 1,
        date: now.toLocaleString('pt-PT'),
        size: (Math.random() * 10 + 40).toFixed(1) + ' MB'
      }
      this.recentBackups.unshift(newBackup)
      if (this.recentBackups.length > 5) {
        this.recentBackups.pop()
      }
    },
    restoreBackup() {
      if (confirm('Tem certeza que deseja restaurar um backup? Esta ação não pode ser desfeita.')) {
        console.log('Restaurando backup...')
        alert('Funcionalidade de restauração será implementada em breve!')
      }
    },
    downloadBackup(backup) {
      console.log('Baixando backup:', backup)
      alert(`Baixando backup de ${backup.date}...`)
    }
  }
}
</script>