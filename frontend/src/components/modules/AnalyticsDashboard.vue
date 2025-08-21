<template>
  <div class="analytics-dashboard">
    <!-- Header -->
    <div class="dashboard-header">
      <h1 class="dashboard-title">Dashboard de Analytics</h1>
      <div class="dashboard-controls">
        <select v-model="selectedPeriod" @change="loadDashboardData" class="period-selector">
          <option value="7">Últimos 7 dias</option>
          <option value="30">Últimos 30 dias</option>
          <option value="90">Últimos 90 dias</option>
          <option value="365">Último ano</option>
        </select>
        <button @click="refreshData" class="refresh-btn" :disabled="loading">
          <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          Atualizar
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Carregando dados de analytics...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <button @click="loadDashboardData" class="retry-btn">Tentar novamente</button>
    </div>

    <!-- Dashboard Content -->
    <div v-else class="dashboard-content">
      <!-- Overview Cards -->
      <div class="overview-section">
        <h2 class="section-title">Visão Geral</h2>
        <div class="metrics-grid">
          <div class="metric-card">
            <div class="metric-icon users">
              <i class="fas fa-users"></i>
            </div>
            <div class="metric-content">
              <h3>{{ formatNumber(overview.users?.total || 0) }}</h3>
              <p>Total de Usuários</p>
              <span class="metric-subtitle">{{ formatNumber(overview.users?.active || 0) }} ativos</span>
            </div>
          </div>

          <div class="metric-card">
            <div class="metric-icon simulados">
              <i class="fas fa-clipboard-list"></i>
            </div>
            <div class="metric-content">
              <h3>{{ formatNumber(overview.simulados?.total || 0) }}</h3>
              <p>Total de Simulados</p>
              <span class="metric-subtitle">{{ formatNumber(overview.simulados?.completed || 0) }} concluídos</span>
            </div>
          </div>

          <div class="metric-card">
            <div class="metric-icon performance">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="metric-content">
              <h3>{{ formatPercentage(overview.simulados?.approval_rate || 0) }}</h3>
              <p>Taxa de Aprovação</p>
              <span class="metric-subtitle">{{ formatPercentage(overview.simulados?.completion_rate || 0) }} conclusão</span>
            </div>
          </div>

          <div class="metric-card">
            <div class="metric-icon score">
              <i class="fas fa-star"></i>
            </div>
            <div class="metric-content">
              <h3>{{ formatNumber(overview.performance?.avg_score || 0, 1) }}</h3>
              <p>Pontuação Média</p>
              <span class="metric-subtitle">{{ formatTime(overview.performance?.avg_completion_time || 0) }} tempo médio</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="charts-section">
        <div class="chart-row">
          <!-- Trends Chart -->
          <div class="chart-container">
            <h3 class="chart-title">Tendências</h3>
            <div class="chart-content">
              <canvas ref="trendsChart" class="chart-canvas"></canvas>
            </div>
          </div>

          <!-- Performance Distribution -->
          <div class="chart-container">
            <h3 class="chart-title">Distribuição de Pontuações</h3>
            <div class="chart-content">
              <canvas ref="distributionChart" class="chart-canvas"></canvas>
            </div>
          </div>
        </div>

        <div class="chart-row">
          <!-- System Metrics -->
          <div class="chart-container">
            <h3 class="chart-title">Métricas do Sistema</h3>
            <div class="system-metrics">
              <div class="system-metric">
                <span class="metric-label">Uptime</span>
                <div class="progress-bar">
                  <div class="progress-fill" :style="{ width: (overview.system?.uptime || 0) + '%' }"></div>
                </div>
                <span class="metric-value">{{ formatPercentage(overview.system?.uptime || 0) }}</span>
              </div>
              <div class="system-metric">
                <span class="metric-label">Uso de Armazenamento</span>
                <div class="progress-bar">
                  <div class="progress-fill storage" :style="{ width: (overview.system?.storage_usage || 0) + '%' }"></div>
                </div>
                <span class="metric-value">{{ formatPercentage(overview.system?.storage_usage || 0) }}</span>
              </div>
            </div>
          </div>

          <!-- Top Performers -->
          <div class="chart-container">
            <h3 class="chart-title">Top Performers</h3>
            <div class="top-performers">
              <div v-for="(performer, index) in topPerformers.slice(0, 5)" :key="performer.id" class="performer-item">
                <div class="performer-rank">{{ index + 1 }}</div>
                <div class="performer-info">
                  <span class="performer-name">{{ performer.name }}</span>
                  <span class="performer-score">{{ formatNumber(performer.avg_score, 1) }} pts</span>
                </div>
                <div class="performer-attempts">{{ performer.total_attempts }} tentativas</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from 'vue'
import { Chart, registerables } from 'chart.js'
import { api } from '../../composables/api'

// Register Chart.js components
Chart.register(...registerables)

// Reactive data
const loading = ref(false)
const error = ref(null)
const selectedPeriod = ref(30)
const overview = reactive({})
const trends = reactive({})
const performance = reactive({})
const topPerformers = ref([])

// Chart refs
const trendsChart = ref(null)
const distributionChart = ref(null)
let trendsChartInstance = null
let distributionChartInstance = null

// Methods
const formatNumber = (value, decimals = 0) => {
  if (value === null || value === undefined) return '0'
  return Number(value).toLocaleString('pt-BR', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  })
}

const formatPercentage = (value) => {
  if (value === null || value === undefined) return '0%'
  return Number(value).toFixed(1) + '%'
}

