<template>
  <div>
    <!-- Page Header -->
    <div class="mb-6">
      <h1 class="heading-2 mb-2">Visão Geral</h1>
      <p class="body-text">Acompanhe as métricas principais do sistema OnHCP</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="card-metric">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-body text-text-secondary mb-1">Total Utilizadores</p>
            <p class="metric-number">{{ formatNumber(stats.totalUsers) }}</p>
            <div class="flex items-center mt-2">
              <span class="text-xs text-success font-medium">+12%</span>
              <span class="text-xs text-text-secondary ml-1">vs mês anterior</span>
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
            <p class="text-sm font-body text-text-secondary mb-1">Integração Completa</p>
            <p class="metric-number">{{ formatNumber(stats.completedOnboarding) }}</p>
            <div class="flex items-center mt-2">
              <span class="text-xs text-success font-medium">+8%</span>
              <span class="text-xs text-text-secondary ml-1">vs mês anterior</span>
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
            <p class="text-sm font-body text-text-secondary mb-1">Pendências</p>
            <p class="metric-number">{{ formatNumber(stats.pending) }}</p>
            <div class="flex items-center mt-2">
              <span class="text-xs text-warning font-medium">-5%</span>
              <span class="text-xs text-text-secondary ml-1">vs mês anterior</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-yellow-50 rounded-md flex items-center justify-center">
            <svg class="w-6 h-6 text-warning" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="card-metric">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-body text-text-secondary mb-1">Problemas</p>
            <p class="metric-number">{{ formatNumber(stats.issues) }}</p>
            <div class="flex items-center mt-2">
              <span class="text-xs text-error font-medium">+3</span>
              <span class="text-xs text-text-secondary ml-1">novos hoje</span>
            </div>
          </div>
          <div class="w-12 h-12 bg-red-50 rounded-md flex items-center justify-center">
            <svg class="w-6 h-6 text-error" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Recent Activity -->
      <div class="lg:col-span-2">
        <div class="card">
          <div class="flex items-center justify-between mb-6">
            <h3 class="heading-3">Atividade Recente</h3>
            <button class="btn-ghost text-sm">Ver todas</button>
          </div>
          <div class="space-y-4">
            <div 
              v-for="activity in recentActivities" 
              :key="activity.id"
              class="flex items-start p-4 rounded-sm border border-neutral-light hover:bg-gray-50 transition-colors"
            >
              <div class="flex-shrink-0 mt-1">
                <div class="w-3 h-3 rounded-full" :class="getActivityDotClass(activity.type)"></div>
              </div>
              <div class="ml-4 flex-1">
                <p class="text-sm font-body text-text-primary">{{ activity.message }}</p>
                <p class="text-xs text-text-secondary mt-1">{{ activity.time }}</p>
              </div>
              <div class="ml-4">
                <span class="badge-neutral" v-if="activity.type === 'info'">Info</span>
                <span class="badge-success" v-else-if="activity.type === 'success'">Sucesso</span>
                <span class="badge-warning" v-else-if="activity.type === 'warning'">Atenção</span>
                <span class="badge-error" v-else-if="activity.type === 'error'">Erro</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="space-y-6">
        <!-- Progress Overview -->
        <div class="card">
          <h3 class="heading-3 mb-4">Progresso Geral</h3>
          <div class="space-y-4">
            <div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-body text-text-secondary">Taxa de Conclusão</span>
                <span class="text-sm font-numbers text-text-primary">{{ completionRate }}%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-indicator" :style="{width: completionRate + '%'}"></div>
              </div>
            </div>
            <div>
              <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-body text-text-secondary">Satisfação</span>
                <span class="text-sm font-numbers text-text-primary">94%</span>
              </div>
              <div class="progress-bar">
                <div class="progress-indicator" style="width: 94%"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
          <h3 class="heading-3 mb-4">Ações Rápidas</h3>
          <div class="space-y-3">
            <button class="btn-primary w-full text-left">
              + Adicionar Utilizador
            </button>
            <button class="btn-outline w-full text-left">
              📊 Gerar Relatório
            </button>
            <button class="btn-ghost w-full text-left">
              ⚙️ Configurações
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminOverview',
  data() {
    return {
      stats: {
        totalUsers: 0,
        completedOnboarding: 0,
        pending: 0,
        issues: 0
      },
      recentActivities: []
    }
  },
  computed: {
    completionRate() {
      return Math.round((this.stats.completedOnboarding / this.stats.totalUsers) * 100)
    }
  },
  methods: {
    formatNumber(num) {
      return new Intl.NumberFormat('pt-PT').format(num)
    },
    getActivityDotClass(type) {
      const classes = {
        success: 'bg-success',
        info: 'bg-primary',
        warning: 'bg-warning',
        error: 'bg-error'
      }
      return classes[type] || 'bg-neutral-dark'
    }
  }
}
</script>