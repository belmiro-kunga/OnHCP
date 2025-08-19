<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-900">
          {{ isEditing ? 'Editar Roteiro' : 'Novo Roteiro' }}
        </h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
        <form @submit.prevent="saveRoadmap" class="p-6 space-y-6">
          <!-- Basic Info -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Roteiro *</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Ex: Onboarding Desenvolvedor"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Departamento *</label>
              <select
                v-model="form.department_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Selecione um departamento</option>
                <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                  {{ dept.name }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Descreva o objetivo e escopo deste roteiro..."
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Duração Estimada (dias) *</label>
              <input
                v-model.number="form.estimated_duration_days"
                type="number"
                min="1"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Ex: 30"
              >
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

          <!-- Steps Section -->
          <div>
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-medium text-gray-900">Etapas do Roteiro</h3>
              <button
                type="button"
                @click="addStep"
                class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-sm flex items-center gap-1"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Adicionar Etapa
              </button>
            </div>

            <div v-if="form.steps.length === 0" class="text-center py-8 text-gray-500">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <p>Nenhuma etapa adicionada ainda</p>
              <p class="text-sm">Clique em "Adicionar Etapa" para começar</p>
            </div>

            <div v-else class="space-y-4">
              <div
                v-for="(step, index) in form.steps"
                :key="step.id"
                class="border border-gray-200 rounded-lg p-4 bg-gray-50"
              >
                <div class="flex justify-between items-start mb-3">
                  <div class="flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2 py-1 rounded">
                      Etapa {{ index + 1 }}
                    </span>
                    <span :class="getStepTypeClass(step.type)" class="text-xs font-medium px-2 py-1 rounded">
                      {{ getStepTypeLabel(step.type) }}
                    </span>
                  </div>
                  <div class="flex gap-2">
                    <button
                      type="button"
                      @click="moveStep(index, -1)"
                      :disabled="index === 0"
                      class="text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                      title="Mover para cima"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                      </svg>
                    </button>
                    <button
                      type="button"
                      @click="moveStep(index, 1)"
                      :disabled="index === form.steps.length - 1"
                      class="text-gray-400 hover:text-gray-600 disabled:opacity-50 disabled:cursor-not-allowed"
                      title="Mover para baixo"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                      </svg>
                    </button>
                    <button
                      type="button"
                      @click="removeStep(index)"
                      class="text-red-400 hover:text-red-600"
                      title="Remover etapa"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                      </svg>
                    </button>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título *</label>
                    <input
                      v-model="step.title"
                      type="text"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Ex: Configurar ambiente de desenvolvimento"
                    >
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                    <select
                      v-model="step.type"
                      required
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                      <option value="task">Tarefa</option>
                      <option value="document">Documento</option>
                      <option value="training">Treinamento</option>
                      <option value="meeting">Reunião</option>
                    </select>
                  </div>
                </div>

                <div class="mt-4">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                  <textarea
                    v-model="step.description"
                    rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Descreva o que deve ser feito nesta etapa..."
                  ></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tempo Estimado (horas)</label>
                    <input
                      v-model.number="step.estimated_hours"
                      type="number"
                      min="1"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Ex: 4"
                    >
                  </div>
                  <div class="flex items-center mt-6">
                    <label class="flex items-center">
                      <input
                        v-model="step.is_mandatory"
                        type="checkbox"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                      >
                      <span class="ml-2 text-sm text-gray-700">Etapa obrigatória</span>
                    </label>
                  </div>
                </div>
              </div>
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
          @click="saveRoadmap"
          :disabled="saving"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
        >
          <svg v-if="saving" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ saving ? 'Salvando...' : (isEditing ? 'Atualizar' : 'Criar Roteiro') }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, watch } from 'vue'
import { useToast } from '@/composables/useToast'

export default {
  name: 'RoadmapModal',
  props: {
    roadmap: {
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
    const isEditing = computed(() => !!props.roadmap)
    
    const form = reactive({
      name: '',
      description: '',
      department_id: '',
      estimated_duration_days: 30,
      status: 'draft',
      steps: []
    })
    
    const generateStepId = () => {
      return 'step_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9)
    }
    
    const addStep = () => {
      form.steps.push({
        id: generateStepId(),
        title: '',
        description: '',
        type: 'task',
        estimated_hours: null,
        is_mandatory: true
      })
    }
    
    const removeStep = (index) => {
      if (confirm('Tem certeza que deseja remover esta etapa?')) {
        form.steps.splice(index, 1)
      }
    }
    
    const moveStep = (index, direction) => {
      const newIndex = index + direction
      if (newIndex >= 0 && newIndex < form.steps.length) {
        const step = form.steps.splice(index, 1)[0]
        form.steps.splice(newIndex, 0, step)
      }
    }
    
    const getStepTypeClass = (type) => {
      const classes = {
        task: 'bg-blue-100 text-blue-800',
        document: 'bg-green-100 text-green-800',
        training: 'bg-purple-100 text-purple-800',
        meeting: 'bg-orange-100 text-orange-800'
      }
      return classes[type] || 'bg-gray-100 text-gray-800'
    }
    
    const getStepTypeLabel = (type) => {
      const labels = {
        task: 'Tarefa',
        document: 'Documento',
        training: 'Treinamento',
        meeting: 'Reunião'
      }
      return labels[type] || type
    }
    
    const saveRoadmap = async () => {
      if (form.steps.length === 0) {
        showToast('Adicione pelo menos uma etapa ao roteiro', 'error')
        return
      }
      
      saving.value = true
      try {
        const url = isEditing.value 
          ? `/api/onboarding-roadmaps/${props.roadmap.id}`
          : '/api/onboarding-roadmaps'
        
        const method = isEditing.value ? 'PUT' : 'POST'
        
        const response = await fetch(url, {
          method,
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(form)
        })
        
        if (!response.ok) {
          const error = await response.json()
          throw new Error(error.message || 'Erro ao salvar roteiro')
        }
        
        showToast(
          isEditing.value ? 'Roteiro atualizado com sucesso!' : 'Roteiro criado com sucesso!',
          'success'
        )
        emit('saved')
      } catch (error) {
        showToast('Erro ao salvar roteiro: ' + error.message, 'error')
      } finally {
        saving.value = false
      }
    }
    
    // Initialize form with roadmap data if editing
    watch(() => props.roadmap, (roadmap) => {
      if (roadmap) {
        Object.assign(form, {
          name: roadmap.name || '',
          description: roadmap.description || '',
          department_id: roadmap.department_id || '',
          estimated_duration_days: roadmap.estimated_duration_days || 30,
          status: roadmap.status || 'draft',
          steps: roadmap.steps ? [...roadmap.steps] : []
        })
      } else {
        // Reset form for new roadmap
        Object.assign(form, {
          name: '',
          description: '',
          department_id: '',
          estimated_duration_days: 30,
          status: 'draft',
          steps: []
        })
      }
    }, { immediate: true })
    
    return {
      saving,
      isEditing,
      form,
      addStep,
      removeStep,
      moveStep,
      getStepTypeClass,
      getStepTypeLabel,
      saveRoadmap
    }
  }
}
</script>