<template>
  <div class="p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Simulados</h1>
        <p class="text-gray-600 mt-1">Avaliações e testes de conhecimento disponíveis</p>
      </div>
      <button
        @click="showHistory = true"
        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Histórico
      </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Nome do simulado..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
          <select
            v-model="selectedType"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os tipos</option>
            <option value="obrigatorio">Obrigatório</option>
            <option value="opcional">Opcional</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select
            v-model="selectedStatus"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            <option value="">Todos os status</option>
            <option value="disponivel">Disponível</option>
            <option value="concluido">Concluído</option>
            <option value="pendente">Pendente</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Lista de Simulados -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div
        v-for="simulado in filteredSimulados"
        :key="simulado.id"
        class="bg-white rounded-lg shadow-sm border hover:shadow-md transition-shadow"
      >
        <div class="p-6">
          <!-- Header do Card -->
          <div class="flex justify-between items-start mb-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <h3 class="text-lg font-semibold text-gray-900">{{ simulado.title }}</h3>
                <span
                  :class="getTypeClass(simulado.type)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ getTypeLabel(simulado.type) }}
                </span>
              </div>
              <p class="text-gray-600 text-sm">{{ simulado.description }}</p>
            </div>
            <div :class="getStatusClass(getSimuladoStatus(simulado))" class="w-3 h-3 rounded-full flex-shrink-0 mt-1"></div>
          </div>

          <!-- Informações do Simulado -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <span>{{ formatDuration(simulado.duration) }}</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              <span>{{ simulado.questions.length }} questões</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
              <span>Nota mínima: {{ simulado.minScore }}%</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <span>{{ getAttemptsText(simulado) }}</span>
            </div>
          </div>

          <!-- Última Tentativa -->
          <div v-if="getLastAttempt(simulado.id)" class="bg-gray-50 rounded-lg p-3 mb-4">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm font-medium text-gray-900">Última tentativa</p>
                <p class="text-xs text-gray-600">{{ formatDate(getLastAttempt(simulado.id).date) }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold" :class="getLastAttempt(simulado.id).passed ? 'text-green-600' : 'text-red-600'">
                  {{ getLastAttempt(simulado.id).score }}%
                </p>
                <p class="text-xs" :class="getLastAttempt(simulado.id).passed ? 'text-green-600' : 'text-red-600'">
                  {{ getLastAttempt(simulado.id).passed ? 'Aprovado' : 'Reprovado' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Progresso Salvo -->
          <div v-if="hasProgress(simulado.id)" class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              <p class="text-sm text-blue-800 font-medium">Progresso salvo disponível</p>
            </div>
            <p class="text-xs text-blue-600 mt-1">Você pode continuar de onde parou</p>
          </div>

          <!-- Ações -->
          <div class="flex gap-2">
            <button
              @click="viewRules(simulado)"
              class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              Ver Regras
            </button>
            <button
              v-if="canAttemptSimulado(simulado)"
              @click="startSimulado(simulado)"
              class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
            >
              {{ hasProgress(simulado.id) ? 'Continuar' : 'Iniciar' }}
            </button>
            <button
              v-else
              disabled
              class="flex-1 bg-gray-300 text-gray-500 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed"
            >
              Limite Atingido
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Estado Vazio -->
    <div v-if="filteredSimulados.length === 0" class="text-center py-12">
      <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
      </svg>
      <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum simulado encontrado</h3>
      <p class="text-gray-600">Não há simulados que correspondam aos filtros selecionados.</p>
    </div>

    <!-- Modal de Regras -->
    <SimuladoRules
      v-if="showRules"
      :simulado="selectedSimulado"
      @close="showRules = false"
      @start="onStartFromRules"
    />

    <!-- Modal de Histórico -->
    <SimuladoHistory
      v-if="showHistory"
      @close="showHistory = false"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSimulado } from '../../composables/useSimulado'
import SimuladoRules from './SimuladoRules.vue'
import SimuladoHistory from './SimuladoHistory.vue'

export default {
  name: 'SimuladoList',
  components: {
    SimuladoRules,
    SimuladoHistory
  },
  setup() {
    const router = useRouter()
    const {
      simulados,
      userAttempts,
      canAttemptSimulado,
      getLastAttempt,
      getSimuladoAttempts
    } = useSimulado()

    // Estado local
    const searchTerm = ref('')
    const selectedType = ref('')
    const selectedStatus = ref('')
    const showRules = ref(false)
    const showHistory = ref(false)
    const selectedSimulado = ref(null)

    // Computed
    const filteredSimulados = computed(() => {
      return simulados.value.filter(simulado => {
        const matchesSearch = simulado.title.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
                            simulado.description.toLowerCase().includes(searchTerm.value.toLowerCase())
        
        const matchesType = !selectedType.value || simulado.type === selectedType.value
        
        const status = getSimuladoStatus(simulado)
        const matchesStatus = !selectedStatus.value || status === selectedStatus.value
        
        return matchesSearch && matchesType && matchesStatus
      })
    })

    // Métodos
    const getSimuladoStatus = (simulado) => {
      const lastAttempt = getLastAttempt(simulado.id)
      
      if (!lastAttempt) {
        return 'pendente'
      }
      
      if (lastAttempt.passed) {
        return 'concluido'
      }
      
      if (canAttemptSimulado(simulado)) {
        return 'disponivel'
      }
      
      return 'pendente'
    }

    const getTypeClass = (type) => {
      return {
        'bg-red-100 text-red-800': type === 'obrigatorio',
        'bg-blue-100 text-blue-800': type === 'opcional'
      }
    }

    const getTypeLabel = (type) => {
      return {
        'obrigatorio': 'Obrigatório',
        'opcional': 'Opcional'
      }[type] || type
    }

    const getStatusClass = (status) => {
      return {
        'bg-green-500': status === 'concluido',
        'bg-blue-500': status === 'disponivel',
        'bg-yellow-500': status === 'pendente'
      }
    }

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

    const getAttemptsText = (simulado) => {
      const attempts = getSimuladoAttempts(simulado.id)
      return `${attempts.length}/${simulado.maxAttempts} tentativas`
    }

    const hasProgress = (simuladoId) => {
      return localStorage.getItem(`simulado_progress_${simuladoId}`) !== null
    }

    const viewRules = (simulado) => {
      selectedSimulado.value = simulado
      showRules.value = true
    }

    const startSimulado = (simulado) => {
      router.push(`/simulados/${simulado.id}/exam`)
    }

    const onStartFromRules = (simulado) => {
      showRules.value = false
      startSimulado(simulado)
    }

    return {
      // Estado
      searchTerm,
      selectedType,
      selectedStatus,
      showRules,
      showHistory,
      selectedSimulado,
      
      // Computed
      filteredSimulados,
      
      // Métodos
      getSimuladoStatus,
      getTypeClass,
      getTypeLabel,
      getStatusClass,
      formatDuration,
      formatDate,
      getAttemptsText,
      hasProgress,
      viewRules,
      startSimulado,
      onStartFromRules,
      canAttemptSimulado,
      getLastAttempt
    }
  }
}
</script>