<template>
  <div class="space-y-8">
    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Email (SMTP)</h3>
        <div class="text-sm text-gray-500">Configuração do servidor de email</div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="form-label">Host</label>
          <input class="form-input" v-model="form.host" :disabled="!can('users.manage')" placeholder="smtp.seudominio.com" />
        </div>
        <div>
          <label class="form-label">Porta</label>
          <input class="form-input" type="number" v-model.number="form.port" :disabled="!can('users.manage')" placeholder="587" />
        </div>
        <div>
          <label class="form-label">Criptografia</label>
          <select class="form-select" v-model="form.encryption" :disabled="!can('users.manage')">
            <option value="none">Nenhuma</option>
            <option value="ssl">SSL (465)</option>
            <option value="tls">STARTTLS (587)</option>
          </select>
        </div>
        <div>
          <label class="form-label">Autenticação</label>
          <select class="form-select" v-model="form.auth" :disabled="!can('users.manage')">
            <option :value="true">Ativada</option>
            <option :value="false">Desativada</option>
          </select>
        </div>
        <div>
          <label class="form-label">Utilizador</label>
          <input class="form-input" v-model="form.username" :disabled="!can('users.manage')" placeholder="user@seudominio.com" />
        </div>
        <div>
          <label class="form-label">Senha</label>
          <input class="form-input" type="password" v-model="password" :disabled="!can('users.manage')" placeholder="••••••••" />
          <p class="text-xs text-gray-500 mt-1">Deixe em branco para manter a senha atual.</p>
        </div>
        <div>
          <label class="form-label">Remetente (Email)</label>
          <input class="form-input" v-model="form.from_address" :disabled="!can('users.manage')" placeholder="no-reply@seudominio.com" />
        </div>
        <div>
          <label class="form-label">Remetente (Nome)</label>
          <input class="form-input" v-model="form.from_name" :disabled="!can('users.manage')" placeholder="OnHCP" />
        </div>
        <div>
          <label class="form-label">Timeout (segundos)</label>
          <input class="form-input" type="number" v-model.number="form.timeout" :disabled="!can('users.manage')" placeholder="10" />
        </div>
      </div>

      <div class="flex items-center gap-3 mt-4">
        <button class="btn-primary" @click="save" :disabled="!can('users.manage') || saving">
          <span v-if="!saving">Guardar</span>
          <span v-else>Guardando...</span>
        </button>
        <button class="btn-secondary" @click="reload" :disabled="loading || saving">Recarregar</button>
        <button class="btn-outline" @click="test" :disabled="testing">Testar Conexão</button>
        <span v-if="message" :class="messageType === 'success' ? 'text-green-600' : 'text-red-600'" class="text-sm">{{ message }}</span>
      </div>

      <div v-if="testResult" class="mt-3 text-sm" :class="testResult.ok ? 'text-green-700' : 'text-red-700'">
        {{ testResult.message }}
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, onMounted } from 'vue'
import { usePermissions } from '../../composables/usePermissions'

export default {
  name: 'AdminEmail',
  setup() {
    const { can } = usePermissions()
    const loading = ref(false)
    const saving = ref(false)
    const testing = ref(false)
    const message = ref('')
    const messageType = ref('success')
    const testResult = ref(null)

    const form = reactive({
      host: '',
      port: 587,
      encryption: 'tls',
      username: '',
      from_address: '',
      from_name: '',
      auth: true,
      timeout: 10,
    })
    const password = ref('')

    const load = async () => {
      loading.value = true
      message.value = ''
      try {
        const res = await fetch('/api/email/config')
        const json = await res.json()
        Object.assign(form, json.data || {})
      } catch (e) {
        console.error(e)
        messageType.value = 'error'
        message.value = 'Falha ao carregar configuração de email.'
      } finally {
        loading.value = false
      }
    }

    const save = async () => {
      saving.value = true
      message.value = ''
      try {
        const payload = { ...form }
        if (password.value) payload.password = password.value
        const res = await fetch('/api/email/config', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        })
        if (!res.ok) throw new Error('Falha ao guardar configuração')
        const json = await res.json()
        Object.assign(form, json.data || {})
        password.value = ''
        messageType.value = 'success'
        message.value = 'Configuração de email guardada com sucesso.'
      } catch (e) {
        console.error(e)
        messageType.value = 'error'
        message.value = 'Não foi possível guardar a configuração de email.'
      } finally {
        saving.value = false
      }
    }

    const test = async () => {
      testing.value = true
      testResult.value = null
      try {
        const payload = { ...form }
        if (password.value) payload.password = password.value
        const res = await fetch('/api/email/test', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        })
        const json = await res.json()
        testResult.value = json?.data || { ok: false, message: 'Teste executado' }
      } catch (e) {
        console.error(e)
        testResult.value = { ok: false, message: 'Falha ao testar a conexão SMTP' }
      } finally {
        testing.value = false
      }
    }

    const reload = () => load()

    onMounted(load)

    return { can, form, password, loading, saving, testing, message, messageType, testResult, save, reload, test }
  }
}
</script>
