<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="flex justify-between items-center p-6 border-b">
        <div>
          <h2 class="text-xl font-bold text-gray-900">{{ simulado.title }}</h2>
          <p class="text-gray-600 text-sm mt-1">Regras e Instruções</p>
        </div>
        <button
          @click="$emit('close')"
          class="text-gray-400 hover:text-gray-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="p-6">
        <!-- Descrição -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-2">Sobre o Simulado</h3>
          <p class="text-gray-700">{{ simulado.description }}</p>
        </div>

        <!-- Informações Principais -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <h4 class="font-semibold text-blue-900">Duração</h4>
            </div>
            <p class="text-blue-800">{{ formatDuration(simulado.duration) }}</p>
            <p class="text-blue-600 text-sm mt-1">Cronômetro será iniciado automaticamente</p>
          </div>

          <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <h4 class="font-semibold text-green-900">Questões</h4>
            </div>
            <p class="text-green-800">{{ simulado.questions.length }} questões</p>
            <p class="text-green-600 text-sm mt-1">{{ getQuestionTypes() }}</p>
          </div>

          <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              <h4 class="font-semibold text-yellow-900">Nota Mínima</h4>
            </div>
            <p class="text-yellow-800">{{ simulado.minScore }}%</p>
            <p class="text-yellow-600 text-sm mt-1">Para aprovação</p>
          </div>

          <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <h4 class="font-semibold text-purple-900">Tentativas</h4>
            </div>
            <p class="text-purple-800">{{ getRemainingAttempts() }}/{{ simulado.maxAttempts }}</p>
            <p class="text-purple-600 text-sm mt-1">{{ getRemainingAttempts() > 0 ? 'Tentativas restantes' : 'Limite atingido' }}</p>
          </div>
        </div>

        <!-- Regras Específicas -->
        <div class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-3">Regras Importantes</h3>
          <div class="space-y-3">
            <div class="flex items-start gap-3">
              <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
              <div>
                <p class="text-gray-700"><strong>Cronômetro:</strong> O tempo começará a contar assim que você iniciar o simulado.</p>
              </div>
            </div>
            
            <div class="flex items-start gap-3">
              <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
              <div>
                <p class="text-gray-700"><strong>Navegação:</strong> {{ getNavigationRule() }}</p>
              </div>
            </div>
            
            <div class="flex items-start gap-3">
              <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
              <div>
                <p class="text-gray-700"><strong>Salvamento:</strong> {{ getSaveRule() }}</p>
              </div>
            </div>
            
            <div class="flex items-start gap-3">
              <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
              <div>
                <p class="text-gray-700"><strong>Finalização:</strong> O simulado será enviado automaticamente quando o tempo esgotar.</p>
              </div>
            </div>
            
            <div class="flex items-start gap-3">
              <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
              <div>
                <p class="text-gray-700"><strong>Resultado:</strong> {{ getResultRule() }}</p>
              </div>
            </div>
            
            <div v-if="simulado.type === 'obrigatorio'" class="flex items-start gap-3">
              <div class="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></div>
              <div>
                <p class="text-gray-700"><strong>Obrigatório:</strong> Este simulado é obrigatório e deve ser concluído com aprovação.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Certificado -->
        <div v-if="simulado.certificate" class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4 mb-6">
          <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
            <h4 class="font-semibold text-yellow-900">Certificado Disponível</h4>
          </div>
          <p class="text-yellow-800">Ao ser aprovado neste simulado, você receberá automaticamente um certificado de conclusão.</p>
        </div>

        <!-- Progresso Salvo -->
        <div v-if="hasProgress" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
          <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h4 class="font-semibold text-blue-900">Progresso Salvo</h4>
          </div>
          <p class="text-blue-800">Você possui um progresso salvo para este simulado. Ao continuar, você retomará de onde parou.</p>
        </div>

        <!-- Última Tentativa -->
        <div v-if="lastAttempt" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
          <div class="flex items-center gap-2 mb-2">
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <h4 class="font-semibold text-gray-900">Última Tentativa</h4>
          </div>
          <div class="flex justify-between items-center">
            <div>
              <p class="text-gray-700">{{ formatDate(lastAttempt.date) }}</p>
              <p class="text-sm text-gray-600">{{ lastAttempt.duration ? formatDuration(lastAttempt.duration) : 'Não finalizado' }}</p>
            </div>
            <div class="text-right">
              <p class="text-lg font-bold" :class="lastAttempt.passed ? 'text-green-600' : 'text-red-600'">
                {{ lastAttempt.score }}%
              </p>
              <p class="text-sm" :class="lastAttempt.passed ? 'text-green-600' : 'text-red-600'">
                {{ lastAttempt.passed ? 'Aprovado' : 'Reprovado' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex justify-end gap-3 p-6 border-t bg-gray-50">
        <button
          @click="$emit('close')"
          class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Cancelar
        </button>
        <button
          @click="startSimulado"
          :disabled="!canStart"
          :class="[
            'px-6 py-2 rounded-lg font-medium transition-colors',
            canStart
              ? 'bg-blue-600 hover:bg-blue-700 text-white'
              : 'bg-gray-300 text-gray-500 cursor-not-allowed'
          ]"
        >
          {{ hasProgress ? 'Continuar Simulado' : 'Iniciar Simulado' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useSimulado } from '../../composables/useSimulado'

export default {
  name: 'SimuladoRules',
  props: {
    simulado: {
      type: Object,
      required: true
    }
  },
  emits: ['close', 'start'],
  setup(props, { emit }) {
    const { getLastAttempt, getSimuladoAttempts, canAttemptSimulado } = useSimulado()

    // Computed
    const lastAttempt = computed(() => getLastAttempt(props.simulado.id))
    const hasProgress = computed(() => {
      return localStorage.getItem(`simulado_progress_${props.simulado.id}`) !== null
    })
    const canStart = computed(() => canAttemptSimulado(props.simulado))

    // Métodos
    const formatDuration = (seconds) => {
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      
      if (hours > 0) {
        return `${hours}h ${minutes}min`
      }
      return `${minutes} minutos`
    }

    const formatDate = (dateString) => {
      const date = new Date(dateString)
      return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const getRemainingAttempts = () => {
      const attempts = getSimuladoAttempts(props.simulado.id)
      return props.simulado.maxAttempts - attempts.length
    }

    const getQuestionTypes = () => {
      const types = [...new Set(props.simulado.questions.map(q => q.type))]
      const typeLabels = {
        'multiple_choice': 'Múltipla escolha',
        'true_false': 'Verdadeiro/Falso',
        'essay': 'Dissertativa'
      }
      return types.map(type => typeLabels[type] || type).join(', ')
    }

    const getNavigationRule = () => {
      return props.simulado.allowBackNavigation
        ? 'Você pode navegar livremente entre as questões.'
        : 'Você não pode voltar para questões anteriores após avançar.'
    }

    const getSaveRule = () => {
      return props.simulado.allowSaveProgress
        ? 'Seu progresso será salvo automaticamente. Você pode pausar e continuar depois.'
        : 'O progresso não será salvo. Você deve completar o simulado em uma única sessão.'
    }

    const getResultRule = () => {
      return props.simulado.showFeedback
        ? 'Você verá o resultado imediatamente com feedback detalhado das questões.'
        : 'Você verá apenas a nota final sem feedback das questões.'
    }

    const startSimulado = () => {
      if (canStart.value) {
        emit('start', props.simulado)
      }
    }

    return {
      // Computed
      lastAttempt,
      hasProgress,
      canStart,
      
      // Métodos
      formatDuration,
      formatDate,
      getRemainingAttempts,
      getQuestionTypes,
      getNavigationRule,
      getSaveRule,
      getResultRule,
      startSimulado
    }
  }
}
</script>