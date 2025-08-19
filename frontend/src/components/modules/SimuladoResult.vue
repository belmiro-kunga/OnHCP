<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4">
      <!-- Header com Resultado -->
      <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="text-center">
          <!-- Ícone de Status -->
          <div class="mb-4">
            <div v-if="result.passed" class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
              <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div v-else class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto">
              <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </div>
          </div>

          <!-- Título e Status -->
          <h1 class="text-2xl font-bold mb-2" :class="result.passed ? 'text-green-600' : 'text-red-600'">
            {{ result.passed ? 'Parabéns! Você foi aprovado!' : 'Não foi dessa vez!' }}
          </h1>
          <p class="text-gray-600 mb-6">{{ simulado.title }}</p>

          <!-- Nota Principal -->
          <div class="mb-6">
            <div class="text-6xl font-bold mb-2" :class="result.passed ? 'text-green-600' : 'text-red-600'">
              {{ result.score }}%
            </div>
            <p class="text-gray-600">
              Nota mínima para aprovação: {{ simulado.minScore }}%
            </p>
          </div>

          <!-- Estatísticas Rápidas -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-blue-600">{{ result.correctAnswers }}</div>
              <div class="text-sm text-blue-800">Acertos</div>
            </div>
            <div class="bg-red-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-red-600">{{ result.wrongAnswers }}</div>
              <div class="text-sm text-red-800">Erros</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-gray-600">{{ formatDuration(result.duration) }}</div>
              <div class="text-sm text-gray-800">Tempo gasto</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4">
              <div class="text-2xl font-bold text-purple-600">{{ result.attemptNumber }}</div>
              <div class="text-sm text-purple-800">Tentativa</div>
            </div>
          </div>

          <!-- Certificado -->
          <div v-if="result.passed && simulado.certificate" class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <div class="flex items-center justify-center gap-3 mb-4">
              <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
              </svg>
              <h3 class="text-lg font-semibold text-yellow-900">Certificado Disponível!</h3>
            </div>
            <p class="text-yellow-800 mb-4">Você foi aprovado e pode baixar seu certificado de conclusão.</p>
            <button
              @click="downloadCertificate"
              :disabled="downloadingCertificate"
              class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg font-medium transition-colors disabled:opacity-50"
            >
              {{ downloadingCertificate ? 'Gerando...' : 'Baixar Certificado' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Navegação de Abas -->
      <div class="bg-white rounded-lg shadow-sm border mb-6">
        <div class="border-b">
          <nav class="flex space-x-8 px-6">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                'py-4 px-1 border-b-2 font-medium text-sm transition-colors',
                activeTab === tab.id
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              {{ tab.label }}
            </button>
          </nav>
        </div>

        <!-- Conteúdo das Abas -->
        <div class="p-6">
          <!-- Aba Resumo -->
          <div v-if="activeTab === 'summary'">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Resumo da Avaliação</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Informações Gerais -->
              <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <h4 class="font-medium text-gray-900 mb-3">Informações Gerais</h4>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Data de realização:</span>
                      <span class="font-medium">{{ formatDate(result.date) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Tempo limite:</span>
                      <span class="font-medium">{{ formatDuration(simulado.duration) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Tempo utilizado:</span>
                      <span class="font-medium">{{ formatDuration(result.duration) }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Eficiência:</span>
                      <span class="font-medium">{{ Math.round((result.duration / simulado.duration) * 100) }}%</span>
                    </div>
                  </div>
                </div>

                <!-- Performance por Tipo -->
                <div class="bg-gray-50 rounded-lg p-4">
                  <h4 class="font-medium text-gray-900 mb-3">Performance por Tipo</h4>
                  <div class="space-y-3">
                    <div v-for="type in questionTypes" :key="type.type" class="flex items-center justify-between">
                      <span class="text-sm text-gray-600">{{ type.label }}</span>
                      <div class="flex items-center gap-2">
                        <div class="w-20 bg-gray-200 rounded-full h-2">
                          <div 
                            class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                            :style="{ width: type.percentage + '%' }"
                          ></div>
                        </div>
                        <span class="text-sm font-medium w-12 text-right">{{ type.percentage }}%</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Gráfico de Performance -->
              <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <h4 class="font-medium text-gray-900 mb-3">Distribuição de Respostas</h4>
                  <div class="relative">
                    <!-- Gráfico de Pizza Simples -->
                    <div class="w-32 h-32 mx-auto relative">
                      <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 36 36">
                        <!-- Fundo -->
                        <path
                          class="text-gray-200"
                          stroke="currentColor"
                          stroke-width="3"
                          fill="transparent"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        />
                        <!-- Acertos -->
                        <path
                          class="text-green-500"
                          stroke="currentColor"
                          stroke-width="3"
                          fill="transparent"
                          :stroke-dasharray="`${(result.correctAnswers / simulado.questions.length) * 100}, 100`"
                          d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                        />
                      </svg>
                      <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-lg font-bold text-gray-900">{{ result.score }}%</span>
                      </div>
                    </div>
                    
                    <div class="mt-4 space-y-2">
                      <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                          <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                          <span>Acertos</span>
                        </div>
                        <span class="font-medium">{{ result.correctAnswers }}/{{ simulado.questions.length }}</span>
                      </div>
                      <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center gap-2">
                          <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                          <span>Erros</span>
                        </div>
                        <span class="font-medium">{{ result.wrongAnswers }}/{{ simulado.questions.length }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Aba Questões -->
          <div v-else-if="activeTab === 'questions'">
            <div class="flex justify-between items-center mb-4">
              <h3 class="text-lg font-semibold text-gray-900">Revisão das Questões</h3>
              <div class="flex items-center gap-2">
                <label class="text-sm text-gray-600">Filtrar:</label>
                <select v-model="questionFilter" class="text-sm border border-gray-300 rounded px-2 py-1">
                  <option value="all">Todas</option>
                  <option value="correct">Acertos</option>
                  <option value="wrong">Erros</option>
                  <option value="unanswered">Não respondidas</option>
                </select>
              </div>
            </div>

            <div class="space-y-6">
              <div
                v-for="(question, index) in filteredQuestions"
                :key="index"
                class="border rounded-lg p-6"
                :class="getQuestionCardClass(question)"
              >
                <!-- Cabeçalho da Questão -->
                <div class="flex justify-between items-start mb-4">
                  <div class="flex items-center gap-3">
                    <span class="text-lg font-semibold text-gray-900">Questão {{ question.originalIndex + 1 }}</span>
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-sm rounded">
                      {{ getQuestionTypeLabel(question.type) }}
                    </span>
                    <div class="flex items-center gap-1">
                      <svg v-if="question.isCorrect" class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      <svg v-else-if="question.userAnswer !== null" class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                      <svg v-else class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                  </div>
                  <span class="text-sm text-gray-600">Peso: {{ question.weight || 1 }}</span>
                </div>

                <!-- Enunciado -->
                <div class="mb-4">
                  <div class="prose max-w-none text-gray-700" v-html="question.question"></div>
                  <div v-if="question.image" class="mt-3">
                    <img :src="question.image" :alt="`Imagem da questão ${question.originalIndex + 1}`" class="max-w-full h-auto rounded border">
                  </div>
                </div>

                <!-- Respostas -->
                <div class="space-y-3">
                  <!-- Múltipla Escolha -->
                  <div v-if="question.type === 'multiple_choice'">
                    <div
                      v-for="option in question.options"
                      :key="option.id"
                      class="flex items-start gap-3 p-3 rounded-lg border"
                      :class="getOptionClass(question, option)"
                    >
                      <div class="flex items-center gap-2 mt-1">
                        <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center"
                             :class="getOptionIconClass(question, option)">
                          <div v-if="isOptionSelected(question, option) || isCorrectOption(question, option)" 
                               class="w-2 h-2 rounded-full bg-current"></div>
                        </div>
                        <span class="font-medium text-sm">{{ option.label }}</span>
                      </div>
                      <div class="flex-1">
                        <div class="text-gray-700" v-html="option.text"></div>
                      </div>
                      <div class="flex items-center gap-1">
                        <svg v-if="isCorrectOption(question, option)" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg v-else-if="isOptionSelected(question, option) && !question.isCorrect" class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                      </div>
                    </div>
                  </div>

                  <!-- Verdadeiro/Falso -->
                  <div v-else-if="question.type === 'true_false'">
                    <div class="grid grid-cols-2 gap-3">
                      <div class="p-3 rounded-lg border" :class="getTrueFalseClass(question, true)">
                        <div class="flex items-center justify-between">
                          <span class="font-medium">Verdadeiro</span>
                          <div class="flex items-center gap-1">
                            <div v-if="question.userAnswer === true" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                            <svg v-if="question.correctAnswer === true" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                          </div>
                        </div>
                      </div>
                      <div class="p-3 rounded-lg border" :class="getTrueFalseClass(question, false)">
                        <div class="flex items-center justify-between">
                          <span class="font-medium">Falso</span>
                          <div class="flex items-center gap-1">
                            <div v-if="question.userAnswer === false" class="w-3 h-3 bg-blue-600 rounded-full"></div>
                            <svg v-if="question.correctAnswer === false" class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Dissertativa -->
                  <div v-else-if="question.type === 'essay'">
                    <div class="space-y-3">
                      <div v-if="question.userAnswer" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h5 class="font-medium text-blue-900 mb-2">Sua Resposta:</h5>
                        <p class="text-blue-800 whitespace-pre-wrap">{{ question.userAnswer }}</p>
                      </div>
                      <div v-else class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-gray-600 italic">Questão não respondida</p>
                      </div>
                      
                      <div v-if="question.modelAnswer && simulado.showFeedback" class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h5 class="font-medium text-green-900 mb-2">Resposta Modelo:</h5>
                        <p class="text-green-800 whitespace-pre-wrap">{{ question.modelAnswer }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Feedback -->
                <div v-if="question.feedback && simulado.showFeedback" class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                  <h5 class="font-medium text-yellow-900 mb-2">Explicação:</h5>
                  <div class="text-yellow-800" v-html="question.feedback"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- Aba Histórico -->
          <div v-else-if="activeTab === 'history'">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Histórico de Tentativas</h3>
            
            <div class="space-y-4">
              <div
                v-for="(attempt, index) in simuladoHistory"
                :key="attempt.id"
                class="border rounded-lg p-4"
                :class="attempt.id === result.id ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
              >
                <div class="flex justify-between items-start">
                  <div>
                    <div class="flex items-center gap-3 mb-2">
                      <span class="font-medium text-gray-900">Tentativa {{ index + 1 }}</span>
                      <span v-if="attempt.id === result.id" class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded">
                        Atual
                      </span>
                      <span :class="attempt.passed ? 'text-green-600' : 'text-red-600'" class="font-medium">
                        {{ attempt.passed ? 'Aprovado' : 'Reprovado' }}
                      </span>
                    </div>
                    <div class="text-sm text-gray-600">
                      {{ formatDate(attempt.date) }}
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-2xl font-bold" :class="attempt.passed ? 'text-green-600' : 'text-red-600'">
                      {{ attempt.score }}%
                    </div>
                    <div class="text-sm text-gray-600">
                      {{ formatDuration(attempt.duration) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Ações -->
      <div class="flex justify-center gap-4">
        <button
          @click="goToSimulados"
          class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
        >
          Voltar aos Simulados
        </button>
        
        <button
          v-if="canRetry"
          @click="retrySimulado"
          class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Tentar Novamente
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useSimulado } from '../../composables/useSimulado'

export default {
  name: 'SimuladoResult',
  setup() {
    const route = useRoute()
    const router = useRouter()
    const { getSimuladoById, getSimuladoResult, getSimuladoAttempts, canAttemptSimulado } = useSimulado()

    // Estado
    const simuladoId = route.params.id
    const attemptId = route.params.attemptId
    const simulado = ref(null)
    const result = ref(null)
    const activeTab = ref('summary')
    const questionFilter = ref('all')
    const downloadingCertificate = ref(false)

    // Computed
    const tabs = computed(() => [
      { id: 'summary', label: 'Resumo' },
      { id: 'questions', label: 'Questões' },
      { id: 'history', label: 'Histórico' }
    ])

    const simuladoHistory = computed(() => {
      return getSimuladoAttempts(simuladoId).sort((a, b) => new Date(b.date) - new Date(a.date))
    })

    const canRetry = computed(() => {
      return simulado.value && canAttemptSimulado(simulado.value)
    })

    const questionTypes = computed(() => {
      if (!simulado.value || !result.value) return []
      
      const types = {}
      simulado.value.questions.forEach(question => {
        if (!types[question.type]) {
          types[question.type] = { correct: 0, total: 0 }
        }
        types[question.type].total++
        
        const userAnswer = result.value.answers[question.id]
        if (userAnswer === question.correctAnswer) {
          types[question.type].correct++
        }
      })
      
      return Object.entries(types).map(([type, data]) => ({
        type,
        label: getQuestionTypeLabel(type),
        percentage: Math.round((data.correct / data.total) * 100)
      }))
    })

    const filteredQuestions = computed(() => {
      if (!simulado.value || !result.value) return []
      
      const questions = simulado.value.questions.map((question, index) => {
        const userAnswer = result.value.answers[question.id]
        const isCorrect = userAnswer === question.correctAnswer
        
        return {
          ...question,
          originalIndex: index,
          userAnswer,
          isCorrect,
          isAnswered: userAnswer !== null && userAnswer !== undefined && userAnswer !== ''
        }
      })
      
      switch (questionFilter.value) {
        case 'correct':
          return questions.filter(q => q.isCorrect)
        case 'wrong':
          return questions.filter(q => !q.isCorrect && q.isAnswered)
        case 'unanswered':
          return questions.filter(q => !q.isAnswered)
        default:
          return questions
      }
    })

    // Métodos
    const loadData = async () => {
      simulado.value = getSimuladoById(simuladoId)
      result.value = getSimuladoResult(simuladoId, attemptId)
      
      if (!simulado.value || !result.value) {
        router.push('/simulados')
      }
    }

    const formatDuration = (seconds) => {
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      const secs = seconds % 60
      
      if (hours > 0) {
        return `${hours}h ${minutes}min ${secs}s`
      } else if (minutes > 0) {
        return `${minutes}min ${secs}s`
      }
      return `${secs}s`
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

    const getQuestionTypeLabel = (type) => {
      const labels = {
        'multiple_choice': 'Múltipla Escolha',
        'true_false': 'Verdadeiro/Falso',
        'essay': 'Dissertativa'
      }
      return labels[type] || type
    }

    const getQuestionCardClass = (question) => {
      if (question.isCorrect) {
        return 'border-green-200 bg-green-50'
      } else if (question.isAnswered) {
        return 'border-red-200 bg-red-50'
      }
      return 'border-gray-200 bg-gray-50'
    }

    const getOptionClass = (question, option) => {
      const isSelected = isOptionSelected(question, option)
      const isCorrect = isCorrectOption(question, option)
      
      if (isCorrect) {
        return 'border-green-300 bg-green-50'
      } else if (isSelected && !question.isCorrect) {
        return 'border-red-300 bg-red-50'
      }
      return 'border-gray-200'
    }

    const getOptionIconClass = (question, option) => {
      const isSelected = isOptionSelected(question, option)
      const isCorrect = isCorrectOption(question, option)
      
      if (isCorrect) {
        return 'border-green-500 text-green-500'
      } else if (isSelected && !question.isCorrect) {
        return 'border-red-500 text-red-500'
      } else if (isSelected) {
        return 'border-blue-500 text-blue-500'
      }
      return 'border-gray-300 text-gray-300'
    }

    const getTrueFalseClass = (question, value) => {
      const isSelected = question.userAnswer === value
      const isCorrect = question.correctAnswer === value
      
      if (isCorrect) {
        return 'border-green-300 bg-green-50'
      } else if (isSelected && !question.isCorrect) {
        return 'border-red-300 bg-red-50'
      }
      return 'border-gray-200'
    }

    const isOptionSelected = (question, option) => {
      return question.userAnswer === option.id
    }

    const isCorrectOption = (question, option) => {
      return question.correctAnswer === option.id
    }

    const downloadCertificate = async () => {
      downloadingCertificate.value = true
      
      try {
        // Simular download do certificado
        await new Promise(resolve => setTimeout(resolve, 2000))
        
        // Aqui você implementaria a lógica real de download
        console.log('Baixando certificado...')
        
      } catch (error) {
        console.error('Erro ao baixar certificado:', error)
      } finally {
        downloadingCertificate.value = false
      }
    }

    const goToSimulados = () => {
      router.push('/simulados')
    }

    const retrySimulado = () => {
      router.push(`/simulados/${simuladoId}/exam`)
    }

    // Lifecycle
    onMounted(() => {
      loadData()
    })

    return {
      // Estado
      simulado,
      result,
      activeTab,
      questionFilter,
      downloadingCertificate,
      
      // Computed
      tabs,
      simuladoHistory,
      canRetry,
      questionTypes,
      filteredQuestions,
      
      // Métodos
      formatDuration,
      formatDate,
      getQuestionTypeLabel,
      getQuestionCardClass,
      getOptionClass,
      getOptionIconClass,
      getTrueFalseClass,
      isOptionSelected,
      isCorrectOption,
      downloadCertificate,
      goToSimulados,
      retrySimulado
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