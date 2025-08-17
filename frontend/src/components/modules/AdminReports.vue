<template>
  <div>
    <!-- Report Filters -->
    <div class="card mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Relatório</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="form-label">Período</label>
          <select v-model="selectedPeriod" @change="updateReports" class="form-input">
            <option value="7">Últimos 7 dias</option>
            <option value="30">Últimos 30 dias</option>
            <option value="90">Últimos 90 dias</option>
            <option value="365">Último ano</option>
          </select>
        </div>
        <div>
          <label class="form-label">Departamento</label>
          <select v-model="selectedDepartment" @change="updateReports" class="form-input">
            <option value="">Todos</option>
            <option value="medicina">Medicina</option>
            <option value="enfermagem">Enfermagem</option>
            <option value="administrativo">Administrativo</option>
            <option value="tecnico">Técnico</option>
          </select>
        </div>
        <div>
          <label class="form-label">Status</label>
          <select v-model="selectedStatus" @change="updateReports" class="form-input">
            <option value="">Todos</option>
            <option value="ativo">Ativo</option>
            <option value="pendente">Pendente</option>
            <option value="inativo">Inativo</option>
          </select>
        </div>
        <div class="flex items-end gap-2">
          <button @click="exportCSV" class="btn-secondary w-1/3">CSV</button>
          <button @click="exportExcel" class="btn-secondary w-1/3">Excel</button>
          <button @click="exportPDF" class="btn-primary w-1/3">PDF</button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Onboarding Report -->
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Relatório de Integração</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Taxa de Conclusão</span>
            <span class="text-sm font-medium text-gray-900">{{ onboardingReport.completionRate }}%</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-green-600 h-2 rounded-full transition-all duration-500" 
              :style="{ width: onboardingReport.completionRate + '%' }"
            ></div>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Tempo Médio</span>
            <span class="text-sm font-medium text-gray-900">{{ onboardingReport.averageTime }} dias</span>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Documentos Pendentes</span>
            <span class="text-sm font-medium text-gray-900">{{ onboardingReport.pendingDocuments }}</span>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Usuários Ativos no Processo</span>
            <span class="text-sm font-medium text-gray-900">{{ onboardingReport.activeUsers }}</span>
          </div>
        </div>
      </div>

      <!-- System Statistics -->
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Estatísticas do Sistema</h3>
        <div class="space-y-4">
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Utilizadores Ativos ({{ selectedPeriod }} dias)</span>
            <span class="text-sm font-medium text-gray-900">{{ systemStats.activeUsers }}</span>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Novos Registros</span>
            <span class="text-sm font-medium text-gray-900">{{ systemStats.newRegistrations }}</span>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Taxa de Aprovação</span>
            <span class="text-sm font-medium text-gray-900">{{ systemStats.approvalRate }}%</span>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600">Documentos Processados</span>
            <span class="text-sm font-medium text-gray-900">{{ systemStats.processedDocuments }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
      <!-- Progress by Department -->
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Progresso por Departamento</h3>
        <div class="space-y-4">
          <div v-for="dept in departmentProgress" :key="dept.name" class="space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-sm font-medium text-gray-700">{{ dept.name }}</span>
              <span class="text-sm text-gray-600">{{ dept.progress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div 
                class="h-2 rounded-full transition-all duration-500"
                :class="dept.progress >= 80 ? 'bg-green-600' : dept.progress >= 50 ? 'bg-yellow-600' : 'bg-red-600'"
                :style="{ width: dept.progress + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Monthly Trends -->
      <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Tendências Mensais</h3>
        <div class="space-y-4">
          <div v-for="month in monthlyTrends" :key="month.month" class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ month.month }}</p>
              <p class="text-xs text-gray-600">{{ month.newUsers }} novos utilizadores</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-medium" :class="month.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ month.growth >= 0 ? '+' : '' }}{{ month.growth }}%
              </p>
              <p class="text-xs text-gray-600">vs mês anterior</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Table -->
    <div class="card mt-8">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Relatório Detalhado de Utilizadores</h3>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Início</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progresso</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tempo Decorrido</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="user in detailedReport" :key="user.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.department }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.startDate }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div 
                      class="h-2 rounded-full"
                      :class="user.progress === 100 ? 'bg-green-600' : user.progress > 50 ? 'bg-blue-600' : 'bg-yellow-600'"
                      :style="{ width: user.progress + '%' }"
                    ></div>
                  </div>
                  <span class="text-xs">{{ user.progress }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.elapsedTime }} dias</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  user.status === 'Completo' ? 'bg-green-100 text-green-800' :
                  user.status === 'Em Progresso' ? 'bg-blue-100 text-blue-800' :
                  user.status === 'Atrasado' ? 'bg-red-100 text-red-800' :
                  'bg-yellow-100 text-yellow-800'
                ]">
                  {{ user.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Access Logs -->
    <div class="card mt-8">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Registos de Acesso</h3>
        <div class="flex gap-2">
          <button class="btn-secondary" @click="exportAccess('csv')">CSV</button>
          <button class="btn-secondary" @click="exportAccess('xlsx')">Excel</button>
          <button class="btn-primary" @click="exportAccess('pdf')">PDF</button>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizador</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ação</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="log in accessLogs" :key="log.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.created_at }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.user?.email || log.user_id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.action }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ log.ip }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Permissions & Compliance -->
    <div class="card mt-8">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Relatório de Permissões e Conformidade</h3>
        <div class="flex gap-2">
          <button class="btn-secondary" @click="exportPerms('csv')">CSV</button>
          <button class="btn-secondary" @click="exportPerms('xlsx')">Excel</button>
          <button class="btn-primary" @click="exportPerms('pdf')">PDF</button>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprovador</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="p in permissionsReport" :key="p.id">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.requester?.email || p.requester_id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.status }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.approver?.email || p.approver_id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ p.created_at }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminReports',
  data() {
    return {
      selectedPeriod: '30',
      selectedDepartment: '',
      selectedStatus: '',
      onboardingReport: {
        completionRate: 71.5,
        averageTime: 12,
        pendingDocuments: 127,
        activeUsers: 89
      },
      systemStats: {
        activeUsers: 1089,
        newRegistrations: 43,
        approvalRate: 94.2,
        processedDocuments: 256
      },
      departmentProgress: [
        { name: 'Medicina', progress: 85 },
        { name: 'Enfermagem', progress: 78 },
        { name: 'Administrativo', progress: 92 },
        { name: 'Técnico', progress: 65 }
      ],
      monthlyTrends: [
        { month: 'Janeiro 2024', newUsers: 45, growth: 12 },
        { month: 'Dezembro 2023', newUsers: 38, growth: -5 },
        { month: 'Novembro 2023', newUsers: 52, growth: 18 },
        { month: 'Outubro 2023', newUsers: 41, growth: 8 }
      ],
      detailedReport: [
        { id: 1, name: 'Dr. João Silva', department: 'Medicina', startDate: '15/01/2024', progress: 100, elapsedTime: 8, status: 'Completo' },
        { id: 2, name: 'Dra. Maria Santos', department: 'Medicina', startDate: '12/01/2024', progress: 85, elapsedTime: 11, status: 'Em Progresso' },
        { id: 3, name: 'Dr. Pedro Costa', department: 'Medicina', startDate: '10/01/2024', progress: 45, elapsedTime: 13, status: 'Em Progresso' },
        { id: 4, name: 'Dra. Ana Lima', department: 'Enfermagem', startDate: '08/01/2024', progress: 20, elapsedTime: 15, status: 'Atrasado' },
        { id: 5, name: 'Carlos Oliveira', department: 'Técnico', startDate: '05/01/2024', progress: 90, elapsedTime: 18, status: 'Em Progresso' }
      ],
      accessLogs: [],
      permissionsReport: []
    }
  },
  methods: {
    updateReports() {
      // Simular actualização dos dados baseado nos filtros
      console.log('Atualizando relatórios com filtros:', {
        period: this.selectedPeriod,
        department: this.selectedDepartment,
        status: this.selectedStatus
      })
      
      // Aqui faria uma chamada para a API para buscar os dados filtrados
      // Por enquanto, vamos simular uma pequena mudança nos dados
      this.onboardingReport.completionRate = Math.random() * 20 + 60 // 60-80%
      this.systemStats.activeUsers = Math.floor(Math.random() * 500 + 800) // 800-1300
    },
    async fetchAccessLogs() {
      try {
        const r = await fetch('/api/reports/access')
        const j = await r.json()
        this.accessLogs = j?.data || []
      } catch (e) {
        console.warn('Falha ao carregar access logs', e)
      }
    },
    async fetchPermissionsReport() {
      try {
        const r = await fetch('/api/reports/permissions')
        const j = await r.json()
        this.permissionsReport = j?.data || []
      } catch (e) {
        console.warn('Falha ao carregar relatório de permissões', e)
      }
    },
    buildRows() {
      // Usa o relatório detalhado como base para exportação
      return this.detailedReport.map(u => ({
        id: u.id,
        nome: u.name,
        departamento: u.department,
        data_inicio: u.startDate,
        progresso: u.progress,
        tempo_dias: u.elapsedTime,
        status: u.status
      }))
    },
    buildAccessRows() {
      return this.accessLogs.map(l => ({
        id: l.id,
        data: l.created_at,
        utilizador: l.user?.email || l.user_id,
        acao: l.action,
        ip: l.ip
      }))
    },
    buildPermRows() {
      return this.permissionsReport.map(p => ({
        id: p.id,
        solicitante: p.requester?.email || p.requester_id,
        status: p.status,
        aprovador: p.approver?.email || p.approver_id,
        data: p.created_at
      }))
    },
    exportCSV() {
      const rows = this.buildRows()
      if (!rows.length) return
      const headers = Object.keys(rows[0])
      const csvRows = [headers.join(',')]
      for (const r of rows) {
        const line = headers.map(h => {
          const v = r[h] ?? ''
          const s = typeof v === 'object' ? JSON.stringify(v) : String(v)
          return '"' + s.replaceAll('"', '""') + '"'
        }).join(',')
        csvRows.push(line)
      }
      const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      link.href = URL.createObjectURL(blob)
      link.download = `relatorio_utilizadores_${Date.now()}.csv`
      link.click()
      URL.revokeObjectURL(link.href)
    },
    exportExcel() {
      console.warn('Exportação para Excel requer a biblioteca xlsx. Sugestão: npm i xlsx')
      this.exportCSV()
    },
    exportPDF() {
      console.warn('Exportação para PDF requer jsPDF + autotable. Sugestão: npm i jspdf jspdf-autotable')
      this.exportCSV()
    },
    async exportAccess(kind) {
      const rows = this.buildAccessRows()
      if (kind === 'xlsx') {
        const XLSX = (await import('xlsx')).default
        const ws = XLSX.utils.json_to_sheet(rows)
        const wb = XLSX.utils.book_new()
        XLSX.utils.book_append_sheet(wb, ws, 'Acessos')
        const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' })
        const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const link = document.createElement('a')
        link.href = URL.createObjectURL(blob)
        link.download = `acessos_${Date.now()}.xlsx`
        link.click()
        URL.revokeObjectURL(link.href)
        return
      }
      if (kind === 'pdf') {
        const { jsPDF } = await import('jspdf')
        const autoTable = (await import('jspdf-autotable')).default
        const doc = new jsPDF()
        const headers = Object.keys(rows[0] || {})
        const body = rows.map(r => headers.map(h => r[h]))
        autoTable(doc, { head: [headers], body })
        doc.save(`acessos_${Date.now()}.pdf`)
        return
      }
      // default CSV
      const headers = Object.keys(rows[0] || {})
      const csvRows = [headers.join(',')]
      for (const r of rows) {
        const line = headers.map(h => '"' + String(r[h] ?? '').replaceAll('"','""') + '"').join(',')
        csvRows.push(line)
      }
      const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      link.href = URL.createObjectURL(blob)
      link.download = `acessos_${Date.now()}.csv`
      link.click()
      URL.revokeObjectURL(link.href)
    },
    async exportPerms(kind) {
      const rows = this.buildPermRows()
      if (kind === 'xlsx') {
        const XLSX = (await import('xlsx')).default
        const ws = XLSX.utils.json_to_sheet(rows)
        const wb = XLSX.utils.book_new()
        XLSX.utils.book_append_sheet(wb, ws, 'Permissoes')
        const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' })
        const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
        const link = document.createElement('a')
        link.href = URL.createObjectURL(blob)
        link.download = `permissoes_${Date.now()}.xlsx`
        link.click()
        URL.revokeObjectURL(link.href)
        return
      }
      if (kind === 'pdf') {
        const { jsPDF } = await import('jspdf')
        const autoTable = (await import('jspdf-autotable')).default
        const doc = new jsPDF()
        const headers = Object.keys(rows[0] || {})
        const body = rows.map(r => headers.map(h => r[h]))
        autoTable(doc, { head: [headers], body })
        doc.save(`permissoes_${Date.now()}.pdf`)
        return
      }
      // default CSV
      const headers = Object.keys(rows[0] || {})
      const csvRows = [headers.join(',')]
      for (const r of rows) {
        const line = headers.map(h => '"' + String(r[h] ?? '').replaceAll('"','""') + '"').join(',')
        csvRows.push(line)
      }
      const blob = new Blob([csvRows.join('\n')], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      link.href = URL.createObjectURL(blob)
      link.download = `permissoes_${Date.now()}.csv`
      link.click()
      URL.revokeObjectURL(link.href)
    }
  },
  mounted() {
    this.updateReports()
    this.fetchAccessLogs()
    this.fetchPermissionsReport()
  }
}
</script>