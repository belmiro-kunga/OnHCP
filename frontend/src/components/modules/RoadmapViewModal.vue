<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
          <h2 class="text-xl font-semibold text-gray-900">{{ roadmap.name }}</h2>
          <div class="flex items-center gap-2 mt-1">
            <span :class="getStatusClass(roadmap.status)" class="text-xs font-medium px-2 py-1 rounded">
              {{ getStatusLabel(roadmap.status) }}
            </span>
            <span class="text-sm text-gray-500">{{ roadmap.department?.name }}</span>
          </div>
        </div>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="overflow-y-auto max-h-[calc(90vh-140px)]">
        <div class="p-6 space-y-6">
          <!-- Basic Info -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-4 rounded-lg">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm font-medium text-blue-900">Duração Estimada</span>
              </div>
              <p class="text-2xl font-bold text-blue-600">{{ roadmap.estimated_duration_days }} dias</p>
            </div>
            
            <div class="bg-green-50 p-4 rounded-lg">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <span class="text-sm font-medium text-green-900">Total de Etapas</span>
              </div>
              <p class="text-2xl font-bold text-green-600">{{ roadmap.steps?.length || 0 }}</p>
            </div>
            
            <div class="bg-purple-50 p-4 rounded-lg">
              <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-sm font-medium text-purple-900">Criado por</span>
              </div>
              <p class="text-sm font-medium text-purple-600">{{ roadmap.created_by?.name || 'N/A' }}</p>
              <p class="text-xs text-purple-500">{{ formatDate(roadmap.created_at) }}</p>
            </div>
          </div>

          <!-- Description -->
          <div v-if="roadmap.description">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Descrição</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-gray-700 whitespace-pre-wrap">{{ roadmap.description }}</p>
            </div>
          </div>

          <!-- Steps -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Etapas do Roteiro</h3>
            
            <div v-if="!roadmap.steps || roadmap.steps.length === 0" class="text-center py-8 text-gray-500">
              <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
              </svg>
              <p>Nenhuma etapa definida</p>
            </div>
            
            <div v-else class="space-y-4">
              <div
                v-for="(step, index) in roadmap.steps"
                :key="step.id || index"
                class="border border-gray-200 rounded-lg p-4 bg-white hover:shadow-md transition-shadow"
              >
                <div class="flex justify-between items-start mb-3">
                  <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-8 h-8 bg-blue-100 text-blue-800 rounded-full flex items-center justify-center text-sm font-medium">
                      {{ index + 1 }}
                    </div>
                    <div>
                      <h4 class="font-medium text-gray-900">{{ step.title }}</h4>
                      <div class="flex items-center gap-2 mt-1">
                        <span :class="getStepTypeClass(step.type)" class="text-xs font-medium px-2 py-1 rounded">
                          {{ getStepTypeLabel(step.type) }}
                        </span>
                        <span v-if="step.is_mandatory" class="bg-red-100 text-red-800 text-xs font-medium px-2 py-1 rounded">
                          Obrigatória
                        </span>
                        <span v-if="step.estimated_hours" class="text-xs text-gray-500">
                          {{ step.estimated_hours }}h estimadas
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div v-if="step.description" class="ml-11">
                  <p class="text-gray-600 text-sm whitespace-pre-wrap">{{ step.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Statistics -->
          <div v-if="roadmap.steps && roadmap.steps.length > 0">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Estatísticas</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div class="bg-gray-50 p-3 rounded-lg text-center">
                <p class="text-sm text-gray-600">Etapas Obrigatórias</p>
                <p class="text-xl font-bold text-gray-900">{{ mandatoryStepsCount }}</p>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg text-center">
                <p class="text-sm text-gray-600">Etapas Opcionais</p>
                <p class="text-xl font-bold text-gray-900">{{ optionalStepsCount }}</p>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg text-center">
                <p class="text-sm text-gray-600">Tempo Total Estimado</p>
                <p class="text-xl font-bold text-gray-900">{{ totalEstimatedHours }}h</p>
              </div>
              <div class="bg-gray-50 p-3 rounded-lg text-center">
                <p class="text-sm text-gray-600">Tipos de Etapas</p>
                <p class="text-xl font-bold text-gray-900">{{ uniqueStepTypes }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 flex justify-end gap-3">
        <button
          @click="$emit('edit', roadmap)"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
          </svg>
          Editar Roteiro
        </button>
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
        >
          Fechar
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'

export default {
  name: 'RoadmapViewModal',
  props: {
    roadmap: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'edit'],
  setup(props) {
    const getStatusClass = (status) => {
      const classes = {
        draft: 'bg-gray-100 text-gray-800',
        active: 'bg-green-100 text-green-800',
        inactive: 'bg-red-100 text-red-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }
    
    const getStatusLabel = (status) => {
      const labels = {
        draft: 'Rascunho',
        active: 'Ativo',
        inactive: 'Inativo'
      }
      return labels[status] || status
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
    
    const formatDate = (date) => {
      if (!date) return 'N/A'
      return new Date(date).toLocaleDateString('pt-AO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    }
    
    const mandatoryStepsCount = computed(() => {
      return props.roadmap.steps?.filter(step => step.is_mandatory).length || 0
    })
    
    const optionalStepsCount = computed(() => {
      return props.roadmap.steps?.filter(step => !step.is_mandatory).length || 0
    })
    
    const totalEstimatedHours = computed(() => {
      return props.roadmap.steps?.reduce((total, step) => {
        return total + (step.estimated_hours || 0)
      }, 0) || 0
    })
    
    const uniqueStepTypes = computed(() => {
      const types = new Set(props.roadmap.steps?.map(step => step.type) || [])
      return types.size
    })
    
    return {
      getStatusClass,
      getStatusLabel,
      getStepTypeClass,
      getStepTypeLabel,
      formatDate,
      mandatoryStepsCount,
      optionalStepsCount,
      totalEstimatedHours,
      uniqueStepTypes
    }
  }
}
</script>