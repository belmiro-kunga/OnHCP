import { ref } from 'vue'

export function useReports(apiBase = '/api') {
  const loading = ref(false)
  const error = ref(null)

  const metrics = ref({
    users: null, // TODO: requires backend users endpoint
    groups: 0,
    approvals_pending: 0,
    delegations_active: 0,
    licenses_total: 0,
    licenses_assigned: 0,
    active_logins: null // TODO: not available yet
  })

  const accessLogs = ref([]) // [{user, group, system, ts}]
  const permissions = ref([]) // [{user/group, permission, status}]

  const listCounts = async () => {
    loading.value = true
    error.value = null
    try {
      const m = await fetch(`${apiBase}/reports/metrics`).then(r => r.json())
      const data = m?.data || {}
      metrics.value = {
        users: data.users ?? null,
        groups: data.groups ?? 0,
        approvals_pending: data.approvals_pending ?? 0,
        delegations_active: data.delegations_active ?? 0,
        licenses_total: data.licenses_total ?? 0,
        licenses_assigned: data.licenses_assigned ?? 0,
        active_logins: data.active_logins ?? null
      }
    } catch (e) {
      error.value = e?.message || String(e)
    } finally {
      loading.value = false
    }
  }

  const listAccess = async (params = {}) => {
    loading.value = true
    error.value = null
    try {
      const qs = new URLSearchParams(params).toString()
      const r = await fetch(`${apiBase}/reports/access${qs ? `?${qs}` : ''}`)
      const j = await r.json()
      accessLogs.value = j?.data || []
    } catch (e) {
      error.value = e?.message || String(e)
    } finally {
      loading.value = false
    }
  }

  const listPermissions = async () => {
    loading.value = true
    error.value = null
    try {
      const r = await fetch(`${apiBase}/reports/permissions`)
      const j = await r.json()
      permissions.value = j?.data || []
    } catch (e) {
      error.value = e?.message || String(e)
    } finally {
      loading.value = false
    }
  }

  // Basic CSV export utility
  const exportToCSV = (rows, filename = 'report.csv') => {
    if (!rows || rows.length === 0) return
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
    link.download = filename
    link.click()
    URL.revokeObjectURL(link.href)
  }

  // Placeholders for future Excel/PDF exports
  const exportToExcel = async (rows, filename = 'report.xlsx') => {
    try {
      const XLSX = (await import('xlsx')).default
      const ws = XLSX.utils.json_to_sheet(rows)
      const wb = XLSX.utils.book_new()
      XLSX.utils.book_append_sheet(wb, ws, 'Relatorio')
      const wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'array' })
      const blob = new Blob([wbout], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' })
      const link = document.createElement('a')
      link.href = URL.createObjectURL(blob)
      link.download = filename
      link.click()
      URL.revokeObjectURL(link.href)
    } catch (e) {
      console.warn('Falha ao exportar Excel, usando CSV como fallback:', e)
      exportToCSV(rows, filename.replace('.xlsx', '.csv'))
    }
  }

  const exportToPDF = async (rows, filename = 'report.pdf') => {
    try {
      const { jsPDF } = await import('jspdf')
      const autoTable = (await import('jspdf-autotable')).default
      const doc = new jsPDF()
      const headers = Object.keys(rows[0] || {})
      const body = rows.map(r => headers.map(h => r[h]))
      autoTable(doc, { head: [headers], body })
      doc.save(filename)
    } catch (e) {
      console.warn('Falha ao exportar PDF, usando CSV como fallback:', e)
      exportToCSV(rows, filename.replace('.pdf', '.csv'))
    }
  }

  return {
    loading,
    error,
    metrics,
    accessLogs,
    permissions,
    listCounts,
    listAccess,
    listPermissions,
    exportToCSV,
    exportToExcel,
    exportToPDF,
  }
}
