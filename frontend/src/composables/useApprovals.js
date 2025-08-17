import { ref } from 'vue'

export function useApprovals(baseUrl = '/api') {
  const loading = ref(false)
  const error = ref(null)
  const items = ref([])

  const list = async () => {
    loading.value = true; error.value = null
    try {
      const res = await fetch(`${baseUrl}/approvals`)
      const json = await res.json()
      items.value = json.data || []
    } catch (e) { error.value = e } finally { loading.value = false }
  }

  const create = async ({ target, change_set, reason }) => {
    loading.value = true; error.value = null
    try {
      const res = await fetch(`${baseUrl}/approvals`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ target, change_set, reason }) })
      const json = await res.json()
      await list()
      return json.data
    } catch (e) { error.value = e; throw e } finally { loading.value = false }
  }

  const approve = async (id) => {
    loading.value = true; error.value = null
    try {
      await fetch(`${baseUrl}/approvals/${id}/approve`, { method: 'POST' })
      await list()
    } catch (e) { error.value = e; throw e } finally { loading.value = false }
  }

  const reject = async (id, reason) => {
    loading.value = true; error.value = null
    try {
      await fetch(`${baseUrl}/approvals/${id}/reject`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ reason }) })
      await list()
    } catch (e) { error.value = e; throw e } finally { loading.value = false }
  }

  return { loading, error, items, list, create, approve, reject }
}
