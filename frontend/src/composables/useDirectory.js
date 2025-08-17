import { api } from './api'

export async function getDirectoryConfig() {
  const res = await api.get('/directory/config')
  // Backend returns { data: { host, base_dn, bind_dn, use_ssl, use_tls } }
  return res?.data?.data || {}
}

export async function updateDirectoryConfig(payload) {
  // payload: { host, base_dn, bind_dn, bind_password?, use_ssl, use_tls }
  const res = await api.put('/directory/config', payload)
  return res?.data?.data || {}
}

export async function testDirectoryConnection(payload = null) {
  // Optional payload to test without saving
  const res = await api.post('/directory/test-connection', payload || {})
  return res?.data?.data || {}
}

export async function listDirectoryGroups() {
  const res = await api.get('/directory/groups')
  return res?.data?.data || []
}
