<template>
  <div>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="heading-2 mb-2">Relatórios e Métricas</h1>
      <p class="body-text">Acompanhe as métricas e relatórios do sistema OnHCP</p>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex space-x-3">
        <button @click="collectMetrics" :disabled="collecting" class="btn-primary">
          <svg v-if="collecting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ collecting ? 'Coletando...' : 'Coletar Métricas' }}
        </button>
        <button @click="exportReport" class="btn-secondary">
          Exportar Relatório
        </button>
      </div>
      <div class="flex items-center space-x-2">
        <label class="text-sm font-medium text-gray-700">Período:</label>
        <select v-model="selectedPeriod" @change="loadMetrics" class="form-select">
          <option value="7">Últimos 7 dias</option>
          <option value="30">Últimos 30 dias</option>
          <option value="90">Últimos 90 dias</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="space-y-6">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="card-metric">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-body text-text-secondary mb-1">Total Usuários</p>
              <p class="metric-number">{{ formatNumber(metrics.users?.total_usuarios || 0) }}</p>
              <div class="flex items-center mt-2">
                <span class="text-xs text-success font-medium">+{{ metrics.users?.usuarios_hoje || 0 }}</span>
                <span class="text-xs text-text-secondary ml-1">hoje</span>
              </div>
            </div>
            <div class="w-12 h-12 bg-primary-50 rounded-md flex items-center justify-center">
              <svg class="w-6 h-6 text-primary" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card-metric">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-body text-text-secondary mb-1">Simulados Hoje</p>
              <p class="metric-number">{{ formatNumber(metrics.simulados?.tentativas_hoje || 0) }}</p>
              <div class="flex items-center mt-2">
                <span class="text-xs text-success font-medium">{{ metrics.simulados?.taxa_conclusao || 0 }}%</span>
                <span class="text-xs text-text-secondary ml-1">conclusão</span>
              </div>
            </div>
            <div class="w-12 h-12 bg-green-50 rounded-md flex items-center justify-center">
              <svg class="w-6 h-6 text-success" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card-metric">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-body text-text-secondary mb-1">Tempo Médio</p>
              <p class="metric-number">{{ formatDuration(metrics.performance?.tempo_medio_conclusao || 0) }}</p>
              <div class="flex items-center mt-2">
                <span class="text-xs text-warning font-medium">{{ formatNumber(metrics.performance?.pontuacao_media || 0) }}</span>
                <span class="text-xs text-text-secondary ml-1">pontos</span>
              </div>
            </div>
            <div class="w-12 h-12 bg-yellow-50 rounded-md flex items-center justify-center">
              <svg class="w-6 h-6 text-warning" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
        </div>

        <div class="card-metric">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-body text-text-secondary mb-1">Notificações</p>
              <p class="metric-number">{{ formatNumber(metrics.sistema?.total_notificacoes || 0) }}</p>
              <div class="flex items-center mt-2">
                <span class="text-xs text-error font-medium">{{ formatNumber(metrics.sistema?.notificacoes_hoje || 0) }}</span>
                <span class="text-xs text-text-secondary ml-1">hoje</span>
              </div>
            </div>
            <div class="w-12 h-12 bg-red-50 rounded-md flex items-center justify-center">
              <svg class="w-6 h-6 text-error" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Users Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Usuários por Período</h3>
            <p class="card-subtitle">Crescimento de usuários ao longo do tempo</p>
          </div>
          <div class="card-body">
            <Line
              v-if="usersChartData"
              :data="usersChartData"
              :options="chartOptions"
              class="h-64"
            />
          </div>
        </div>

        <!-- Simulados Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Simulados por Categoria</h3>
            <p class="card-subtitle">Distribuição de simulados realizados</p>
          </div>
          <div class="card-body">
            <Doughnut
              v-if="simuladosChartData"
              :data="simuladosChartData"
              :options="doughnutOptions"
              class="h-64"
            />
          </div>
        </div>

        <!-- Performance Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Performance dos Usuários</h3>
            <p class="card-subtitle">Distribuição de pontuações</p>
          </div>
          <div class="card-body">
            <Bar
              v-if="performanceChartData"
              :data="performanceChartData"
              :options="barOptions"
              class="h-64"
            />
          </div>
        </div>

        <!-- System Metrics Chart -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Métricas do Sistema</h3>
            <p class="card-subtitle">Uso de armazenamento e uptime</p>
          </div>
          <div class="card-body">
            <Line
              v-if="systemChartData"
              :data="systemChartData"
              :options="chartOptions"
              class="h-64"
            />
          </div>
        </div>
      </div>

      <!-- Recent Activity Table -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Atividade Recente</h3>
          <p class="card-subtitle">Últimas ações no sistema</p>
        </div>
        <div class="card-body">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="activity in recentActivity" :key="activity.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                          <span class="text-sm font-medium text-gray-700">{{ activity.user?.charAt(0) || 'U' }}</span>
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ activity.user || 'Usuário' }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ activity.action || 'Ação' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(activity.created_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(activity.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                      {{ activity.status || 'Concluído' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { Line, Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'
import { api } from '../../composables/api'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend
)

export default {
  name: 'AdminReports',
  components: {
    Line,
    Bar,
    Doughnut
  },
  setup() {
    const loading = ref(true)
    const collecting = ref(false)
    const selectedPeriod = ref('30')
    const metrics = ref({})
    const recentActivity = ref([])

    // Chart options
    const chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }

    const barOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'top'
        }
      },
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }

    const doughnutOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right'
        }
      }
    }

    // Chart data computed properties
    const usersChartData = computed(() => {
      if (!metrics.value.users) return null
      
      return {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
        datasets: [
          {
            label: 'Usuários Ativos',
            data: [65, 78, 90, 81, 95, 102],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4
          },
          {
            label: 'Novos Usuários',
            data: [12, 15, 18, 22, 25, 28],
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4
          }
        ]
      }
    })

    const simuladosChartData = computed(() => {
      if (!metrics.value.simulados) return null
      
      return {
        labels: ['Técnico', 'Gestão', 'Segurança', 'Compliance', 'Outros'],
        datasets: [
          {
            data: [30, 25, 20, 15, 10],
            backgroundColor: [
              'rgba(59, 130, 246, 0.8)',
              'rgba(16, 185, 129, 0.8)',
              'rgba(245, 158, 11, 0.8)',
              'rgba(239, 68, 68, 0.8)',
              'rgba(139, 92, 246, 0.8)'
            ],
            borderWidth: 2,
            borderColor: '#fff'
          }
        ]
      }
    })

    const performanceChartData = computed(() => {
      if (!metrics.value.performance) return null
      
      return {
        labels: ['0-20', '21-40', '41-60', '61-80', '81-100'],
        datasets: [
          {
            label: 'Número de Usuários',
            data: [5, 12, 25, 35, 23],
            backgroundColor: 'rgba(59, 130, 246, 0.8)',
            borderColor: 'rgb(59, 130, 246)',
            borderWidth: 1
          }
        ]
      }
    })

    const systemChartData = computed(() => {
      if (!metrics.value.sistema) return null
      
      return {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
        datasets: [
          {
            label: 'Uso de Armazenamento (%)',
            data: [45, 52, 48, 61, 58, 65],
            borderColor: 'rgb(245, 158, 11)',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.4
          },
          {
            label: 'Uptime (%)',
            data: [99.9, 99.8, 99.9, 99.7, 99.9, 99.8],
            borderColor: 'rgb(16, 185, 129)',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4
          }
        ]
      }
    })

    // Methods
    const loadMetrics = async () => {
      loading.value = true
      try {
        const response = await api.get('/reports/dashboard', {
          params: { period: selectedPeriod.value }
        })
        metrics.value = response.data
        
        // Load recent activity
        const activityResponse = await api.get('/reports/recent-activity')
        recentActivity.value = activityResponse.data.slice(0, 10)
      } catch (error) {
        console.error('Erro ao carregar métricas:', error)
      } finally {
        loading.value = false
      }
    }

    const collectMetrics = async () => {
      collecting.value = true
      try {
        await api.post('/reports/collect-metrics')
        await loadMetrics()
      } catch (error) {
        console.error('Erro ao coletar métricas:', error)
      } finally {
        collecting.value = false
      }
    }

    const exportReport = async () => {
      try {
        const response = await api.get('/reports/export', {
          params: { period: selectedPeriod.value },
          responseType: 'blob'
        })
        
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `relatorio-${new Date().toISOString().split('T')[0]}.pdf`)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(url)
      } catch (error) {
        console.error('Erro ao exportar relatório:', error)
      }
    }

    const formatNumber = (num) => {
      return new Intl.NumberFormat('pt-BR').format(num)
    }

    const formatDuration = (minutes) => {
      if (minutes < 60) {
        return `${minutes}min`
      }
      const hours = Math.floor(minutes / 60)
      const mins = minutes % 60
      return `${hours}h ${mins}min`
    }

    const formatDate = (date) => {
      return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    const getStatusClass = (status) => {
      switch (status) {
        case 'Concluído':
          return 'bg-green-100 text-green-800'
        case 'Em Progresso':
          return 'bg-yellow-100 text-yellow-800'
        case 'Erro':
          return 'bg-red-100 text-red-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    }

    // Lifecycle
    onMounted(() => {
      loadMetrics()
    })

    return {
      loading,
      collecting,
      selectedPeriod,
      metrics,
      recentActivity,
      chartOptions,
      barOptions,
      doughnutOptions,
      usersChartData,
      simuladosChartData,
      performanceChartData,
      systemChartData,
      loadMetrics,
      collectMetrics,
      exportReport,
      formatNumber,
      formatDuration,
      formatDate,
      getStatusClass
    }
  }
}
</script>

<style scoped>
.card {
  @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6;
}

.card-metric {
  @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6;
}

.card-header {
  @apply mb-4;
}

.card-title {
  @apply text-lg font-semibold text-gray-900;
}

.card-subtitle {
  @apply text-sm text-gray-600;
}

.card-body {
  @apply relative;
}

.heading-2 {
  @apply text-2xl font-bold text-gray-900;
}

.body-text {
  @apply text-gray-600;
}

.metric-number {
  @apply text-2xl font-bold text-gray-900;
}

.btn-primary {
  @apply bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed;
}

.btn-secondary {
  @apply bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors;
}

.form-select {
  @apply border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500;
}

.text-primary {
  @apply text-blue-600;
}

.text-success {
  @apply text-green-600;
}

.text-warning {
  @apply text-yellow-600;
}

.text-error {
  @apply text-red-600;
}

.text-text-secondary {
  @apply text-gray-500;
}

.bg-primary-50 {
  @apply bg-blue-50;
}
</style>