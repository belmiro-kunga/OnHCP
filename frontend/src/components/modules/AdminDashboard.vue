<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="text-xl font-semibold">Dashboard Administrativo</h2>
      <button class="btn-secondary" @click="listCounts" :disabled="loading">Atualizar</button>
    </div>

    <div v-if="error" class="p-3 rounded bg-red-50 text-red-700 text-sm">{{ error }}</div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="card">
        <div class="text-sm text-gray-500">Grupos</div>
        <div class="text-2xl font-bold">{{ metrics.groups }}</div>
      </div>
      <div class="card">
        <div class="text-sm text-gray-500">Aprovações Pendentes</div>
        <div class="text-2xl font-bold text-yellow-600">{{ metrics.approvals_pending }}</div>
      </div>
      <div class="card">
        <div class="text-sm text-gray-500">Delegações Ativas</div>
        <div class="text-2xl font-bold text-blue-600">{{ metrics.delegations_active }}</div>
      </div>
      <div class="card">
        <div class="text-sm text-gray-500">Licenças (Total)</div>
        <div class="text-2xl font-bold">{{ metrics.licenses_total }}</div>
      </div>
      <div class="card">
        <div class="text-sm text-gray-500">Licenças Atribuídas</div>
        <div class="text-2xl font-bold text-green-600">{{ metrics.licenses_assigned }}</div>
      </div>
      <div class="card">
        <div class="text-sm text-gray-500">Logins Ativos</div>
        <div class="text-2xl font-bold">{{ metrics.active_logins ?? '—' }}</div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useReports } from '@/composables/useReports'

const { loading, error, metrics, listCounts } = useReports('/api')

onMounted(() => {
  listCounts()
})
</script>
