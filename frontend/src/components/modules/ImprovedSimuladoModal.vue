<template>
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
    <div class="w-full max-w-4xl bg-white rounded-xl shadow-2xl border border-gray-200 max-h-[90vh] overflow-hidden">
      <!-- Header -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-xl font-semibold text-gray-900">Criar Novo Simulado</h3>
            <p class="text-sm text-gray-600 mt-1">Configure as informações básicas e adicione questões</p>
          </div>
          <button 
            @click="$emit('close')"
            class="p-2 hover:bg-white/50 rounded-lg transition-colors group"
          >
            <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>

      <!-- Progress Steps -->
      <div class="px-6 py-3 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center space-x-4">
          <div class="flex items-center">
            <div :class="currentStep >= 1 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'" 
                 class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
              1
            </div>
            <span class="ml-2 text-sm font-medium" :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-500'">Informações Básicas</span>
          </div>
          <div class="flex-1 h-px bg-gray-300"></div>
          <div class="flex items-center">
            <div :class="currentStep >= 2 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'" 
                 class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
              2
            </div>
            <span class="ml-2 text-sm font-medium" :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-500'">Questões</span>
          </div>
          <div class="flex-1 h-px bg-gray-300"></div>
          <div class="flex items-center">
            <div :class="currentStep >= 3 ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-600'" 
                 class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">
              3
            </div>
            <span class="ml-2 text-sm font-medium" :class="currentStep >= 3 ? 'text-blue-600' : 'text-gray-500'">Revisão</span>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="overflow-y-auto" style="max-height: calc(90vh - 200px);">
        <!-- Step 1: Basic Information -->
        <div v-if="currentStep === 1" class="p-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column -->
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Título do Simulado *
                </label>
                <input 
                  v-model="form.title"
                  type="text"
                  placeholder="Ex: Simulado de Segurança no Trabalho"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.title }"
                >
                <p v-if="errors.title" class="mt-1 text-sm text-red-600">{{ errors.title }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Descrição *
                </label>
                <textarea 
                  v-model="form.description"
                  rows="4"
                  placeholder="Descreva o objetivo e conteúdo do simulado..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none"
                  :class="{ 'border-red-300 focus:ring-red-500 focus:border-red-500': errors.description }"
                ></textarea>
                <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo *
                  </label>
                  <select 
                    v-model="form.type"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  >
                    <option value="obrigatorio">Obrigatório</option>
                    <option value="pratica">Prática</option>
                    <option value="certificacao">Certificação</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Categoria
                  </label>
                  <select 
                    v-model="form.category_id"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  >
                    <option value="">Sem categoria</option>
                    <option value="1">Segurança</option>
                    <option value="2">Qualidade</option>
                    <option value="3">Compliance</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-4">
              <div class="bg-blue-50 rounded-lg p-4">
                <h4 class="font-medium text-blue-900 mb-3">Configurações de Tempo e Tentativas</h4>
                
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-blue-800 mb-2">
                      Duração (minutos) *
                    </label>
                    <input 
                      v-model.number="form.duration"
                      type="number"
                      min="1"
                      max="300"
                      class="w-full px-3 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                    >
                    <p class="text-xs text-blue-600 mt-1">{{ formatDuration(form.duration * 60) }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-blue-800 mb-2">
                      Nota Mínima (%) *
                    </label>
                    <input 
                      v-model.number="form.min_score"
                      type="number"
                      min="0"
                      max="100"
                      class="w-full px-3 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                    >
                  </div>

                  <div class="col-span-2">
                    <label class="block text-sm font-medium text-blue-800 mb-2">
                      Máximo de Tentativas *
                    </label>
                    <input 
                      v-model.number="form.max_attempts"
                      type="number"
                      min="1"
                      max="10"
                      class="w-full px-3 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                    >
                  </div>
                </div>
              </div>

              <div class="bg-green-50 rounded-lg p-4">
                <h4 class="font-medium text-green-900 mb-3">Opções de Navegação e Feedback</h4>
                
                <div class="space-y-3">
                  <label class="flex items-center">
                    <input 
                      v-model="form.allow_navigation"
                      type="checkbox"
                      class="w-4 h-4 text-green-600 border-green-300 rounded focus:ring-green-500"
                    >
                    <span class="ml-3 text-sm text-green-800">
                      <strong>Navegação livre</strong>
                      <br>
                      <span class="text-green-600">Permite voltar às questões anteriores</span>
                    </span>
                  </label>

                  <label class="flex items-center">
                    <input 
                      v-model="form.allow_save_progress"
                      type="checkbox"
                      class="w-4 h-4 text-green-600 border-green-300 rounded focus:ring-green-500"
                    >
                    <span class="ml-3 text-sm text-green-800">
                      <strong>Salvar progresso</strong>
                      <br>
                      <span class="text-green-600">Permite pausar e continuar depois</span>
                    </span>
                  </label>

                  <label class="flex items-center">
                    <input 
                      v-model="form.show_feedback"
                      type="checkbox"
                      class="w-4 h-4 text-green-600 border-green-300 rounded focus:ring-green-500"
                    >
                    <span class="ml-3 text-sm text-green-800">
                      <strong>Mostrar feedback</strong>
                      <br>
                      <span class="text-green-600">Exibe explicações após o simulado</span>
                    </span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 2: Questions -->
        <div v-if="currentStep === 2" class="p-6">
          <div class="flex items-center justify-between mb-6">
            <div>
              <h4 class="text-lg font-semibold text-gray-900">Questões do Simulado</h4>
              <p class="text-sm text-gray-600">Adicione e configure as questões do simulado</p>
            </div>
            <button 
              @click="addQuestion"
              class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
              Adicionar Questão
            </button>
          </div>

          <div v-if="form.questions.length === 0" class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma questão adicionada</h3>
            <p class="text-gray-600 mb-4">Comece adicionando a primeira questão do seu simulado</p>
            <button 
              @click="addQuestion"
              class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors"
            >
              Adicionar Primeira Questão
            </button>
          </div>

          <div v-else class="space-y-6">
            <div 
              v-for="(question, index) in form.questions" 
              :key="index"
              class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm"
            >
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center font-medium text-sm">
                    {{ index + 1 }}
                  </div>
                  <h5 class="font-medium text-gray-900">Questão {{ index + 1 }}</h5>
                </div>
                <button 
                  @click="removeQuestion(index)"
                  class="text-red-600 hover:text-red-800 text-sm font-medium transition-colors"
                >
                  Remover
                </button>
              </div>

              <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
                <div class="lg:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Enunciado da Questão *
                  </label>
                  <textarea 
                    v-model="question.statement"
                    rows="3"
                    placeholder="Digite o enunciado da questão..."
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipo de Questão *
                  </label>
                  <select 
                    v-model="question.type"
                    @change="updateQuestionType(index)"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="multiple_choice">Múltipla Escolha</option>
                    <option value="true_false">Verdadeiro/Falso</option>
                    <option value="essay">Dissertativa</option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Dificuldade
                  </label>
                  <select 
                    v-model="question.difficulty"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="easy">Fácil</option>
                    <option value="medium">Médio</option>
                    <option value="hard">Difícil</option>
                  </select>
                </div>
              </div>

              <!-- Multiple Choice Options -->
              <div v-if="question.type === 'multiple_choice'" class="space-y-3">
                <div class="flex items-center justify-between">
                  <label class="text-sm font-medium text-gray-700">Opções de Resposta</label>
                  <button 
                    @click="addOption(index)"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors"
                  >
                    + Adicionar Opção
                  </button>
                </div>
                
                <div 
                  v-for="(option, optionIndex) in question.options" 
                  :key="optionIndex"
                  class="flex items-center gap-3"
                >
                  <input 
                    type="radio" 
                    :name="`correct-${index}`" 
                    :value="option.id"
                    v-model="question.correctAnswer"
                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                  >
                  <input 
                    v-model="option.text"
                    type="text"
                    placeholder="Digite a opção..."
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  >
                  <button 
                    v-if="question.options.length > 2"
                    @click="removeOption(index, optionIndex)"
                    class="text-red-600 hover:text-red-800 text-sm transition-colors"
                  >
                    Remover
                  </button>
                </div>
                
                <p v-if="question.options.length < 2" class="text-sm text-red-600">
                  Mínimo 2 opções necessárias
                </p>
              </div>

              <!-- True/False Options -->
              <div v-if="question.type === 'true_false'" class="space-y-3">
                <label class="text-sm font-medium text-gray-700">Resposta Correta</label>
                <div class="flex gap-4">
                  <label class="flex items-center">
                    <input 
                      type="radio" 
                      value="true" 
                      v-model="question.correctAnswer"
                      class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    >
                    <span class="ml-2 text-sm text-gray-700">Verdadeiro</span>
                  </label>
                  <label class="flex items-center">
                    <input 
                      type="radio" 
                      value="false" 
                      v-model="question.correctAnswer"
                      class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                    >
                    <span class="ml-2 text-sm text-gray-700">Falso</span>
                  </label>
                </div>
              </div>

              <!-- Explanation -->
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Explicação (opcional)
                </label>
                <textarea 
                  v-model="question.explanation"
                  rows="2"
                  placeholder="Explicação que será mostrada após a resposta..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"
                ></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- Step 3: Review -->
        <div v-if="currentStep === 3" class="p-6">
          <div class="mb-6">
            <h4 class="text-lg font-semibold text-gray-900 mb-2">Revisão do Simulado</h4>
            <p class="text-sm text-gray-600">Verifique todas as informações antes de criar o simulado</p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Info Summary -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Informações Básicas</h5>
              <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <dt class="text-gray-600">Título:</dt>
                  <dd class="font-medium text-gray-900">{{ form.title }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-600">Tipo:</dt>
                  <dd class="font-medium text-gray-900">{{ form.type }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-600">Duração:</dt>
                  <dd class="font-medium text-gray-900">{{ formatDuration(form.duration * 60) }}</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-600">Nota Mínima:</dt>
                  <dd class="font-medium text-gray-900">{{ form.min_score }}%</dd>
                </div>
                <div class="flex justify-between">
                  <dt class="text-gray-600">Máx. Tentativas:</dt>
                  <dd class="font-medium text-gray-900">{{ form.max_attempts }}</dd>
                </div>
              </dl>
            </div>

            <!-- Questions Summary -->
            <div class="bg-gray-50 rounded-lg p-4">
              <h5 class="font-medium text-gray-900 mb-3">Questões ({{ form.questions.length }})</h5>
              <div class="space-y-2 text-sm">
                <div v-for="(question, index) in form.questions" :key="index" class="flex justify-between items-center">
                  <span class="text-gray-600">Questão {{ index + 1 }}:</span>
                  <span class="font-medium text-gray-900">{{ getQuestionTypeLabel(question.type) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Validation Errors -->
          <div v-if="Object.keys(validationErrors).length > 0" class="mt-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <h5 class="font-medium text-red-900 mb-2">Erros de Validação</h5>
            <ul class="text-sm text-red-700 space-y-1">
              <li v-for="(error, field) in validationErrors" :key="field">• {{ error }}</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 border-t border-gray-200 bg-gray-50 flex justify-between">
        <button 
          v-if="currentStep > 1"
          @click="previousStep"
          class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
        >
          Anterior
        </button>
        <div v-else></div>

        <div class="flex gap-3">
          <button 
            @click="$emit('close')"
            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </button>
          
          <button 
            v-if="currentStep < 3"
            @click="nextStep"
            :disabled="!canProceed"
            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
          >
            Próximo
          </button>
          
          <button 
            v-else
            @click="createSimulado"
            :disabled="!isValid || creating"
            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors flex items-center gap-2"
          >
            <svg v-if="creating" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            {{ creating ? 'Criando...' : 'Criar Simulado' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, reactive } from 'vue'

export default {
  name: 'ImprovedSimuladoModal',
  emits: ['close', 'create'],
  setup(props, { emit }) {
    const currentStep = ref(1)
    const creating = ref(false)
    
    const form = reactive({
      title: '',
      description: '',
      duration: 30, // em minutos
      min_score: 70,
      max_attempts: 3,
      type: 'obrigatorio',
      category_id: '',
      allow_navigation: true,
      allow_save_progress: true,
      show_feedback: true,
      questions: []
    })

    const errors = reactive({})

    // Computed
    const canProceed = computed(() => {
      if (currentStep.value === 1) {
        return form.title.trim() && form.description.trim()
      }
      if (currentStep.value === 2) {
        return form.questions.length > 0 && form.questions.every(q => isQuestionValid(q))
      }
      return true
    })

    const isValid = computed(() => {
      return Object.keys(validationErrors.value).length === 0
    })

    const validationErrors = computed(() => {
      const errors = {}
      
      if (!form.title.trim()) errors.title = 'Título é obrigatório'
      if (!form.description.trim()) errors.description = 'Descrição é obrigatória'
      if (form.questions.length === 0) errors.questions = 'Pelo menos uma questão é necessária'
      
      form.questions.forEach((question, index) => {
        if (!question.statement.trim()) {
          errors[`question_${index}_statement`] = `Enunciado da questão ${index + 1} é obrigatório`
        }
        if (question.type === 'multiple_choice' && question.options.length < 2) {
          errors[`question_${index}_options`] = `Questão ${index + 1} precisa de pelo menos 2 opções`
        }
        if (!question.correctAnswer) {
          errors[`question_${index}_answer`] = `Resposta correta da questão ${index + 1} é obrigatória`
        }
      })
      
      return errors
    })

    // Methods
    const formatDuration = (seconds) => {
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      
      if (hours > 0) {
        return `${hours}h ${minutes}min`
      }
      return `${minutes} minutos`
    }

    const nextStep = () => {
      if (canProceed.value && currentStep.value < 3) {
        currentStep.value++
      }
    }

    const previousStep = () => {
      if (currentStep.value > 1) {
        currentStep.value--
      }
    }

    const addQuestion = () => {
      form.questions.push({
        statement: '',
        type: 'multiple_choice',
        difficulty: 'medium',
        options: [
          { id: 'a', text: '' },
          { id: 'b', text: '' }
        ],
        correctAnswer: '',
        explanation: ''
      })
    }

    const removeQuestion = (index) => {
      form.questions.splice(index, 1)
    }

    const updateQuestionType = (index) => {
      const question = form.questions[index]
      if (question.type === 'true_false') {
        question.options = []
        question.correctAnswer = ''
      } else if (question.type === 'multiple_choice') {
        question.options = [
          { id: 'a', text: '' },
          { id: 'b', text: '' }
        ]
        question.correctAnswer = ''
      } else if (question.type === 'essay') {
        question.options = []
        question.correctAnswer = ''
      }
    }

    const addOption = (questionIndex) => {
      const question = form.questions[questionIndex]
      const nextId = String.fromCharCode(97 + question.options.length) // a, b, c, d...
      question.options.push({ id: nextId, text: '' })
    }

    const removeOption = (questionIndex, optionIndex) => {
      const question = form.questions[questionIndex]
      if (question.options.length > 2) {
        question.options.splice(optionIndex, 1)
        // Reset correct answer if it was the removed option
        if (question.correctAnswer === question.options[optionIndex]?.id) {
          question.correctAnswer = ''
        }
      }
    }

    const isQuestionValid = (question) => {
      if (!question.statement.trim()) return false
      if (question.type === 'multiple_choice') {
        return question.options.length >= 2 && 
               question.options.every(opt => opt.text.trim()) &&
               question.correctAnswer
      }
      if (question.type === 'true_false') {
        return question.correctAnswer
      }
      return true
    }

    const getQuestionTypeLabel = (type) => {
      const labels = {
        'multiple_choice': 'Múltipla Escolha',
        'true_false': 'Verdadeiro/Falso',
        'essay': 'Dissertativa'
      }
      return labels[type] || type
    }

    const createSimulado = async () => {
      if (!isValid.value) return
      
      creating.value = true
      try {
        // Convert duration from minutes to seconds
        const simuladoData = {
          ...form,
          duration: form.duration * 60
        }
        
        emit('create', simuladoData)
      } catch (error) {
        console.error('Erro ao criar simulado:', error)
      } finally {
        creating.value = false
      }
    }

    return {
      currentStep,
      creating,
      form,
      errors,
      canProceed,
      isValid,
      validationErrors,
      formatDuration,
      nextStep,
      previousStep,
      addQuestion,
      removeQuestion,
      updateQuestionType,
      addOption,
      removeOption,
      getQuestionTypeLabel,
      createSimulado
    }
  }
}
</script>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}

/* Smooth transitions */
.transition-colors {
  transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

/* Focus states */
input:focus, textarea:focus, select:focus {
  outline: none;
}

/* Animation for step indicators */
.w-8.h-8 {
  transition: all 0.3s ease;
}
</style>