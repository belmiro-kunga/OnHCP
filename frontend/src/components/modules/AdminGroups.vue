<template>
  <div class="p-4 space-y-4">
    <h2 class="text-xl font-semibold">Grupos / Times</h2>

    <div class="border rounded p-3 space-y-2">
      <h3 class="font-medium">Criar grupo</h3>
      <div class="flex gap-2 flex-wrap">
        <input v-model="form.name" placeholder="Nome" class="input" />
        <input v-model="form.description" placeholder="Descrição" class="input flex-1" />
        <button class="btn" @click="createGroup" :disabled="loading">Criar</button>
      </div>
      <p v-if="error" class="text-red-600 text-sm">{{ errorMessage }}</p>
    </div>

    <div class="border rounded">
      <div class="p-2 border-b font-medium">Lista de grupos</div>
      <div v-if="loading" class="p-3">Carregando...</div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="text-left bg-gray-50">
            <th class="p-2">Nome</th>
            <th class="p-2">Descrição</th>
            <th class="p-2">Membros</th>
            <th class="p-2">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="g in groups" :key="g.id" class="border-t">
            <td class="p-2">
              <input v-if="edit.id===g.id" v-model="edit.name" class="input" />
              <span v-else>{{ g.name }}</span>
            </td>
            <td class="p-2">
              <input v-if="edit.id===g.id" v-model="edit.description" class="input" />
              <span v-else>{{ g.description }}</span>
            </td>
            <td class="p-2">{{ g.users_count ?? g.usersCount ?? '-' }}</td>
            <td class="p-2 flex gap-2">
              <button class="btn-secondary" @click="selectGroup(g)">Membros</button>
              <button v-if="edit.id!==g.id" class="btn-secondary" @click="startEdit(g)">Editar</button>
              <button v-else class="btn" @click="saveEdit">Salvar</button>
              <button class="btn-danger" @click="removeGroup(g)">Excluir</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="selected" class="border rounded p-3 space-y-3">
      <div class="flex items-center justify-between">
        <h3 class="font-medium">Membros de {{ selected.name }}</h3>
        <button class="btn-secondary" @click="selected=null">Fechar</button>
      </div>
      <div class="flex gap-2 flex-wrap items-end">
        <input v-model.number="memberForm.user_id" placeholder="user_id" type="number" class="input w-36" />
        <input v-model="memberForm.role" placeholder="Função (opcional)" class="input w-56" />
        <button class="btn" @click="addMemberToSelected" :disabled="loading">Adicionar</button>
      </div>
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left bg-gray-50">
            <th class="p-2">ID</th>
            <th class="p-2">Nome</th>
            <th class="p-2">Email</th>
            <th class="p-2">Função</th>
            <th class="p-2">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="m in members" :key="m.id" class="border-t">
            <td class="p-2">{{ m.id }}</td>
            <td class="p-2">{{ m.name }}</td>
            <td class="p-2">{{ m.email }}</td>
            <td class="p-2">{{ m.role }}</td>
            <td class="p-2">
              <button class="btn-danger" @click="removeMemberFromSelected(m)">Remover</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref, computed } from 'vue'
import { useGroups } from '@/composables/useGroups'

const { loading, error, groups, members, list, create, update, remove, listMembers, addMember, removeMember } = useGroups('/api')

const form = reactive({ name: '', description: '' })
const edit = reactive({ id: null, name: '', description: '' })
const selected = ref(null)
const memberForm = reactive({ user_id: null, role: '' })
const errorMessage = computed(() => (error.value?.message || error.value) ?? '')

onMounted(list)

const createGroup = async () => {
  if (!form.name) return
  await create({ name: form.name, description: form.description })
  form.name = ''; form.description=''
}

const startEdit = (g) => { edit.id = g.id; edit.name = g.name; edit.description = g.description }
const saveEdit = async () => { await update(edit.id, { name: edit.name, description: edit.description }); edit.id = null }
const removeGroup = async (g) => { await remove(g.id); if (selected.value?.id===g.id) selected.value=null }

const selectGroup = async (g) => { selected.value = g; await listMembers(g.id) }
const addMemberToSelected = async () => { if (!selected.value) return; await addMember(selected.value.id, { user_id: memberForm.user_id, role: memberForm.role }); memberForm.user_id=null; memberForm.role='' }
const removeMemberFromSelected = async (m) => { if (!selected.value) return; await removeMember(selected.value.id, m.id) }
</script>

<style scoped>
.input{ @apply border rounded px-2 py-1; }
.btn{ @apply bg-blue-600 text-white px-3 py-1 rounded; }
.btn-secondary{ @apply bg-gray-200 px-3 py-1 rounded; }
.btn-danger{ @apply bg-red-600 text-white px-3 py-1 rounded; }
</style>
