<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4">
      <!-- Header -->
      <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Histórico de Simulados</h1>
            <p class="text-gray-600">Acompanhe seu progresso e performance nos simulados</p>
          </div>
          <button
            @click="goToSimulados"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Voltar aos Simulados
          </button>
        </div>
      </div>

      <!-- Estatísticas Gerais -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total de Tentativas</p>
              <p class="text-2xl font-bold text-gray-900">{{ totalAttempts }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Taxa de Aprovação</p>
              <p class="text-2xl font-bold text-green-600">{{ approvalRate }}%</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Nota Média</p>
              <p class="text-2xl font-bold text-blue-600">{{ averageScore }}%</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Certificados</p>
              <p class="text-2xl font-bold text-yellow-600">{{ certificatesEarned }}</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
              <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Filtros -->
      <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- Filtro por Simulado -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Simulado</label>
            <select v-model="filters.simuladoId" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Todos os simulados</option>
              <option v-for="simulado in availableSimulados" :key="simulado.id" :value="simulado.id">
                {{ simulado.title }}
              </option>
            </select>
          </div>

          <!-- Filtro por Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select v-model="filters.status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Todos</option>
              <option value="passed">Aprovado</option>
              <option value="failed">Reprovado</option>
              <option value="in_progress">Em Progresso</option>
            </select>
          </div>

          <!-- Filtro por Período -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Período</label>
            <select v-model="filters.period" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Todos os períodos</option>
              <option value="today">Hoje</option>
              <option value="week">Esta semana</option>
              <option value="month">Este mês</option>
              <option value="quarter">Este trimestre</option>
              <option value="year">Este ano</option>
            </select>
          </div>

          <!-- Busca -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nome do simulado..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            >
          </div>
        </div>

        <div class="flex justify-between items-center mt-4">
          <button
            @click="clearFilters"
            class="text-sm text-gray-600 hover:text-gray-800 transition-colors"
          >
            Limpar filtros
          </button>
          
          <div class="text-sm text-gray-600">
            {{ filteredAttempts.length }} de {{ allAttempts.length }} tentativas
          </div>
        </div>
      </div>

      <!-- Lista de Tentativas -->
      <div class="bg-white rounded-lg shadow-sm border">
        <div class="p-6 border-b">
          <div class="flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">Histórico de Tentativas</h3>
            
            <div class="flex items-center gap-2">
              <label class="text-sm text-gray-600">Ordenar por:</label>
              <select v-model="sortBy" class="text-sm border border-gray-300 rounded px-2 py-1">
                <option value="date_desc">Data (mais recente)</option>
                <option value="date_asc">Data (mais antiga)</option>
                <option value="score_desc">Nota (maior)</option>
                <option value="score_asc">Nota (menor)</option>
                <option value="title_asc">Simulado (A-Z)</option>
              </select>
            </div>
          </div>
        </div>

        <div class="divide-y divide-gray-200">
          <div
            v-for="attempt in paginatedAttempts"
            :key="attempt.id"
            class="p-6 hover:bg-gray-50 transition-colors"
          >
            <div class="flex justify-between items-start">
              <!-- Informações da Tentativa -->
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <h4 class="font-semibold text-gray-900">{{ getSimuladoTitle(attempt.simuladoId) }}</h4>
                  
                  <!-- Status Badge -->
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="getStatusBadgeClass(attempt)"
                  >
                    {{ getStatusLabel(attempt) }}
                  </span>
                  
                  <!-- Certificado -->
                  <span
                    v-if="attempt.passed && attempt.certificateGenerated"
                    class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full"
                  >
                    Certificado
                  </span>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-600">
                  <div>
                    <span class="font-medium">Data:</span>
                    {{ formatDate(attempt.date) }}
                  </div>
                  <div>
                    <span class="font-medium">Duração:</span>
                    {{ formatDuration(attempt.duration) }}
                  </div>
                  <div>
                    <span class="font-medium">Acertos:</span>
                    {{ attempt.correctAnswers }}/{{ attempt.totalQuestions }}
                  </div>
                  <div>
                    <span class="font-medium">Tentativa:</span>
                    {{ attempt.attemptNumber }}
                  </div>
                </div>
                
                <!-- Progresso Visual -->
                <div class="mt-3">
                  <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-600">Progresso</span>
                    <span class="font-medium" :class="attempt.passed ? 'text-green-600' : 'text-red-600'">
                      {{ attempt.score }}%
                    </span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="h-2 rounded-full transition-all duration-300"
                      :class="attempt.passed ? 'bg-green-500' : 'bg-red-500'"
                      :style="{ width: attempt.score + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
              
              <!-- Nota e Ações -->
              <div class="ml-6 text-right">
                <div class="text-3xl font-bold mb-2" :class="attempt.passed ? 'text-green-600' : 'text-red-600'">
                  {{ attempt.score }}%
                </div>
                
                <div class="space-y-2">
                  <button
                    @click="viewResult(attempt)"
                    class="w-full px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors"
                  >
                    Ver Resultado
                  </button>
                  
                  <button
                    v-if="attempt.passed && attempt.certificateGenerated"
                    @click="downloadCertificate(attempt)"
                    class="w-full px-3 py-1 text-sm bg-yellow-600 text-white rounded hover:bg-yellow-700 transition-colors"
                  >
                    Certificado
                  </button>
                  
                  <button
                    v-if="canRetrySimulado(attempt.simuladoId)"
                    @click="retrySimulado(attempt.simuladoId)"
                    class="w-full px-3 py-1 text-sm bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors"
                  >
                    Tentar Novamente
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Paginação -->
        <div v-if="totalPages > 1" class="p-6 border-t">
          <div class="flex justify-between items-center">
            <div class="text-sm text-gray-600">
              Mostrando {{ (currentPage - 1) * itemsPerPage + 1 }} a {{ Math.min(currentPage * itemsPerPage, filteredAttempts.length) }} de {{ filteredAttempts.length }} tentativas
            </div>
            
            <div class="flex items-center gap-2">
              <button
                @click="currentPage--"
                :disabled="currentPage === 1"
                class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Anterior
              </button>
              
              <span class="px-3 py-1 text-sm">
                {{ currentPage }} de {{ totalPages }}
              </span>
              
              <button
                @click="currentPage++"
                :disabled="currentPage === totalPages"
                class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Próxima
              </button>
            </div>
          </div>
        </div>

        <!-- Estado Vazio -->
        <div v-if="filteredAttempts.length === 0" class="p-12 text-center">
          <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4m6-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
          <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhuma tentativa encontrada</h3>
          <p class="text-gray-600 mb-4">{{ allAttempts.length === 0 ? 'Você ainda não fez nenhum simulado.' : 'Tente ajustar os filtros para encontrar tentativas.' }}</p>
          <button
            @click="goToSimulados"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            Fazer um Simulado
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSimulado } from '../../composables/useSimulado'

