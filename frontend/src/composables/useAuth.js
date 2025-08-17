import { ref, computed } from 'vue'
import { api } from './api'

// Very simple in-memory auth state for dev/testing
// In the future, wire this to backend auth and persist tokens
const state = {
  isAuthenticated: ref(!!(typeof localStorage !== 'undefined' && localStorage.getItem('token'))),
  // Simulated current user with roles and attributes for ABAC
  currentUser: ref({
    id: 1,
    name: 'Admin Demo',
    roles: ['admin'], // e.g., ['admin'], ['manager'], ['collaborator'], ['guest']
    attributes: {
      department: 'Administração',
      team: 'Core',
      branch: 'HQ',
      clearance: 5,
    },
  }),
  // auth token (optional). If provided by backend, persist for API client
  token: ref(localStorage.getItem('token') || null),
}

export function useAuth() {
  const login = async (email, password) => {
    try {
      const res = await api.post('/auth/login', { email, password })
      const token = res.data?.data?.token
      const user = res.data?.data?.user
      if (!token) throw new Error('Token não recebido do servidor')
      setToken(token)
      if (user) {
        // Map backend user data to frontend format
        const backendRoles = Array.isArray(user.roles) ? user.roles : null
        const backendPerms = Array.isArray(user.permissions) ? user.permissions : null
        const derivedRoles = user.is_admin ? ['admin'] : ['guest']

        const mappedUser = {
          ...user,
          roles: (backendRoles && backendRoles.length > 0) ? backendRoles : derivedRoles,
          permissions: backendPerms || undefined,
          attributes: {
            department: 'Administração',
            team: 'Core',
            branch: 'HQ',
            clearance: (backendRoles?.includes('admin') || user.is_admin) ? 5 : 1,
          }
        }
        state.currentUser.value = mappedUser
      }
      state.isAuthenticated.value = true
      return true
    } catch (e) {
      const msg = e?.response?.data?.message || e.message || 'Falha no login'
      throw new Error(msg)
    }
  }

  const loginAs = (user) => {
    state.currentUser.value = user
    state.isAuthenticated.value = true
  }

  const logout = async () => {
    try {
      if (state.token.value) {
        await api.post('/auth/logout')
      }
    } catch (_) { /* ignore */ }
    state.isAuthenticated.value = false
    state.currentUser.value = null
    state.token.value = null
    try { localStorage.removeItem('token') } catch (_) {}
  }

  const hasRole = (role) => {
    if (!state.currentUser.value) return false
    return state.currentUser.value.roles?.includes(role)
  }

  const roles = computed(() => state.currentUser.value?.roles ?? [])
  const attributes = computed(() => state.currentUser.value?.attributes ?? {})

  // Token helpers
  const setToken = (token) => {
    state.token.value = token || null
    try {
      if (token) localStorage.setItem('token', token)
      else localStorage.removeItem('token')
    } catch (_) {}
  }

  return {
    isAuthenticated: state.isAuthenticated,
    currentUser: state.currentUser,
    token: state.token,
    roles,
    attributes,
    hasRole,
    login,
    loginAs,
    logout,
    setToken,
  }
}

// Backend API helpers for Authentication & Access management
// Stats
export async function getAuthStats() {
  const res = await api.get('/auth/stats')
  // Support multiple possible shapes
  return res?.data?.data || res?.data || {}
}

// Methods
export async function getAuthMethods() {
  const res = await api.get('/auth/methods')
  return res?.data?.data || res?.data || []
}

export async function createAuthMethod(payload) {
  const res = await api.post('/auth/methods', payload)
  return res?.data?.data || res?.data
}

export async function toggleAuthMethod(id, enabled) {
  const res = await api.patch(`/auth/methods/${id}`, { enabled })
  return res?.data?.data || res?.data
}

// Sessions
export async function getSessions() {
  const res = await api.get('/auth/sessions')
  return res?.data?.data || res?.data || []
}

export async function forceLogoutSession(sessionId) {
  const res = await api.post(`/auth/sessions/${sessionId}/terminate`)
  return res?.data?.data || res?.data
}

// Security Settings
export async function getSecuritySettings() {
  const res = await api.get('/auth/settings')
  return res?.data?.data || res?.data || {}
}

export async function saveSecuritySettings(payload) {
  const res = await api.put('/auth/settings', payload)
  return res?.data?.data || res?.data
}
