<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold text-gray-900">Segurança</h2>
      <div class="flex gap-2 text-sm text-gray-500">
        <span v-if="!can('security.view')" class="text-red-700">Acesso negado</span>
      </div>
    </div>

    <div v-if="!can('security.view')" class="p-6 bg-red-50 border border-red-200 rounded-lg text-red-800">
      <h3 class="text-lg font-semibold mb-2">Acesso negado</h3>
      <p>Você não possui permissão para visualizar as configurações de segurança.</p>
    </div>

    <div v-else class="space-y-8">
      <!-- LDAP / Active Directory Configuration -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">LDAP / Active Directory</h3>
          <div class="text-sm text-gray-500">Configuração de diretório corporativo</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="form-label">Host</label>
            <input v-model="directory.host" placeholder="ex.: ad.contoso.local" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
          <div>
            <label class="form-label">IP</label>
            <input v-model="directory.ip" placeholder="ex.: 10.0.0.10" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
          <div>
            <label class="form-label">Porta</label>
            <input v-model.number="directory.port" type="number" min="1" max="65535" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
          <div class="flex items-center gap-6 pt-6">
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" v-model="directory.use_ssl" :disabled="!can('users.manage')" />
              <span>SSL (LDAPS)</span>
            </label>
            <label class="inline-flex items-center gap-2">
              <input type="checkbox" v-model="directory.use_tls" :disabled="!can('users.manage')" />
              <span>StartTLS</span>
            </label>
          </div>

          <div>
            <label class="form-label">Domínio</label>
            <input v-model="directory.domain" placeholder="ex.: CONTOSO" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
          <div>
            <label class="form-label">Base DN</label>
            <input v-model="directory.base_dn" placeholder="ex.: DC=contoso,DC=local" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
          <div>
            <label class="form-label">Bind DN</label>
            <input v-model="directory.bind_dn" placeholder="ex.: CN=ldap-reader,OU=Service Accounts,DC=contoso,DC=local" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
          <div>
            <label class="form-label">Bind Password</label>
            <input v-model="bindPassword" type="password" autocomplete="new-password" placeholder="••••••••" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
          <div class="md:col-span-2">
            <label class="form-label">Servidores DNS (separados por vírgula)</label>
            <input v-model="dnsServersCsv" placeholder="ex.: 10.0.0.2, 10.0.0.3" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
          <div>
            <label class="form-label">Máscara de Rede</label>
            <input v-model="directory.netmask" placeholder="ex.: 255.255.255.0 ou /24" class="form-input w-full" :disabled="!can('users.manage')" />
          </div>
        </div>

        <div class="flex items-center gap-3 mt-4">
          <button class="btn-primary" @click="saveDirectory" :disabled="savingDirectory || !can('users.manage')">
            <span v-if="!savingDirectory">Guardar</span>
            <span v-else>Guardando...</span>
          </button>
          <button class="btn-secondary" @click="testDirectory" :disabled="testingDirectory">Testar Conexão</button>
          <span v-if="directoryMessage" :class="directoryMessageType === 'success' ? 'text-green-600' : 'text-red-600'" class="text-sm">{{ directoryMessage }}</span>
          <span v-if="testResult" :class="testResult.ok ? 'text-green-700' : 'text-yellow-700'" class="text-sm">{{ testResult.message }}</span>
        </div>
      </div>
      <!-- IP Policies (Whitelist/Blacklist) -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Políticas de IP</h3>
          <div class="flex items-center gap-2">
            <select v-model="newPolicy.type" class="form-input w-36" :disabled="!can('security.manage')">
              <option value="whitelist">Whitelist</option>
              <option value="blacklist">Blacklist</option>
            </select>
            <input v-model="newPolicy.ip_cidr" placeholder="Ex.: 203.0.113.0/24" class="form-input w-56" :disabled="!can('security.manage')"/>
            <input v-model="newPolicy.reason" placeholder="Motivo" class="form-input w-56" :disabled="!can('security.manage')"/>
            <button class="btn-primary" @click="addPolicy" :disabled="!can('security.manage')">Adicionar</button>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP/CIDR</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Motivo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="p in policies" :key="p.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span :class="p.type === 'whitelist' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 rounded-full text-xs font-semibold">{{ p.type }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ p.ip_cidr }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ p.reason }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button class="text-red-600 hover:text-red-800" @click="removePolicy(p)" :disabled="!can('security.manage')">Remover</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- IP Locks (temporary blocks by failed attempts) -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Bloqueios de IP (temporários)</h3>
          <div class="text-sm text-gray-500">Ativos: {{ ipLocks.length }}</div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Falhas</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bloqueado até</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="l in ipLocks" :key="l.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ l.ip }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ l.fail_count }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ l.locked_until }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <button class="text-blue-600 hover:text-blue-800" @click="unlockIp(l)" :disabled="!can('security.manage')">Desbloquear</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Notas -->
      <div class="p-4 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-900">
        As políticas e bloqueios reais são aplicadas no backend. Esta UI é a primeira versão (mock) e será ligada às APIs Laravel.
      </div>
    </div>
  </div>
</template>

<script>
import { reactive, ref, onMounted } from 'vue'
import { usePermissions } from '../../composables/usePermissions'

