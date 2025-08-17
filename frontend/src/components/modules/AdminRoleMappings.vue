<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Mapeamentos de Funções</h3>
      <button class="btn-primary" @click="openCreate = true">+ Novo Mapeamento</button>
    </div>

    <div class="card mb-6">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-md font-semibold text-gray-900">Lista</h4>
        <button class="btn-secondary" @click="fetchAll">Recarregar</button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cargo (Job Title)</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grupo AD</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Função</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prioridade</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ativo</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="rm in mappings" :key="rm.id">
              <td class="px-6 py-4 text-sm">{{ displayDept(rm.department) }}</td>
              <td class="px-6 py-4 text-sm">{{ rm.job_title || '-' }}</td>
              <td class="px-6 py-4 text-sm">{{ rm.ad_group || '-' }}</td>
              <td class="px-6 py-4 text-sm">{{ rm.role?.name || rm.role_id }}</td>
              <td class="px-6 py-4 text-sm">{{ rm.priority }}</td>
              <td class="px-6 py-4 text-sm">
                <span :class="rm.active ? 'text-green-600' : 'text-gray-500'">{{ rm.active ? 'Sim' : 'Não' }}</span>
              </td>
              <td class="px-6 py-4 text-right">
                <button class="btn-secondary mr-2" @click="startEdit(rm)">Editar</button>
                <button class="btn-secondary" @click="removeMapping(rm)">Remover</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="openCreate || editItem" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="closeModal" role="dialog" aria-modal="true" aria-labelledby="rmTitle">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" tabindex="-1">
        <div class="mt-1">
          <h3 id="rmTitle" class="text-lg font-medium text-gray-900 mb-4">{{ editItem ? 'Editar' : 'Novo' }} Mapeamento</h3>
          <form @submit.prevent="save" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
              <select v-model="form.department_id" class="form-input w-full">
                <option :value="''">-</option>
                <option v-for="opt in deptOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cargo (Job Title)</label>
              <input v-model="form.job_title" type="text" class="form-input w-full" placeholder="opcional" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Grupo AD</label>
              <input v-model="form.ad_group" list="ad-groups" class="form-input w-full" placeholder="opcional" />
              <datalist id="ad-groups">
                <option v-for="g in adGroups" :key="g.name" :value="g.name">{{ g.name }}</option>
              </datalist>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Função</label>
              <select v-model="form.role_id" class="form-input w-full" required>
                <option v-for="opt in roleOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prioridade</label>
                <input v-model.number="form.priority" type="number" class="form-input w-full" />
              </div>
              <div class="flex items-center mt-6">
                <label class="inline-flex items-center text-sm">
                  <input type="checkbox" class="form-checkbox mr-2" v-model="form.active" /> Ativo
                </label>
              </div>
            </div>
            <div class="flex justify-end gap-3">
              <button type="button" class="btn-secondary" @click="closeModal">Cancelar</button>
              <button type="submit" class="btn-primary">Guardar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import api from '../../composables/api'
import { useRoleMappings } from '../../composables/useRoleMappings'
import { useRoles } from '../../composables/useRoles'
import { useDepartments } from '../../composables/useDepartments'

export default {
  name: 'AdminRoleMappings',
  setup() {
    const { list, create, update, remove } = useRoleMappings()
    const { getRoleOptions } = useRoles()
    const { getDepartmentOptions } = useDepartments()

    const mappings = ref([])
    const roleOptions = ref([])
    const deptOptions = ref([])
    const adGroups = ref([])

    const openCreate = ref(false)
    const editItem = ref(null)
    const form = ref({ department_id: '', job_title: '', ad_group: '', role_id: '', priority: 100, active: true })

    const fetchAll = async () => {
      const res = await list(); mappings.value = res.data || []
      roleOptions.value = (await getRoleOptions())?.data || []
      deptOptions.value = (await getDepartmentOptions())?.data || []
      adGroups.value = (await api.get('/directory/groups')).data?.data || []
    }

    const startEdit = (rm) => {
      editItem.value = rm
      openCreate.value = false
      form.value = {
        department_id: rm.department_id ?? '',
        job_title: rm.job_title || '',
        ad_group: rm.ad_group || '',
        role_id: rm.role_id,
        priority: rm.priority ?? 100,
        active: !!rm.active,
      }
    }

    const closeModal = () => { openCreate.value = false; editItem.value = null; form.value = { department_id: '', job_title: '', ad_group: '', role_id: '', priority: 100, active: true } }

    const save = async () => {
      if (editItem.value) {
        await update(editItem.value.id, form.value)
      } else {
        await create(form.value)
      }
      await fetchAll(); closeModal()
    }

    const removeMapping = async (rm) => {
      await remove(rm.id)
      await fetchAll()
    }

    const displayDept = (d) => d?.name || d?.label || d?.title || d?.id || '-'

    onMounted(fetchAll)

    return { mappings, roleOptions, deptOptions, adGroups, openCreate, editItem, form, fetchAll, startEdit, closeModal, save, removeMapping, displayDept }
  }
}
</script>
