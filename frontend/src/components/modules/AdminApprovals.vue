<template>
  <div class="p-4 space-y-4">
    <h2 class="text-xl font-semibold">Fluxo de Aprovação</h2>

    <div class="border rounded p-3 space-y-2">
      <h3 class="font-medium">Novo pedido</h3>
      <div class="flex gap-2 flex-wrap">
        <input v-model="form.target" placeholder="Alvo (ex.: role:admin)" class="input" />
        <input v-model="changeKey" placeholder="Chave da mudança" class="input" />
        <input v-model="changeValue" placeholder="Valor" class="input" />
        <button class="btn" @click="addChange">Adicionar mudança</button>
        <input v-model="form.reason" placeholder="Justificativa (opcional)" class="input flex-1" />
        <button class="btn" @click="createApproval" :disabled="loading">Enviar pedido</button>
      </div>
      <div class="text-xs text-gray-600">Change set: {{ JSON.stringify(form.change_set) }}</div>
      <p v-if="error" class="text-red-600 text-sm">{{ errorMessage }}</p>
    </div>

    <div class="border rounded">
      <div class="p-2 border-b font-medium">Pedidos</div>
      <div v-if="loading" class="p-3">Carregando...</div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="text-left bg-gray-50">
            <th class="p-2">ID</th>
            <th class="p-2">Alvo</th>
            <th class="p-2">Status</th>
            <th class="p-2">Solicitante</th>
            <th class="p-2">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="it in items" :key="it.id" class="border-t">
            <td class="p-2">{{ it.id }}</td>
            <td class="p-2">{{ it.target }}</td>
            <td class="p-2">{{ it.status }}</td>
            <td class="p-2">{{ it.requester?.email }}</td>
            <td class="p-2 flex gap-2">
              <button class="btn" @click="approve(it)" :disabled="loading">Aprovar</button>
              <button class="btn-danger" @click="reject(it)" :disabled="loading">Rejeitar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue'
import { useApprovals } from '@/composables/useApprovals'

const { loading, error, items, list, create, approve, reject } = useApprovals('/api')
const form = reactive({ target: '', reason: '', change_set: {} })
const changeKey = ref('')
const changeValue = ref('')
const errorMessage = computed(() => (error.value?.message || error.value) ?? '')

onMounted(list)

const addChange = () => {
  if (!changeKey.value) return
  form.change_set[changeKey.value] = changeValue.value
  changeKey.value = ''
  changeValue.value = ''
}

const createApproval = async () => {
  if (!form.target || Object.keys(form.change_set).length === 0) return
  await create({ target: form.target, change_set: form.change_set, reason: form.reason })
  form.target = ''; form.reason=''; form.change_set = {}
}

</script>

<style scoped>
.input{ @apply border rounded px-2 py-1; }
.btn{ @apply bg-blue-600 text-white px-3 py-1 rounded; }
.btn-danger{ @apply bg-red-600 text-white px-3 py-1 rounded; }
</style>
