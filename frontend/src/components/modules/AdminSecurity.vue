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
      await Promise.all([fetchPolicies(), fetchLocks()])
      loading.value = false
    })

    return { can, loading, errorMsg, policies, newPolicy, addPolicy, removePolicy, ipLocks, unlockIp }
  }
}
</script>