const formatTime = (minutes) => {
  if (!minutes) return '0min'
  if (minutes < 60) return `${Math.round(minutes)}min`
  const hours = Math.floor(minutes / 60)
  const mins = Math.round(minutes % 60)
  return `${hours}h ${mins}min`
}

const loadDashboardData = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Load overview data
    const overviewResponse = await api.get('/reports/overview')
    Object.assign(overview, overviewResponse.data.data)
    
    // Load trends data
    const trendsResponse = await api.get(`/reports/trends?days=${selectedPeriod.value}`)
    Object.assign(trends, trendsResponse.data.data)
    
    // Load performance data
    const performanceResponse = await api.get(`/reports/performance?days=${selectedPeriod.value}`)
    Object.assign(performance, performanceResponse.data.data)
    
    // Load top performers
    const performersResponse = await api.get('/reports/users/top-performers?limit=10')
    topPerformers.value = performersResponse.data.data
    
    // Update charts after data is loaded
    await nextTick()
    updateCharts()
    
  } catch (err) {
    console.error('Erro ao carregar dados do dashboard:', err)
    error.value = 'Erro ao carregar dados. Tente novamente.'
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  loadDashboardData()
}

const updateCharts = () => {
  updateTrendsChart()
  updateDistributionChart()
}

const updateTrendsChart = () => {
  if (!trendsChart.value) return
  
  // Destroy existing chart
  if (trendsChartInstance) {
    trendsChartInstance.destroy()
  }
  
  const ctx = trendsChart.value.getContext('2d')
  
  // Prepare data
  const labels = trends.usuarios?.map(item => new Date(item.date).toLocaleDateString('pt-BR')) || []
  
  trendsChartInstance = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [
        {
          label: 'Novos Usuários',
          data: trends.usuarios?.map(item => item.value) || [],
          borderColor: '#3B82F6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          tension: 0.4
        },
        {
          label: 'Novos Simulados',
          data: trends.simulados?.map(item => item.value) || [],
          borderColor: '#10B981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          tension: 0.4
        },
        {
          label: 'Tentativas',
          data: trends.attempts?.map(item => item.value) || [],
          borderColor: '#F59E0B',
          backgroundColor: 'rgba(245, 158, 11, 0.1)',
          tension: 0.4
        }
      ]
    },
    options: {
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
  })
}

const updateDistributionChart = () => {
  if (!distributionChart.value) return
  
  // Destroy existing chart
  if (distributionChartInstance) {
    distributionChartInstance.destroy()
  }
  
  const ctx = distributionChart.value.getContext('2d')
  
  // Get distribution data
  const distribution = performance.distribuicao_pontuacoes || {
    excellent: 0,
    good: 0,
    average: 0,
    poor: 0
  }
  
  distributionChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Excelente (90-100)', 'Bom (70-89)', 'Regular (50-69)', 'Ruim (<50)'],
      datasets: [{
        data: [distribution.excellent, distribution.good, distribution.average, distribution.poor],
        backgroundColor: [
          '#10B981',
          '#3B82F6',
          '#F59E0B',
          '#EF4444'
        ],
        borderWidth: 2,
        borderColor: '#ffffff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  })
}

// Lifecycle
onMounted(() => {
  loadDashboardData()
})
</script>

<style scoped>
.analytics-dashboard {
  padding: 24px;
  background-color: #f8fafc;
  min-height: 100vh;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 32px;
}

.dashboard-title {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0;
}

.dashboard-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.period-selector {
  padding: 8px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  background: white;
  font-size: 14px;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.refresh-btn:hover:not(:disabled) {
  background: #2563eb;
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 64px;
  text-align: center;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #e5e7eb;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 16px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state i {
  font-size: 48px;
  color: #ef4444;
  margin-bottom: 16px;
}

.retry-btn {
  padding: 8px 16px;
  background: #3b82f6;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 16px;
}

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 16px;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 32px;
}

.metric-card {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  gap: 16px;
}

.metric-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  color: white;
}

.metric-icon.users { background: #3b82f6; }
.metric-icon.simulados { background: #10b981; }
.metric-icon.performance { background: #f59e0b; }
.metric-icon.score { background: #8b5cf6; }

.metric-content h3 {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  margin: 0 0 4px 0;
}

.metric-content p {
  font-size: 14px;
  color: #6b7280;
  margin: 0 0 4px 0;
}

.metric-subtitle {
  font-size: 12px;
  color: #9ca3af;
}

.charts-section {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.chart-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 24px;
}

.chart-container {
  background: white;
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.chart-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 16px;
}

.chart-content {
  position: relative;
  height: 300px;
}

.chart-canvas {
  max-height: 100%;
}

.system-metrics {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.system-metric {
  display: flex;
  align-items: center;
  gap: 12px;
}

.metric-label {
  min-width: 120px;
  font-size: 14px;
  color: #6b7280;
}

.progress-bar {
  flex: 1;
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.progress-fill {
  height: 100%;
  background: #10b981;
  transition: width 0.3s ease;
}

.progress-fill.storage {
  background: #f59e0b;
}

.metric-value {
  min-width: 60px;
  text-align: right;
  font-weight: 600;
  color: #1f2937;
}

.top-performers {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.performer-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: #f9fafb;
  border-radius: 8px;
}

.performer-rank {
  width: 24px;
  height: 24px;
  background: #3b82f6;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 600;
}

.performer-info {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.performer-name {
  font-weight: 500;
  color: #1f2937;
  font-size: 14px;
}

.performer-score {
  font-size: 12px;
  color: #6b7280;
}

.performer-attempts {
  font-size: 12px;
  color: #9ca3af;
}

@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .chart-row {
    grid-template-columns: 1fr;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
  }
}
</style>