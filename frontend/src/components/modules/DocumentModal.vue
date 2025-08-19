<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">
          {{ isEditing ? 'Editar Documento' : 'Novo Documento' }}
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
        <form @submit.prevent="saveDocument" class="p-6 space-y-6">
          <!-- Basic Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Documento *</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Ex: Contrato de Trabalho"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
              <select
                v-model="form.type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Selecione um tipo</option>
                <option value="contract">Contrato</option>
                <option value="policy">Política</option>
                <option value="manual">Manual</option>
                <option value="form">Formulário</option>
                <option value="other">Outro</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Descreva o propósito e conteúdo do documento..."
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
              <select
                v-model="form.department_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Todos os departamentos</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="draft">Rascunho</option>
                <option value="active">Ativo</option>
                <option value="inactive">Inativo</option>
              </select>
            </div>
          </div>

          <!-- File Upload -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              {{ isEditing ? 'Substituir Arquivo' : 'Arquivo *' }}
            </label>
            
            <!-- Current File Info (for editing) -->
            <div v-if="isEditing && document.file_name" class="mb-4 p-3 bg-gray-50 rounded-lg">
              <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ document.file_name }}</p>
                  <p class="text-xs text-gray-500">{{ formatFileSize(document.file_size) }}</p>
                </div>
              </div>
            </div>
            
            <!-- File Upload Area -->
            <div
              @drop="handleDrop"
              @dragover.prevent
              @dragenter.prevent
              class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400 transition-colors"
              :class="{ 'border-blue-400 bg-blue-50': dragOver }"
            >
              <input
                ref="fileInput"
                type="file"
                @change="handleFileSelect"
                class="hidden"
                accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png"
              >
              
              <div v-if="!selectedFile">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                <p class="text-gray-600 mb-2">Arraste e solte um arquivo aqui ou</p>
                <button
                  type="button"
                  @click="$refs.fileInput.click()"
                  class="text-blue-600 hover:text-blue-700 font-medium"
                >
                  clique para selecionar
                </button>
                <p class="text-xs text-gray-500 mt-2">
                  Formatos aceitos: PDF, DOC, DOCX, TXT, JPG, PNG (máx. 10MB)
                </p>
              </div>
              
              <div v-else class="flex items-center justify-center gap-3">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ selectedFile.name }}</p>
                  <p class="text-xs text-gray-500">{{ formatFileSize(selectedFile.size) }}</p>
                </div>
                <button
                  type="button"
                  @click="removeFile"
                  class="text-red-600 hover:text-red-700 p-1"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Document Options -->
          <div class="space-y-4">
            <div class="flex items-center">
              <input
                v-model="form.requires_signature"
                type="checkbox"
                id="requires_signature"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              >
              <label for="requires_signature" class="ml-2 text-sm text-gray-700">
                Requer assinatura digital
              </label>
            </div>
            
            <div class="flex items-center">
              <input
                v-model="form.is_mandatory"
                type="checkbox"
                id="is_mandatory"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              >
              <label for="is_mandatory" class="ml-2 text-sm text-gray-700">
                Documento obrigatório no onboarding
              </label>
            </div>
          </div>

          <!-- Upload Progress -->
          <div v-if="uploading" class="space-y-2">
            <div class="flex justify-between text-sm text-gray-600">
              <span>Enviando arquivo...</span>
              <span>{{ uploadProgress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: uploadProgress + '%' }"
              ></div>
            </div>
          </div>
        </form>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
        <button
          type="button"
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
        >
          Cancelar
        </button>
        <button
          @click="saveDocument"
          :disabled="saving || uploading || (!selectedFile && !isEditing)"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
        >
          <svg v-if="saving || uploading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ saving || uploading ? 'Salvando...' : (isEditing ? 'Atualizar' : 'Criar Documento') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue'
import { useToast } from '@/composables/useToast'

export default {
  name: 'DocumentModal',
  props: {
    document: {
      type: Object,
      default: null
    },
    departments: {
      type: Array,
      default: () => []
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const { showToast } = useToast()
    
    const saving = ref(false)
    const uploading = ref(false)
    const uploadProgress = ref(0)
    const dragOver = ref(false)
    const selectedFile = ref(null)
    const fileInput = ref(null)
    
    const isEditing = computed(() => !!props.document)
    
    const form = reactive({
      name: '',
      description: '',
      type: '',
      department_id: '',
      status: 'draft',
      requires_signature: false,
      is_mandatory: false
    })
    
    const handleFileSelect = (event) => {
      const file = event.target.files[0]
      if (file) {
        validateAndSetFile(file)
      }
    }
    
    const handleDrop = (event) => {
      event.preventDefault()
      dragOver.value = false
      
      const file = event.dataTransfer.files[0]
      if (file) {
        validateAndSetFile(file)
      }
    }
    
    const validateAndSetFile = (file) => {
      // Validate file size (10MB max)
      if (file.size > 10 * 1024 * 1024) {
        showToast('O arquivo deve ter no máximo 10MB', 'error')
        return
      }
      
      // Validate file type
      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'image/jpeg',
        'image/jpg',
        'image/png'
      ]
      
      if (!allowedTypes.includes(file.type)) {
        showToast('Tipo de arquivo não suportado', 'error')
        return
      }
      
      selectedFile.value = file
      
      // Auto-fill name if empty
      if (!form.name) {
        form.name = file.name.replace(/\.[^/.]+$/, '')
      }
    }
    
    const removeFile = () => {
      selectedFile.value = null
      if (fileInput.value) {
        fileInput.value.value = ''
      }
    }
    
    const formatFileSize = (bytes) => {
      if (!bytes) return 'N/A'
      const sizes = ['Bytes', 'KB', 'MB', 'GB']
      const i = Math.floor(Math.log(bytes) / Math.log(1024))
      return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
    }
    
    const saveDocument = async () => {
      if (!isEditing.value && !selectedFile.value) {
        showToast('Selecione um arquivo', 'error')
        return
      }
      
      saving.value = true
      uploading.value = !!selectedFile.value
      uploadProgress.value = 0
      
      try {
        const formData = new FormData()
        
        // Add form fields
        Object.keys(form).forEach(key => {
          if (form[key] !== null && form[key] !== '') {
            formData.append(key, form[key])
          }
        })
        
        // Add file if selected
        if (selectedFile.value) {
          formData.append('file', selectedFile.value)
        }
        
        const url = isEditing.value 
          ? `/api/documents/${props.document.id}`
          : '/api/documents'
        
        const method = isEditing.value ? 'PUT' : 'POST'
        
        const xhr = new XMLHttpRequest()
        
        // Track upload progress
        if (uploading.value) {
          xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
              uploadProgress.value = Math.round((e.loaded / e.total) * 100)
            }
          })
        }
        
        const response = await new Promise((resolve, reject) => {
          xhr.onload = () => resolve(xhr)
          xhr.onerror = () => reject(new Error('Erro na requisição'))
          
          xhr.open(method, url)
          xhr.setRequestHeader('Authorization', `Bearer ${localStorage.getItem('token')}`)
          
          // For PUT requests, we need to use POST with _method override
          if (method === 'PUT') {
            formData.append('_method', 'PUT')
            xhr.open('POST', url)
          }
          
          xhr.send(formData)
        })
        
        if (response.status < 200 || response.status >= 300) {
          const error = JSON.parse(response.responseText)
          throw new Error(error.message || 'Erro ao salvar documento')
        }
        
        showToast(
          isEditing.value ? 'Documento atualizado com sucesso!' : 'Documento criado com sucesso!',
          'success'
        )
        emit('saved')
      } catch (error) {
        showToast('Erro ao salvar documento: ' + error.message, 'error')
      } finally {
        saving.value = false
        uploading.value = false
        uploadProgress.value = 0
      }
    }
    
    // Initialize form with document data if editing
    watch(() => props.document, (document) => {
      if (document) {
        Object.assign(form, {
          name: document.name || '',
          description: document.description || '',
          type: document.type || '',
          department_id: document.department_id || '',
          status: document.status || 'draft',
          requires_signature: document.requires_signature || false,
          is_mandatory: document.is_mandatory || false
        })
      } else {
        // Reset form for new document
        Object.assign(form, {
          name: '',
          description: '',
          type: '',
          department_id: '',
          status: 'draft',
          requires_signature: false,
          is_mandatory: false
        })
        removeFile()
      }
    }, { immediate: true })
    
    return {
      saving,
      uploading,
      uploadProgress,
      dragOver,
      selectedFile,
      fileInput,
      isEditing,
      form,
      handleFileSelect,
      handleDrop,
      removeFile,
      formatFileSize,
      saveDocument
    }
  }
}
</script>