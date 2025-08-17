import { ref } from 'vue'

export function useGroups(baseUrl = '/api') {
  const loading = ref(false)
  const error = ref(null)
  const groups = ref([])
  const members = ref([])

  const list = async () => {
    loading.value = true; error.value = null
    try {
      const res = await fetch(`${baseUrl}/groups`)
      const json = await res.json()
      groups.value = json.data || []
    } catch (e) { error.value = e } finally { loading.value = false }
  }

  const create = async ({ name, description }) => {
    loading.value = true; error.value = null
    try {
      const res = await fetch(`${baseUrl}/groups`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ name, description }) })
      const json = await res.json()
      await list()
      return json.data
    } catch (e) { error.value = e; throw e } finally { loading.value = false }
  }

  const update = async (id, payload) => {
    loading.value = true; error.value = null
    try {
      await fetch(`${baseUrl}/groups/${id}`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
      await list()
    } catch (e) { error.value = e; throw e } finally { loading.value = false }
  }

  const remove = async (id) => {
    loading.value = true; error.value = null
    try {
      await fetch(`${baseUrl}/groups/${id}`, { method: 'DELETE' })
      await list()
    } catch (e) { error.value = e; throw e } finally { loading.value = false }
  }

  const listMembers = async (groupId) => {
    loading.value = true; error.value = null
    try {
      const res = await fetch(`${baseUrl}/groups/${groupId}/members`)
      const json = await res.json()
      members.value = json.data || []
    } catch (e) { error.value = e } finally { loading.value = false }
  }

  const addMember = async (groupId, { user_id, role }) => {
    loading.value = true; error.value = null
    try {
      await fetch(`${baseUrl}/groups/${groupId}/members`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ user_id, role }) })
      await listMembers(groupId)
    } catch (e) { error.value = e; throw e } finally { loading.value = false }
  }

  const removeMember = async (groupId, userId) => {
    loading.value = true; error.value = null
    try {
      await fetch(`${baseUrl}/groups/${groupId}/members/${userId}`, { method: 'DELETE' })
      await listMembers(groupId)
    } catch (e) { error.value = e; throw e } finally { loading.value = false }
  }

  return { loading, error, groups, members, list, create, update, remove, listMembers, addMember, removeMember }
}