export default {
  name: 'AdminSecurity',
  setup() {
    const { can } = usePermissions()

    const loading = ref(false)
    const errorMsg = ref('')

    const policies = reactive([])
    const ipLocks = reactive([])
    const newPolicy = reactive({ type: 'whitelist', ip_cidr: '', reason: '' })

    // LDAP / AD state
    const directory = reactive({
      host: '', ip: '', port: 389, use_ssl: false, use_tls: false,
      dns_servers: [], netmask: '', domain: '', base_dn: '', bind_dn: ''
    })
    const bindPassword = ref('') // not returned by API
    const dnsServersCsv = ref('')
    const savingDirectory = ref(false)
    const testingDirectory = ref(false)
    const directoryMessage = ref('')
    const directoryMessageType = ref('success')
    const testResult = ref(null)

    async function fetchPolicies() {
      try {
        const res = await fetch('/api/security/ip-policies')
        const json = await res.json()
        policies.splice(0, policies.length, ...(json.data || []))
      } catch (e) {
        console.error(e)
        errorMsg.value = 'Falha ao carregar políticas de IP.'
      }
    }

    async function fetchLocks() {
      try {
        const res = await fetch('/api/security/ip-locks')
        const json = await res.json()
        ipLocks.splice(0, ipLocks.length, ...(json.data || []))
      } catch (e) {
        console.error(e)
        errorMsg.value = 'Falha ao carregar bloqueios de IP.'
      }
    }

    async function fetchDirectoryConfig() {
      try {
        const res = await fetch('/api/directory/config', { headers: { 'Accept': 'application/json' } })
        const json = await res.json()
        const cfg = json?.data || {}
        Object.assign(directory, cfg)
        dnsServersCsv.value = Array.isArray(cfg.dns_servers) ? cfg.dns_servers.join(', ') : (cfg.dns_servers || '')
      } catch (e) {
        console.error(e)
        directoryMessageType.value = 'error'
        directoryMessage.value = 'Falha ao carregar configuração do diretório.'
      }
    }

    async function saveDirectory() {
      if (!can('users.manage')) return
      savingDirectory.value = true
      directoryMessage.value = ''
      try {
        const payload = {
          ...directory,
          dns_servers: dnsServersCsv.value,
        }
        if (bindPassword.value) payload.bind_password = bindPassword.value
        const res = await fetch('/api/directory/config', {
          method: 'PUT',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        })
        if (!res.ok) throw new Error('Falha ao guardar configuração')
        const json = await res.json()
        Object.assign(directory, json.data || {})
        directoryMessageType.value = 'success'
        directoryMessage.value = 'Configuração guardada com sucesso.'
        bindPassword.value = ''
      } catch (e) {
        console.error(e)
        directoryMessageType.value = 'error'
        directoryMessage.value = 'Não foi possível guardar a configuração.'
      } finally {
        savingDirectory.value = false
      }
    }

    async function testDirectory() {
      testingDirectory.value = true
      testResult.value = null
      try {
        const payload = {
          ...directory,
          dns_servers: dnsServersCsv.value,
        }
        if (bindPassword.value) payload.bind_password = bindPassword.value
        const res = await fetch('/api/directory/test-connection', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        })
        const json = await res.json()
        testResult.value = json?.data || { ok: false, message: 'Teste executado' }
      } catch (e) {
        console.error(e)
        testResult.value = { ok: false, message: 'Falha ao testar a conexão' }
      } finally {
        testingDirectory.value = false
      }
    }

    async function addPolicy() {
      if (!can('security.manage')) return
      if (!newPolicy.ip_cidr) return
      try {
        const res = await fetch('/api/security/ip-policies', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(newPolicy)
        })
        if (!res.ok) throw new Error('Falha ao criar política')
        await fetchPolicies()
        newPolicy.ip_cidr = ''
        newPolicy.reason = ''
      } catch (e) {
        console.error(e)
        errorMsg.value = 'Não foi possível adicionar a política.'
      }
    }

    async function removePolicy(p) {
      if (!can('security.manage')) return
      try {
        const res = await fetch(`/api/security/ip-policies/${p.id}`, { method: 'DELETE' })
        if (!res.ok) throw new Error('Falha ao remover política')
        await fetchPolicies()
      } catch (e) {
        console.error(e)
        errorMsg.value = 'Não foi possível remover a política.'
      }
    }

    async function unlockIp(lock) {
      if (!can('security.manage')) return
      try {
        const res = await fetch('/api/security/ip-locks/unlock', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ ip: lock.ip })
        })
        if (!res.ok) throw new Error('Falha ao desbloquear IP')
        await fetchLocks()
      } catch (e) {
        console.error(e)
        errorMsg.value = 'Não foi possível desbloquear o IP.'
      }
    }

    onMounted(async () => {
      loading.value = true
      await Promise.all([fetchPolicies(), fetchLocks(), fetchDirectoryConfig()])
      loading.value = false
    })

    return { can, loading, errorMsg, policies, newPolicy, addPolicy, removePolicy, ipLocks, unlockIp,
      directory, bindPassword, dnsServersCsv, savingDirectory, testingDirectory, directoryMessage, directoryMessageType, testResult,
      saveDirectory, testDirectory }
  }
}
</script>