export default {
  name: 'SimuladoHistory',
  setup() {
    const router = useRouter()
    const { getAllSimulados, getAllUserAttempts, canAttemptSimulado } = useSimulado()

    // Estado
    const allAttempts = ref([])
    const availableSimulados = ref([])
    const currentPage = ref(1)
    const itemsPerPage = ref(10)
    const sortBy = ref('date_desc')
    
    const filters = ref({
      simuladoId: '',
      status: '',
      period: '',
      search: ''
    })

    // Computed
    const totalAttempts = computed(() => allAttempts.value.length)
    
    const approvalRate = computed(() => {
      if (totalAttempts.value === 0) return 0
      const passed = allAttempts.value.filter(attempt => attempt.passed).length
      return Math.round((passed / totalAttempts.value) * 100)
    })
    
    const averageScore = computed(() => {
      if (totalAttempts.value === 0) return 0
      const total = allAttempts.value.reduce((sum, attempt) => sum + attempt.score, 0)
      return Math.round(total / totalAttempts.value)
    })
    
    const certificatesEarned = computed(() => {
      return allAttempts.value.filter(attempt => attempt.passed && attempt.certificateGenerated).length
    })

    const filteredAttempts = computed(() => {
      let filtered = [...allAttempts.value]
      
      // Filtro por simulado
      if (filters.value.simuladoId) {
        filtered = filtered.filter(attempt => attempt.simuladoId === filters.value.simuladoId)
      }
      
      // Filtro por status
      if (filters.value.status) {
        if (filters.value.status === 'passed') {
          filtered = filtered.filter(attempt => attempt.passed)
        } else if (filters.value.status === 'failed') {
          filtered = filtered.filter(attempt => !attempt.passed && attempt.status === 'completed')
        } else if (filters.value.status === 'in_progress') {
          filtered = filtered.filter(attempt => attempt.status === 'in_progress')
        }
      }
      
      // Filtro por período
      if (filters.value.period) {
        const now = new Date()
        const filterDate = new Date()
        
        switch (filters.value.period) {
          case 'today':
            filterDate.setHours(0, 0, 0, 0)
            break
          case 'week':
            filterDate.setDate(now.getDate() - 7)
            break
          case 'month':
            filterDate.setMonth(now.getMonth() - 1)
            break
          case 'quarter':
            filterDate.setMonth(now.getMonth() - 3)
            break
          case 'year':
            filterDate.setFullYear(now.getFullYear() - 1)
            break
        }
        
        filtered = filtered.filter(attempt => new Date(attempt.date) >= filterDate)
      }
      
      // Filtro por busca
      if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase()
        filtered = filtered.filter(attempt => {
          const simuladoTitle = getSimuladoTitle(attempt.simuladoId).toLowerCase()
          return simuladoTitle.includes(searchTerm)
        })
      }
      
      // Ordenação
      filtered.sort((a, b) => {
        switch (sortBy.value) {
          case 'date_desc':
            return new Date(b.date) - new Date(a.date)
          case 'date_asc':
            return new Date(a.date) - new Date(b.date)
          case 'score_desc':
            return b.score - a.score
          case 'score_asc':
            return a.score - b.score
          case 'title_asc':
            return getSimuladoTitle(a.simuladoId).localeCompare(getSimuladoTitle(b.simuladoId))
          default:
            return 0
        }
      })
      
      return filtered
    })
    
    const totalPages = computed(() => {
      return Math.ceil(filteredAttempts.value.length / itemsPerPage.value)
    })
    
    const paginatedAttempts = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage.value
      const end = start + itemsPerPage.value
      return filteredAttempts.value.slice(start, end)
    })

    // Métodos
    const loadData = () => {
      availableSimulados.value = getAllSimulados()
      allAttempts.value = getAllUserAttempts()
    }

    const getSimuladoTitle = (simuladoId) => {
      const simulado = availableSimulados.value.find(s => s.id === simuladoId)
      return simulado ? simulado.title : 'Simulado não encontrado'
    }

    const getStatusLabel = (attempt) => {
      if (attempt.status === 'in_progress') return 'Em Progresso'
      return attempt.passed ? 'Aprovado' : 'Reprovado'
    }

    const getStatusBadgeClass = (attempt) => {
      if (attempt.status === 'in_progress') {
        return 'bg-yellow-100 text-yellow-800'
      }
      return attempt.passed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
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

    const formatDuration = (seconds) => {
      const hours = Math.floor(seconds / 3600)
      const minutes = Math.floor((seconds % 3600) / 60)
      const secs = seconds % 60
      
      if (hours > 0) {
        return `${hours}h ${minutes}min`
      } else if (minutes > 0) {
        return `${minutes}min ${secs}s`
      }
      return `${secs}s`
    }

    const clearFilters = () => {
      filters.value = {
        simuladoId: '',
        status: '',
        period: '',
        search: ''
      }
      currentPage.value = 1
    }

    const viewResult = (attempt) => {
      router.push(`/simulados/${attempt.simuladoId}/result/${attempt.id}`)
    }

    const downloadCertificate = async (attempt) => {
      // Implementar download do certificado
      console.log('Baixando certificado para tentativa:', attempt.id)
    }

    const retrySimulado = (simuladoId) => {
      router.push(`/simulados/${simuladoId}/exam`)
    }

    const canRetrySimulado = (simuladoId) => {
      const simulado = availableSimulados.value.find(s => s.id === simuladoId)
      return simulado && canAttemptSimulado(simulado)
    }

    const goToSimulados = () => {
      router.push('/simulados')
    }

    // Lifecycle
    onMounted(() => {
      loadData()
    })

    return {
      // Estado
      allAttempts,
      availableSimulados,
      currentPage,
      itemsPerPage,
      sortBy,
      filters,
      
      // Computed
      totalAttempts,
      approvalRate,
      averageScore,
      certificatesEarned,
      filteredAttempts,
      totalPages,
      paginatedAttempts,
      
      // Métodos
      getSimuladoTitle,
      getStatusLabel,
      getStatusBadgeClass,
      formatDate,
      formatDuration,
      clearFilters,
      viewResult,
      downloadCertificate,
      retrySimulado,
      canRetrySimulado,
      goToSimulados
    }
  }
}
</script>

<style scoped>
/* Estilos específicos se necessário */
</style>