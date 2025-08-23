<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold text-gray-900">Configurações</h2>
      <div class="flex gap-2 text-sm text-gray-500">
        <span v-if="!can('admin.dashboard.view')" class="text-red-700">Acesso negado</span>
      </div>
    </div>

    <div v-if="!can('admin.dashboard.view')" class="p-6 bg-red-50 border border-red-200 rounded-lg text-red-800">
      <h3 class="text-lg font-semibold mb-2">Acesso negado</h3>
      <p>Você não possui permissão para visualizar as configurações.</p>
    </div>

    <div v-else class="space-y-6">
      <!-- Submenu tabs -->
      <nav class="border-b border-gray-200">
        <ul class="-mb-px flex space-x-6">
          <li>
            <button
              class="whitespace-nowrap py-3 border-b-2 text-sm font-medium"
              :class="subTab === 'login' ? 'border-primary-600 text-primary-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              @click="selectSubTab('login')"
            >
              Login
            </button>
          </li>
          <li>
            <button
              class="whitespace-nowrap py-3 border-b-2 text-sm font-medium"
              :class="subTab === 'security' ? 'border-primary-600 text-primary-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              @click="selectSubTab('security')"
            >
              Segurança
            </button>
          </li>
          <li>
            <button
              class="whitespace-nowrap py-3 border-b-2 text-sm font-medium"
              :class="subTab === 'audit' ? 'border-primary-600 text-primary-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              @click="selectSubTab('audit')"
            >
              Auditoria
            </button>
          </li>
          <li>
            <button
              class="whitespace-nowrap py-3 border-b-2 text-sm font-medium"
              :class="subTab === 'email' ? 'border-primary-600 text-primary-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              @click="selectSubTab('email')"
            >
              Email
            </button>
          </li>
          <li>
            <button
              class="whitespace-nowrap py-3 border-b-2 text-sm font-medium"
              :class="subTab === 'video' ? 'border-primary-600 text-primary-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
              @click="selectSubTab('video')"
            >
              Vídeo
            </button>
          </li>
        </ul>
      </nav>

      <!-- Conteúdo: Login -->
      <div v-if="subTab === 'login'" class="space-y-8">
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Modo de Login</h3>
            <div class="text-sm text-gray-500">Defina o método padrão de autenticação</div>
          </div>

          <div class="space-y-4">
            <div class="flex items-center gap-4">
              <label class="inline-flex items-center gap-2">
                <input type="radio" class="form-radio" value="password" v-model="loginMode" />
                <span>Email e Senha</span>
              </label>
              <label class="inline-flex items-center gap-2">
                <input type="radio" class="form-radio" value="ldap" v-model="loginMode" />
                <span>LDAP / Active Directory</span>
              </label>
            </div>

            <p v-if="loginMode === 'password'" class="text-sm text-gray-600">
              Os utilizadores farão login com email e senha definidos no sistema.
            </p>
            <p v-else class="text-sm text-gray-600">
              O login será realizado contra o servidor LDAP/AD configurado. Certifique-se de configurar a conexão LDAP em Segurança.
            </p>

            <div class="flex items-center gap-3">
              <button class="btn-primary" @click="saveMode" :disabled="saving">
                <span v-if="!saving">Guardar</span>
                <span v-else>Guardando...</span>
              </button>
              <button class="btn-secondary" @click="reloadMode" :disabled="loading || saving">Recarregar</button>
              <span v-if="message" :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'" class="text-sm">{{ message }}</span>
            </div>

            <div class="p-3 bg-blue-50 border border-blue-100 rounded text-sm text-blue-900">
              Precisa configurar o LDAP? Vá para <strong>Segurança</strong> e use a secção de <strong>LDAP/Active Directory</strong>.
            </div>
          </div>
        </div>
      </div>

      <!-- Conteúdo: Segurança -->
      <div v-if="subTab === 'security'" class="space-y-8">
        <AdminSecurity />
      </div>

      <!-- Conteúdo: Auditoria -->
      <div v-if="subTab === 'audit'" class="space-y-8">
        <AdminAudit />
      </div>

      <!-- Conteúdo: Email -->
      <div v-if="subTab === 'email'" class="space-y-8">
        <AdminEmail />
      </div>

      <!-- Conteúdo: Vídeo -->
      <div v-if="subTab === 'video'" class="space-y-8">
        <AdminVideoConfig />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { usePermissions } from '../../composables/usePermissions'
import { getLoginMode, setLoginMode } from '../../composables/useAuth'
import AdminSecurity from './AdminSecurity.vue'
import AdminAudit from './AdminAudit.vue'
import AdminEmail from './AdminEmail.vue'
import AdminVideoConfig from './AdminVideoConfig.vue'

export default {
  name: 'AdminSettings',
  components: { AdminSecurity, AdminAudit, AdminEmail, AdminVideoConfig },
  setup() {
    const { can } = usePermissions()
    const router = useRouter()
    const loading = ref(false)
    const saving = ref(false)
    const loginMode = ref('password')
    const message = ref('')
    const messageType = ref('success')
    const subTab = ref('login')

    const loadMode = async () => {
      loading.value = true
      message.value = ''
      try {
        const mode = await getLoginMode()
        loginMode.value = mode === 'ldap' ? 'ldap' : 'password'
      } catch (e) {
        console.error(e)
        messageType.value = 'error'
        message.value = 'Falha ao carregar o modo de login.'
      } finally {
        loading.value = false
      }
    }

    const saveMode = async () => {
      saving.value = true
      message.value = ''
      try {
        const mode = await setLoginMode(loginMode.value)
        loginMode.value = mode
        messageType.value = 'success'
        message.value = 'Modo de login atualizado com sucesso.'
      } catch (e) {
        console.error(e)
        messageType.value = 'error'
        message.value = e?.response?.data?.message || e.message || 'Não foi possível salvar o modo de login.'
      } finally {
        saving.value = false
      }
    }

    const reloadMode = () => loadMode()

    const selectSubTab = (tab) => {
      subTab.value = tab
      // Deep-linking for future tabs
      if (tab === 'login') router.replace('/admin/dashboard/settings/login')
      if (tab === 'security') router.replace('/admin/dashboard/settings/security')
      if (tab === 'audit') router.replace('/admin/dashboard/settings/audit')
      if (tab === 'email') router.replace('/admin/dashboard/settings/email')
      if (tab === 'video') router.replace('/admin/dashboard/settings/video')
    }

    onMounted(() => {
      loadMode()
      // Sync subtab with route
      const path = router.currentRoute.value.path
      if (path.endsWith('/settings/login')) {
        subTab.value = 'login'
      } else if (path.endsWith('/settings/security')) {
        subTab.value = 'security'
      } else if (path.endsWith('/settings/audit')) {
        subTab.value = 'audit'
      } else if (path.endsWith('/settings/email')) {
        subTab.value = 'email'
      } else if (path.endsWith('/settings/video')) {
        subTab.value = 'video'
      }
    })

    return { can, loading, saving, loginMode, message, messageType, saveMode, reloadMode, subTab, selectSubTab }
  }
}
</script>
