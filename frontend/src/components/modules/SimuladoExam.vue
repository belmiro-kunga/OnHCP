<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header com Cronômetro -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-40">
      <div class="max-w-6xl mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center gap-4">
            <h1 class="text-xl font-bold text-gray-900">{{ simulado.title }}</h1>
            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
              Questão {{ currentQuestionIndex + 1 }} de {{ simulado.questions.length }}
            </span>
          </div>
          
          <!-- Cronômetro -->
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5" :class="timeWarning ? 'text-red-500' : 'text-gray-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span class="font-mono text-lg" :class="timeWarning ? 'text-red-500 font-bold' : 'text-gray-900'">
                {{ formatTime(timeRemaining) }}
              </span>
            </div>
            
            <button
              v-if="simulado.allowSaveProgress"
              @click="saveProgress"
              :disabled="saving"
              class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors disabled:opacity-50"
            >
              {{ saving ? 'Salvando...' : 'Salvar' }}
            </button>
            
            <button
              @click="showExitModal = true"
              class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 text-sm rounded-lg transition-colors"
            >
              Sair
            </button>
          </div>
        </div>
        
        <!-- Barra de Progresso -->
        <div class="mt-4">
          <div class="flex justify-between text-sm text-gray-600 mb-1">
            <span>Progresso</span>
            <span>{{ Math.round(progress) }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-blue-600 h-2 rounded-full transition-all duration-300"
              :style="{ width: progress + '%' }"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Conteúdo Principal -->
    <div class="max-w-6xl mx-auto px-4 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Navegação de Questões -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow-sm border p-4 sticky top-24">
            <h3 class="font-semibold text-gray-900 mb-3">Navegação</h3>
            <div class="grid grid-cols-5 gap-2">
              <button
                v-for="(question, index) in simulado.questions"
                :key="index"
                @click="goToQuestion(index)"
                :disabled="!canNavigateToQuestion(index)"
                :class="getQuestionButtonClass(index)"
                class="w-8 h-8 text-sm font-medium rounded transition-colors"
              >
                {{ index + 1 }}
              </button>
            </div>
            
            <div class="mt-4 space-y-2 text-xs">
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-blue-600 rounded"></div>
                <span class="text-gray-600">Atual</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-green-500 rounded"></div>
                <span class="text-gray-600">Respondida</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-gray-300 rounded"></div>
                <span class="text-gray-600">Não respondida</span>
              </div>
              <div v-if="!simulado.allowBackNavigation" class="flex items-center gap-2">
                <div class="w-3 h-3 bg-gray-400 rounded opacity-50"></div>
                <span class="text-gray-600">Bloqueada</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Questão Atual -->
        <div class="lg:col-span-3">
          <div class="bg-white rounded-lg shadow-sm border p-6">
            <!-- Cabeçalho da Questão -->
            <div class="flex justify-between items-start mb-6">
              <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">
                  Questão {{ currentQuestionIndex + 1 }}
                </h2>
                <span class="px-2 py-1 bg-gray-100 text-gray-700 text-sm rounded">
                  {{ getQuestionTypeLabel(currentQuestion.type) }}
                </span>
              </div>
              <div class="text-sm text-gray-600">
                Peso: {{ currentQuestion.weight || 1 }} ponto(s)
              </div>
            </div>

            <!-- Enunciado -->
            <div class="mb-6">
              <div class="prose max-w-none" v-html="currentQuestion.question"></div>
              
              <!-- Imagem da questão -->
              <div v-if="currentQuestion.image" class="mt-4">
                <img 
                  :src="currentQuestion.image" 
                  :alt="`Imagem da questão ${currentQuestionIndex + 1}`"
                  class="max-w-full h-auto rounded-lg border"
                >
              </div>
            </div>

            <!-- Opções de Resposta -->
            <div class="mb-6">
              <!-- Múltipla Escolha -->
              <div v-if="currentQuestion.type === 'multiple_choice'" class="space-y-3">
                <label
                  v-for="(option, index) in currentQuestion.options"
                  :key="index"
                  class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                  :class="{
                    'border-blue-500 bg-blue-50': userAnswers[currentQuestionIndex] === option.id,
                    'border-gray-300': userAnswers[currentQuestionIndex] !== option.id
                  }"
                >
                  <input
                    type="radio"
                    :name="`question_${currentQuestionIndex}`"
                    :value="option.id"
                    v-model="userAnswers[currentQuestionIndex]"
                    class="mt-1 text-blue-600 focus:ring-blue-500"
                  >
                  <div class="flex-1">
                    <div class="font-medium text-gray-900">{{ option.label }}</div>
                    <div class="text-gray-700 mt-1" v-html="option.text"></div>
                  </div>
                </label>
              </div>

              <!-- Verdadeiro/Falso -->
              <div v-else-if="currentQuestion.type === 'true_false'" class="space-y-3">
                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                       :class="{
                         'border-blue-500 bg-blue-50': userAnswers[currentQuestionIndex] === true,
                         'border-gray-300': userAnswers[currentQuestionIndex] !== true
                       }">
                  <input
                    type="radio"
                    :name="`question_${currentQuestionIndex}`"
                    :value="true"
                    v-model="userAnswers[currentQuestionIndex]"
                    class="text-blue-600 focus:ring-blue-500"
                  >
                  <span class="font-medium text-gray-900">Verdadeiro</span>
                </label>
                
                <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                       :class="{
                         'border-blue-500 bg-blue-50': userAnswers[currentQuestionIndex] === false,
                         'border-gray-300': userAnswers[currentQuestionIndex] !== false
                       }">
                  <input
                    type="radio"
                    :name="`question_${currentQuestionIndex}`"
                    :value="false"
                    v-model="userAnswers[currentQuestionIndex]"
                    class="text-blue-600 focus:ring-blue-500"
                  >
                  <span class="font-medium text-gray-900">Falso</span>
                </label>
              </div>

              <!-- Dissertativa -->
              <div v-else-if="currentQuestion.type === 'essay'" class="space-y-3">
                <textarea
                  v-model="userAnswers[currentQuestionIndex]"
                  :placeholder="`Digite sua resposta para a questão ${currentQuestionIndex + 1}...`"
                  rows="6"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-vertical"
                ></textarea>
                <div class="text-sm text-gray-600">
                  {{ (userAnswers[currentQuestionIndex] || '').length }} caracteres
                  <span v-if="currentQuestion.maxLength">
                    / {{ currentQuestion.maxLength }} máximo
                  </span>
                </div>
              </div>
            </div>

            <!-- Navegação entre Questões -->
            <div class="flex justify-between items-center pt-6 border-t">
              <button
                @click="previousQuestion"
                :disabled="!canGoToPrevious"
                class="flex items-center gap-2 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Anterior
              </button>

              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-600">
                  {{ answeredQuestions }} de {{ simulado.questions.length }} respondidas
                </span>
              </div>

              <button
                v-if="!isLastQuestion"
                @click="nextQuestion"
                class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
              >
                Próxima
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
              </button>
              
              <button
                v-else
                @click="showFinishModal = true"
                class="flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Finalizar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Finalização -->
    <div v-if="showFinishModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Finalizar Simulado</h3>
          </div>
          
          <div class="mb-6">
            <p class="text-gray-700 mb-4">Você está prestes a finalizar o simulado. Verifique suas respostas:</p>
            
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="font-medium text-gray-900">Total de questões:</span>
                  <span class="ml-2">{{ simulado.questions.length }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-900">Respondidas:</span>
                  <span class="ml-2" :class="answeredQuestions === simulado.questions.length ? 'text-green-600' : 'text-red-600'">
                    {{ answeredQuestions }}
                  </span>
                </div>
                <div>
                  <span class="font-medium text-gray-900">Tempo restante:</span>
                  <span class="ml-2">{{ formatTime(timeRemaining) }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-900">Não respondidas:</span>
                  <span class="ml-2" :class="unansweredQuestions === 0 ? 'text-green-600' : 'text-red-600'">
                    {{ unansweredQuestions }}
                  </span>
                </div>
              </div>
            </div>
            
            <div v-if="unansweredQuestions > 0" class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
              <p class="text-yellow-800 text-sm">
                <strong>Atenção:</strong> Você possui {{ unansweredQuestions }} questão(ões) não respondida(s). 
                Elas serão consideradas como incorretas.
              </p>
            </div>
          </div>
          
          <div class="flex justify-end gap-3">
            <button
              @click="showFinishModal = false"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Continuar Respondendo
            </button>
            <button
              @click="finishExam"
              :disabled="submitting"
              class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50"
            >
              {{ submitting ? 'Enviando...' : 'Finalizar Simulado' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Saída -->
    <div v-if="showExitModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-4">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Sair do Simulado</h3>
          </div>
          
          <div class="mb-6">
            <p class="text-gray-700 mb-4">Você tem certeza que deseja sair do simulado?</p>
            
            <div v-if="simulado.allowSaveProgress" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
              <p class="text-blue-800 text-sm">
                <strong>Progresso será salvo:</strong> Você pode continuar de onde parou mais tarde.
              </p>
            </div>
            
            <div v-else class="p-3 bg-red-50 border border-red-200 rounded-lg">
              <p class="text-red-800 text-sm">
                <strong>Progresso será perdido:</strong> Você terá que começar novamente.
              </p>
            </div>
          </div>
          
          <div class="flex justify-end gap-3">
            <button
              @click="showExitModal = false"
              class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
              Continuar Simulado
            </button>
            <button
              @click="exitExam"
              class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
            >
              Sair
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSimulado } from '../../composables/useSimulado'

export default {
  name: 'SimuladoExam',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const { getSimuladoById, submitSimulado } = useSimulado()

    // Estado
    const simuladoId = route.params.id
    const simulado = ref(null)
    const currentQuestionIndex = ref(0)
    const userAnswers = ref({})
    const timeRemaining = ref(0)
    const timer = ref(null)
    const showFinishModal = ref(false)
    const showExitModal = ref(false)
    const submitting = ref(false)
    const saving = ref(false)
    const startTime = ref(null)

    // Computed
    const currentQuestion = computed(() => {
      return simulado.value?.questions[currentQuestionIndex.value]
    })

    const isLastQuestion = computed(() => {
      return currentQuestionIndex.value === simulado.value?.questions.length - 1
    })

    const canGoToPrevious = computed(() => {
      return simulado.value?.allowBackNavigation && currentQuestionIndex.value > 0
    })

    const progress = computed(() => {
      if (!simulado.value) return 0
      return ((currentQuestionIndex.value + 1) / simulado.value.questions.length) * 100
    })

    const answeredQuestions = computed(() => {
      return Object.keys(userAnswers.value).filter(key => {
        const answer = userAnswers.value[key]
        return answer !== null && answer !== undefined && answer !== ''
      }).length
    })

    const unansweredQuestions = computed(() => {
      return simulado.value?.questions.length - answeredQuestions.value
    })

    const timeWarning = computed(() => {
      return timeRemaining.value <= 300 // 5 minutos
    })

    // Métodos
    const loadSimulado = async () => {
      simulado.value = getSimuladoById(simuladoId)
      if (!simulado.value) {
        router.push('/simulados')
        return
      }

      // Carregar progresso salvo se existir
      loadSavedProgress()
      
      // Iniciar cronômetro
      startTimer()
    }

    const loadSavedProgress = () => {
      const savedProgress = localStorage.getItem(`simulado_progress_${simuladoId}`)
      if (savedProgress) {
        const progress = JSON.parse(savedProgress)
        userAnswers.value = progress.answers || {}
        currentQuestionIndex.value = progress.currentQuestion || 0
        timeRemaining.value = progress.timeRemaining || simulado.value.duration
        startTime.value = progress.startTime || Date.now()
      } else {
        timeRemaining.value = simulado.value.duration
        startTime.value = Date.now()
      }
    }

    const startTimer = () => {
      timer.value = setInterval(() => {
        timeRemaining.value--
        
        if (timeRemaining.value <= 0) {
          finishExam(true) // Auto-submit quando tempo acabar
        }
      }, 1000)
    }

    const formatTime = (seconds) => {
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      const secs = seconds % 60
      
      if (hours > 0) {
        return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
      }
      return `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
    }

    const getQuestionTypeLabel = (type) => {
      const labels = {
        'multiple_choice': 'Múltipla Escolha',
        'true_false': 'Verdadeiro/Falso',
        'essay': 'Dissertativa'
      }
      return labels[type] || type
    }

    const getQuestionButtonClass = (index) => {
      const isAnswered = userAnswers.value[index] !== undefined && userAnswers.value[index] !== null && userAnswers.value[index] !== ''
      const isCurrent = index === currentQuestionIndex.value
      const canNavigate = canNavigateToQuestion(index)
      
      if (isCurrent) {
        return 'bg-blue-600 text-white'
      } else if (isAnswered) {
        return 'bg-green-500 text-white hover:bg-green-600'
      } else if (canNavigate) {
        return 'bg-gray-300 text-gray-700 hover:bg-gray-400'
      } else {
        return 'bg-gray-200 text-gray-400 cursor-not-allowed'
      }
    }

    const canNavigateToQuestion = (index) => {
      if (simulado.value?.allowBackNavigation) {
        return true
      }
      return index <= currentQuestionIndex.value
    }

    const goToQuestion = (index) => {
      if (canNavigateToQuestion(index)) {
        currentQuestionIndex.value = index
      }
    }

    const nextQuestion = () => {
      if (!isLastQuestion.value) {
        currentQuestionIndex.value++
      }
    }

    const previousQuestion = () => {
      if (canGoToPrevious.value) {
        currentQuestionIndex.value--
      }
    }

    const saveProgress = async () => {
      if (!simulado.value?.allowSaveProgress) return
      
      saving.value = true
      
      const progress = {
        answers: userAnswers.value,
        currentQuestion: currentQuestionIndex.value,
        timeRemaining: timeRemaining.value,
        startTime: startTime.value,
        lastSaved: Date.now()
      }
      
      localStorage.setItem(`simulado_progress_${simuladoId}`, JSON.stringify(progress))
      
      setTimeout(() => {
        saving.value = false
      }, 1000)
    }

    const finishExam = async (autoSubmit = false) => {
      if (submitting.value) return
      
      submitting.value = true
      
      try {
        const duration = simulado.value.duration - timeRemaining.value
        
        const result = await submitSimulado(simuladoId, {
          answers: userAnswers.value,
          duration,
          startTime: startTime.value,
          endTime: Date.now(),
          autoSubmit
        })
        
        // Limpar progresso salvo
        localStorage.removeItem(`simulado_progress_${simuladoId}`)
        
        // Parar cronômetro
        if (timer.value) {
          clearInterval(timer.value)
        }
        
        // Redirecionar para resultado
        router.push(`/simulados/${simuladoId}/result/${result.attemptId}`)
        
      } catch (error) {
        console.error('Erro ao enviar simulado:', error)
        submitting.value = false
      }
    }

    const exitExam = () => {
      if (simulado.value?.allowSaveProgress) {
        saveProgress()
      } else {
        localStorage.removeItem(`simulado_progress_${simuladoId}`)
      }
      
      if (timer.value) {
        clearInterval(timer.value)
      }
      
      router.push('/simulados')
    }

    // Auto-save a cada 30 segundos
    const autoSaveInterval = ref(null)
    const startAutoSave = () => {
      if (simulado.value?.allowSaveProgress) {
        autoSaveInterval.value = setInterval(() => {
          saveProgress()
        }, 30000) // 30 segundos
      }
    }

    // Lifecycle
    onMounted(() => {
      loadSimulado()
      startAutoSave()
      
      // Prevenir saída acidental
      window.addEventListener('beforeunload', (e) => {
        e.preventDefault()
        e.returnValue = ''
      })
    })

    onUnmounted(() => {
      if (timer.value) {
        clearInterval(timer.value)
      }
      if (autoSaveInterval.value) {
        clearInterval(autoSaveInterval.value)
      }
      window.removeEventListener('beforeunload', () => {})
    })

    // Watch para auto-save quando resposta muda
    watch(userAnswers, () => {
      if (simulado.value?.allowSaveProgress) {
        // Debounce save
        clearTimeout(window.autoSaveTimeout)
        window.autoSaveTimeout = setTimeout(() => {
          saveProgress()
        }, 2000)
      }
    }, { deep: true })

    return {
      // Estado
      simulado,
      currentQuestionIndex,
      userAnswers,
      timeRemaining,
      showFinishModal,
      showExitModal,
      submitting,
      saving,
      
      // Computed
      currentQuestion,
      isLastQuestion,
      canGoToPrevious,
      progress,
      answeredQuestions,
      unansweredQuestions,
      timeWarning,
      
      // Métodos
      formatTime,
      getQuestionTypeLabel,
      getQuestionButtonClass,
      canNavigateToQuestion,
      goToQuestion,
      nextQuestion,
      previousQuestion,
      saveProgress,
      finishExam,
      exitExam
    }
  }
}
</script>

<style scoped>
.prose {
  max-width: none;
}

.prose h1, .prose h2, .prose h3, .prose h4, .prose h5, .prose h6 {
  margin-top: 0;
  margin-bottom: 0.5rem;
}

.prose p {
  margin-bottom: 1rem;
}

.prose ul, .prose ol {
  margin-bottom: 1rem;
  padding-left: 1.5rem;
}

.prose li {
  margin-bottom: 0.25rem;
}
</style>