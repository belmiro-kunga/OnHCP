<template>
  <section class="p-4 md:p-6">
    <header class="mb-4 flex items-center justify-between">
      <h2 class="text-xl md:text-2xl font-semibold text-text-primary">Gestão de Categorias de Simulados</h2>
      <div class="flex items-center gap-2">
        <button
          class="px-3 py-2 rounded-md border hover:bg-muted"
          :disabled="loading"
          @click="openCreateModal()"
        >
          Nova Categoria
        </button>
        <button
          class="px-3 py-2 rounded-md bg-primary text-white disabled:opacity-60"
          :disabled="loading"
          @click="loadCategories"
        >
          Recarregar
        </button>
        <button
          class="px-3 py-2 rounded-md border hover:bg-muted"
          @click="$router.push('/admin/dashboard/simulado')"
          title="Voltar aos Simulados"
        >
          Simulados
        </button>
      </div>
    </header>

    <div v-if="error" class="mb-4 p-3 rounded-md border border-red-300 bg-red-50 text-red-700">
      {{ error }}
    </div>

    <div v-if="loading" class="mb-4 p-3 rounded-md border border-border bg-surface text-text-secondary">
      A carregar categorias...
    </div>

    <div class="rounded-lg border border-border overflow-hidden">
      <table class="min-w-full divide-y divide-border">
        <thead class="bg-muted/50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Nome</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Data de Criação</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-surface divide-y divide-border">
          <tr v-if="!categories.length && !loading" class="text-center">
            <td colspan="3" class="px-4 py-8 text-text-secondary">
              Nenhuma categoria encontrada
            </td>
          </tr>
          <tr v-for="category in categories" :key="category.id" class="hover:bg-muted/30">
            <td class="px-4 py-3 text-sm text-text-primary font-medium">
              {{ category.name }}
            </td>
            <td class="px-4 py-3 text-sm text-text-secondary">
              {{ formatDate(category.created_at) }}
            </td>
            <td class="px-4 py-3 text-sm">
              <div class="flex items-center gap-2">
                <button
                  class="px-2 py-1 text-xs rounded border hover:bg-muted"
                  @click="openEditModal(category)"
                >
                  Editar
                </button>
                <button
                  class="px-2 py-1 text-xs rounded border border-red-300 text-red-600 hover:bg-red-50"
                  @click="confirmDelete(category)"
                >
                  Excluir
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Criar/Editar Categoria -->
    <div v-if="showModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-md rounded-lg shadow-lg border border-border bg-surface">
        <div class="px-4 py-3 border-b border-border flex items-center justify-between">
          <h3 class="font-semibold text-text-primary">
            {{ isEditing ? 'Editar Categoria' : 'Nova Categoria' }}
          </h3>
          <button class="p-1 rounded hover:bg-muted" @click="closeModal">✕</button>
        </div>
        <form @submit.prevent="saveCategory" class="p-4">
          <div v-if="modalError" class="mb-4 p-3 rounded-md border border-red-300 bg-red-50 text-red-700 text-sm">
            {{ modalError }}
          </div>
          <div class="grid gap-3">
            <label class="grid gap-1">
              <span class="text-sm font-medium text-text-primary">Nome da Categoria *</span>
              <input
                v-model="form.name"
                type="text"
                class="border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary"
                placeholder="Digite o nome da categoria"
                required
                maxlength="255"
              />
            </label>
          </div>
          <div class="flex justify-end gap-2 mt-6">
            <button
              type="button"
              class="px-4 py-2 rounded-md border hover:bg-muted"
              @click="closeModal"
            >
              Cancelar
            </button>
            <button
              type="submit"
              class="px-4 py-2 rounded-md bg-primary text-white hover:opacity-90 disabled:opacity-60"
              :disabled="saving || !form.name.trim()"
            >
              {{ saving ? 'Salvando...' : (isEditing ? 'Atualizar' : 'Criar') }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Confirmação de Exclusão -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
      <div class="w-full max-w-md rounded-lg shadow-lg border border-border bg-surface">
        <div class="px-4 py-3 border-b border-border">
          <h3 class="font-semibold text-text-primary">Confirmar Exclusão</h3>
        </div>
        <div class="p-4">
          <p class="text-text-secondary mb-4">
            Tem certeza que deseja excluir a categoria "{{ categoryToDelete?.name }}"?
          </p>
          <p class="text-sm text-red-600 mb-4">
            Esta ação não pode ser desfeita.
          </p>
          <div class="flex justify-end gap-2">
            <button
              type="button"
              class="px-4 py-2 rounded-md border hover:bg-muted"
              @click="closeDeleteModal"
            >
              Cancelar
            </button>
            <button
              type="button"
              class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700 disabled:opacity-60"
              :disabled="deleting"
              @click="deleteCategory"
            >
              {{ deleting ? 'Excluindo...' : 'Excluir' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted } from 'vue'
import { adminSimuladoApi } from '../../composables/adminSimuladoApi'

export default {
  name: 'AdminSimuladoCategories',
  setup() {
    const categories = ref([])
    const loading = ref(false)
    const error = ref(null)
    
    // Modal states
    const showModal = ref(false)
    const isEditing = ref(false)
    const saving = ref(false)
    const modalError = ref(null)
    const form = ref({
      id: null,
      name: ''
    })
    
    // Delete modal states
    const showDeleteModal = ref(false)
    const categoryToDelete = ref(null)
    const deleting = ref(false)
    
    const loadCategories = async () => {
      loading.value = true
      error.value = null
      try {
        categories.value = await adminSimuladoApi.listCategories()
      } catch (e) {
        error.value = e?.response?.data?.message || e.message || 'Erro ao carregar categorias'
      } finally {
        loading.value = false
      }
    }
    
    const openCreateModal = () => {
      isEditing.value = false
      form.value = { id: null, name: '' }
      modalError.value = null
      showModal.value = true
    }
    
    const openEditModal = (category) => {
      isEditing.value = true
      form.value = { id: category.id, name: category.name }
      modalError.value = null
      showModal.value = true
    }
    
    const closeModal = () => {
      showModal.value = false
      form.value = { id: null, name: '' }
      modalError.value = null
    }
    
    const saveCategory = async () => {
      if (!form.value.name.trim()) return
      
      saving.value = true
      modalError.value = null
      
      try {
        if (isEditing.value) {
          // Para edição, precisaremos implementar a API de update
          await adminSimuladoApi.updateCategory(form.value.id, form.value.name.trim())
        } else {
          await adminSimuladoApi.createCategory(form.value.name.trim())
        }
        
        closeModal()
        await loadCategories()
      } catch (e) {
        if (e.response?.status === 422) {
          const errors = e.response.data.errors
          if (errors?.name) {
            modalError.value = errors.name[0]
          } else {
            modalError.value = 'Dados inválidos'
          }
        } else {
          modalError.value = e?.response?.data?.error || e?.response?.data?.message || e.message || 'Erro ao salvar categoria'
        }
      } finally {
        saving.value = false
      }
    }
    
    const confirmDelete = (category) => {
      categoryToDelete.value = category
      showDeleteModal.value = true
    }
    
    const closeDeleteModal = () => {
      showDeleteModal.value = false
      categoryToDelete.value = null
    }
    
    const deleteCategory = async () => {
      if (!categoryToDelete.value) return
      
      deleting.value = true
      
      try {
        // Para exclusão, precisaremos implementar a API de delete
        await adminSimuladoApi.deleteCategory(categoryToDelete.value.id)
        closeDeleteModal()
        await loadCategories()
      } catch (e) {
        if (e.response?.status === 422) {
          error.value = e.response.data.error || 'Não é possível excluir esta categoria'
        } else {
          error.value = e?.response?.data?.error || e?.response?.data?.message || e.message || 'Erro ao excluir categoria'
        }
      } finally {
        deleting.value = false
      }
    }
    
    const formatDate = (dateString) => {
      if (!dateString) return 'N/A'
      try {
        return new Date(dateString).toLocaleDateString('pt-PT', {
          year: 'numeric',
          month: '2-digit',
          day: '2-digit',
          hour: '2-digit',
          minute: '2-digit'
        })
      } catch {
        return 'Data inválida'
      }
    }
    
    onMounted(() => {
      loadCategories()
    })
    
    return {
      categories,
      loading,
      error,
      showModal,
      isEditing,
      saving,
      modalError,
      form,
      showDeleteModal,
      categoryToDelete,
      deleting,
      loadCategories,
      openCreateModal,
      openEditModal,
      closeModal,
      saveCategory,
      confirmDelete,
      closeDeleteModal,
      deleteCategory,
      formatDate
    }
  }
}
</script>

<style scoped>
.bg-muted\/50 { background-color: rgba(0,0,0,0.03); }
.bg-black\/40 { background-color: rgba(0,0,0,0.4); }
.bg-surface { background-color: #ffffff; }
</style>