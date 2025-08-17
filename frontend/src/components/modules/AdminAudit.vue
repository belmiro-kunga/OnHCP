<template>
  <div class="space-y-6">
    <!-- Header / Actions -->
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold text-gray-900">Monitoramento e Auditoria</h2>
      <div class="flex gap-2">
        <button v-if="can('audit.export')" @click="exportAuditTrail" class="btn-secondary">📤 Exportar Trilha (CSV)</button>
      </div>
    </div>

    <!-- Access Gate -->
    <div v-if="!can('audit.view')" class="p-6 bg-red-50 border border-red-200 rounded-lg text-red-800">
      <h3 class="text-lg font-semibold mb-2">Acesso negado</h3>
      <p>Você não possui permissão para visualizar os registos de auditoria.</p>
    </div>

    <div v-else class="space-y-8">
      <!-- Activity Logs -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Log de Atividades</h3>
          <div class="flex gap-2">
            <input v-model="filters.search" placeholder="Pesquisar por utilizador/ação/recurso..." class="form-input w-64" />
            <select v-model="filters.severity" class="form-input w-40">
              <option value="">Todas severidades</option>
              <option value="low">Baixa</option>
              <option value="medium">Média</option>
              <option value="high">Alta</option>
            </select>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quando</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilizador</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ação</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recurso</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origem</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severidade</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="log in filteredActivityLogs" :key="log.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.timestamp }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ log.user }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ log.action }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ log.resource }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.ip }} · {{ log.userAgent }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="severityClass(log.severity)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">{{ log.severity }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Login History -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Histórico de Login</h3>
          <div class="text-sm text-gray-500">Total: {{ loginHistory.length }}</div>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quando</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilizador</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Navegador</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dispositivo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="entry in loginHistory" :key="entry.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ entry.timestamp }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ entry.user }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ entry.ip }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ entry.browser }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ entry.device }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="entry.success ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                    {{ entry.success ? 'Sucesso' : 'Falha' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Security Alerts -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Alertas de Segurança</h3>
          <div class="text-sm text-gray-500">Últimas 48h</div>
        </div>
        <ul class="space-y-3">
          <li v-for="alert in securityAlerts" :key="alert.id" class="flex items-start gap-3">
            <span :class="severityDot(alert.severity)" class="inline-block w-2.5 h-2.5 rounded-full mt-2"></span>
            <div>
              <div class="text-sm text-gray-900">
                <strong>{{ alert.type }}</strong> · {{ alert.message }}
              </div>
              <div class="text-xs text-gray-500">{{ alert.timestamp }} · {{ alert.ip }} · {{ alert.userAgent }}</div>
            </div>
          </li>
        </ul>
      </div>

      <!-- Access/Permissions Reports -->
      <div class="card">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium text-gray-900">Relatórios de Acessos e Permissões</h3>
          <button v-if="can('audit.export')" @click="exportAccessReport" class="btn-secondary">📊 Exportar Relatório (CSV)</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-gray-900 mb-2">Ações por Severidade</h4>
            <ul class="text-sm text-gray-700 space-y-1">
              <li v-for="(count, sev) in stats.bySeverity" :key="sev">{{ sev }}: <strong>{{ count }}</strong></li>
            </ul>
          </div>
          <div class="p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-gray-900 mb-2">Logins por Status</h4>
            <ul class="text-sm text-gray-700 space-y-1">
              <li>Sucesso: <strong>{{ stats.logins.success }}</strong></li>
              <li>Falha: <strong>{{ stats.logins.fail }}</strong></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Compliance Note -->
      <div class="p-4 bg-blue-50 border border-blue-100 rounded-lg text-sm text-blue-900">
        <strong>Conformidade</strong>: Trilha de auditoria exportável para LGPD, GDPR, ISO 27001. Para produção, armazene logs de forma imutável com retenção e hash/assinatura.
      </div>
    </div>
  </div>
</template>

<script>
import { computed, reactive } from 'vue'
import { usePermissions } from '../../composables/usePermissions'

export default {
  name: 'AdminAudit',
  setup() {
    const { can } = usePermissions()

    const filters = reactive({ search: '', severity: '' })

    // Demo/mock datasets
    const activityLogs = reactive([
      { id: 1, timestamp: '2025-08-15 09:31', user: 'admin', action: 'update', resource: 'users/5', ip: '192.168.0.10', userAgent: 'Chrome/Windows', severity: 'medium' },
      { id: 2, timestamp: '2025-08-15 10:05', user: 'manager@corp', action: 'view', resource: 'users', ip: '192.168.0.22', userAgent: 'Firefox/Linux', severity: 'low' },
      { id: 3, timestamp: '2025-08-16 08:12', user: 'guest', action: 'login_failed', resource: 'auth', ip: '10.1.2.3', userAgent: 'Safari/iOS', severity: 'high' },
      { id: 4, timestamp: '2025-08-16 09:01', user: 'admin', action: 'export', resource: 'permissions', ip: '192.168.0.10', userAgent: 'Chrome/Windows', severity: 'low' },
    ])

    const loginHistory = reactive([
      { id: 1, timestamp: '2025-08-16 08:00', user: 'admin', ip: '192.168.0.10', browser: 'Chrome', device: 'Windows', success: true },
      { id: 2, timestamp: '2025-08-16 08:05', user: 'guest', ip: '10.1.2.3', browser: 'Safari', device: 'iPhone', success: false },
      { id: 3, timestamp: '2025-08-16 09:15', user: 'manager@corp', ip: '192.168.0.22', browser: 'Firefox', device: 'Linux', success: true },
    ])

    const securityAlerts = reactive([
      { id: 1, type: 'Falhas de Login', message: '5 tentativas falhas em 10min para guest', timestamp: '2025-08-16 08:10', ip: '10.1.2.3', userAgent: 'Safari/iOS', severity: 'high' },
      { id: 2, type: 'Acesso Suspeito', message: 'Sessão iniciada de nova localização', timestamp: '2025-08-16 09:20', ip: '200.200.10.5', userAgent: 'Chrome/Android', severity: 'medium' },
    ])

    const filteredActivityLogs = computed(() => {
      const q = filters.search.trim().toLowerCase()
      return activityLogs.filter(l => {
        const matchesQ = !q || `${l.user} ${l.action} ${l.resource}`.toLowerCase().includes(q)
        const matchesSev = !filters.severity || l.severity === filters.severity
        return matchesQ && matchesSev
      })
    })

    const stats = computed(() => {
      const bySeverity = filteredActivityLogs.value.reduce((acc, l) => {
        acc[l.severity] = (acc[l.severity] || 0) + 1
        return acc
      }, {})
      const logins = loginHistory.reduce((acc, e) => {
        if (e.success) acc.success++
        else acc.fail++
        return acc
      }, { success: 0, fail: 0 })
      return { bySeverity, logins }
    })

    function severityClass(sev) {
      return sev === 'high' ? 'bg-red-100 text-red-800'
        : sev === 'medium' ? 'bg-yellow-100 text-yellow-800'
        : 'bg-green-100 text-green-800'
    }

    function severityDot(sev) {
      return sev === 'high' ? 'bg-red-500' : sev === 'medium' ? 'bg-yellow-500' : 'bg-green-500'
    }

    function toCSV(rows) {
      const headers = Object.keys(rows[0] || {})
      const csv = [headers.join(',')]
      rows.forEach(r => {
        csv.push(headers.map(h => `${(r[h] ?? '').toString().replaceAll('"', '""')}`).join(','))
      })
      return csv.join('\n')
    }

    function downloadCSV(filename, csvContent) {
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', filename)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
    }

    function exportAuditTrail() {
      if (!can('audit.export')) return
      const csv = toCSV([...filteredActivityLogs.value])
      downloadCSV(`trilha_auditoria_${new Date().toISOString().split('T')[0]}.csv`, csv)
    }

    function exportAccessReport() {
      if (!can('audit.export')) return
      const rows = [
        { metric: 'logins_sucesso', value: stats.value.logins.success },
        { metric: 'logins_falha', value: stats.value.logins.fail },
        ...Object.entries(stats.value.bySeverity).map(([sev, count]) => ({ metric: `acoes_${sev}`, value: count }))
      ]
      const csv = toCSV(rows)
      downloadCSV(`relatorio_acessos_${new Date().toISOString().split('T')[0]}.csv`, csv)
    }

    return {
      can,
      filters,
      activityLogs,
      loginHistory,
      securityAlerts,
      filteredActivityLogs,
      stats,
      severityClass,
      severityDot,
      exportAuditTrail,
      exportAccessReport,
    }
  }
}
</script>
