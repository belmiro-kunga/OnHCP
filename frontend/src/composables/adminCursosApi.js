import { api } from './api'

// Admin Cursos API (Video courses)
// All methods return response data directly and throw on errors.
// Endpoints follow the same style used in adminSimuladoApi.js
// Adjust backend routes if needed to match your server.
export const adminCursosApi = {
  // Cursos CRUD
  async list(params = {}) {
    const { data } = await api.get('/admin/cursos', { params })
    return data
  },
  async get(id) {
    const { data } = await api.get(`/admin/cursos/${id}`)
    return data
  },
  // payload pode ser um objeto simples ou FormData (para thumbnail)
  async create(payload) {
    let body = payload
    let headers = {}
    if (!(payload instanceof FormData)) {
      // construir FormData se tiver thumbnail file
      body = new FormData()
      Object.entries(payload || {}).forEach(([k, v]) => {
        if (v !== undefined && v !== null) body.append(k, v)
      })
    }
    headers = { 'Content-Type': 'multipart/form-data' }
    const { data } = await api.post('/admin/cursos', body, { headers })
    return data
  },
  async update(id, payload) {
    let body = payload
    let headers = {}
    // Se houver ficheiro de thumbnail, enviar como multipart
    const hasFile = payload instanceof FormData || (payload && payload.thumbnail instanceof File)
    if (hasFile && !(payload instanceof FormData)) {
      body = new FormData()
      Object.entries(payload || {}).forEach(([k, v]) => {
        if (v !== undefined && v !== null) body.append(k, v)
      })
    }
    if (hasFile) headers = { 'Content-Type': 'multipart/form-data' }
    const { data } = await api.put(`/admin/cursos/${id}`, body, { headers })
    return data
  },
  async remove(id) {
    const { data } = await api.delete(`/admin/cursos/${id}`)
    return data
  },

  // Publicação de cursos
  async publish(id) {
    const { data } = await api.post(`/admin/cursos/${id}/publish`)
    return data
  },
  async unpublish(id) {
    const { data } = await api.post(`/admin/cursos/${id}/unpublish`)
    return data
  },

  // Stats for cards/overview (DEPRECATED: no matching backend route)
  // async stats(params = {}) {
  //   const { data } = await api.get('/admin/cursos/stats', { params })
  //   return data
  // },

  // Categories
  async listCategories() {
    const { data } = await api.get('/admin/curso-categories')
    return data
  },
  async createCategory(name) {
    const { data } = await api.post('/admin/curso-categories', { name })
    return data
  },
  async updateCategory(id, name) {
    const { data } = await api.put(`/admin/curso-categories/${id}`, { name })
    return data
  },
  async deleteCategory(id) {
    const { data } = await api.delete(`/admin/curso-categories/${id}`)
    return data
  },

  // Video workflow
  // 1) Initialize upload (e.g., get pre-signed URL or upload session)
  async initUpload(cursoId, payload = { filename: '', contentType: '', size: 0 }) {
    const { data } = await api.post(`/admin/cursos/${cursoId}/video/upload-init`, payload)
    return data
  },
  // 2) Complete upload (notify backend to start transcode / finalize)
  async completeUpload(cursoId, payload = { uploadId: '', parts: [] }) {
    const { data } = await api.post(`/admin/cursos/${cursoId}/video/upload-complete`, payload)
    return data
  },
  // 3) Get playback URLs (HLS m3u8, MP4 fallback, captions list)
  async getPlaybackUrls(cursoId) {
    const { data } = await api.get(`/admin/cursos/${cursoId}/video/playback`)
    return data
  },
  // 4) Save viewer progress (position, percent watched, completed flag)
  async saveProgress(cursoId, payload = { position: 0, percent: 0, completed: false }) {
    const { data } = await api.post(`/admin/cursos/${cursoId}/video/progress`, payload)
    return data
  },

  // Módulos
  async addModule(courseId, payload) {
    const { data } = await api.post(`/admin/cursos/${courseId}/modules`, payload)
    return data
  },
  async updateModule(courseId, moduleId, payload) {
    const { data } = await api.put(`/admin/cursos/${courseId}/modules/${moduleId}`, payload)
    return data
  },
  async deleteModule(courseId, moduleId) {
    const { data } = await api.delete(`/admin/cursos/${courseId}/modules/${moduleId}`)
    return data
  },

  // Aulas
  async addLesson(courseId, moduleId, payload) {
    const { data } = await api.post(`/admin/cursos/${courseId}/modules/${moduleId}/lessons`, payload)
    return data
  },
  async updateLesson(courseId, moduleId, lessonId, payload) {
    const { data } = await api.put(`/admin/cursos/${courseId}/modules/${moduleId}/lessons/${lessonId}`, payload)
    return data
  },
  async deleteLesson(courseId, moduleId, lessonId) {
    const { data } = await api.delete(`/admin/cursos/${courseId}/modules/${moduleId}/lessons/${lessonId}`)
    return data
  },
}
