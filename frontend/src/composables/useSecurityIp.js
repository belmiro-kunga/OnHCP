import { ref } from 'vue'

// Simple composable to interact with backend IP security endpoints
// Endpoints (see backend routes):
//  GET    /api/security/ip-policies
//  POST   /api/security/ip-policies { type: 'whitelist'|'blacklist', ip_cidr, reason? }
//  DELETE /api/security/ip-policies/{id}
//  GET    /api/security/ip-locks
//  POST   /api/security/ip-locks/unlock { ip }

export function useSecurityIp(baseUrl = '/api') {
  const loading = ref(false)
  const error = ref(null)
  const policies = ref([])
  const locks = ref([])

  const handle = async (fn) => {
    loading.value = true
    error.value = null
    try {
      return await fn()
    } catch (e) {
      error.value = e?.message || 'Erro inesperado'
      throw e
    } finally {
      loading.value = false
    }
  }

  const listPolicies = async () => handle(async () => {
    const res = await fetch(`${baseUrl}/security/ip-policies`)
    if (!res.ok) throw new Error('Falha ao carregar políticas de IP')
    const json = await res.json()
    policies.value = json.data || []
    return policies.value
  })

  const createPolicy = async ({ type, ip_cidr, reason }) => handle(async () => {
    const res = await fetch(`${baseUrl}/security/ip-policies`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ type, ip_cidr, reason }),
    })
    if (!res.ok) {
      const t = await res.text()
      throw new Error(`Falha ao criar política (${res.status}): ${t}`)
    }
    const json = await res.json()
    // push or reload list
    policies.value = [json.data, ...policies.value]
    return json.data
  })

  const deletePolicy = async (policyId) => handle(async () => {
    const res = await fetch(`${baseUrl}/security/ip-policies/${policyId}`, { method: 'DELETE' })
    if (!res.ok) throw new Error('Falha ao remover política')
    policies.value = policies.value.filter(p => p.id !== policyId)
    return true
  })

  const listLocks = async () => handle(async () => {
    const res = await fetch(`${baseUrl}/security/ip-locks`)
    if (!res.ok) throw new Error('Falha ao carregar bloqueios de IP')
    const json = await res.json()
    locks.value = json.data || []
    return locks.value
  })

  const unlockIp = async (ip) => handle(async () => {
    const res = await fetch(`${baseUrl}/security/ip-locks/unlock`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ip }),
    })
    if (!res.ok) throw new Error('Falha ao desbloquear IP')
    // refresh locks after unlock
    await listLocks()
    return true
  })

  return {
    loading,
    error,
    policies,
    locks,
    listPolicies,
    createPolicy,
    deletePolicy,
    listLocks,
    unlockIp,
  }
}
