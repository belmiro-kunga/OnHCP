import axios from 'axios'

// Central API client with baseURL and interceptors
// baseURL: from Vite env (VITE_API_BASE_URL) or fallback to '/api'
const baseURL = (import.meta?.env?.VITE_API_BASE_URL) || '/api'

export const api = axios.create({
  baseURL,
  withCredentials: false,
})

// Request: attach Authorization if token exists
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers = config.headers || {}
    config.headers.Authorization = `Bearer ${token}`
  }
  if (import.meta.env.DEV) {
    // Lightweight debug
    const method = (config.method || 'get').toUpperCase()
    const url = `${config.baseURL || ''}${config.url || ''}`
    // Don't log potentially sensitive bodies
    // eslint-disable-next-line no-console
    console.debug('[API][REQ]', method, url, {
      headers: config.headers,
      params: config.params,
    })
  }
  return config
})

// Response: normalize errors, basic 401 handling
api.interceptors.response.use(
  (res) => {
    if (import.meta.env.DEV) {
      // eslint-disable-next-line no-console
      console.debug('[API][RES]', res.status, res.config?.url)
    }
    return res
  },
  (err) => {
    if (import.meta.env.DEV) {
      // eslint-disable-next-line no-console
      console.error('[API][ERR]', {
        status: err?.response?.status,
        url: err?.config?.url,
        method: err?.config?.method?.toUpperCase(),
        data: err?.response?.data,
        message: err?.message
      })
    }
    
    if (err?.response?.status === 401) {
      // Optional: clear token and signal app
      try { localStorage.removeItem('token') } catch (_) {}
      // Could dispatch an event the app can listen to
      window.dispatchEvent(new CustomEvent('auth:unauthorized'))
    }
    
    return Promise.reject(err)
  }
)

export default api
