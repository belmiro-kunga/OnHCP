<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">Cadastro de Novo Funcionário</h1>
      <p class="text-gray-600 mt-1">Insira os dados do novo funcionário e configure seu onboarding</p>
    </div>

    <!-- Form -->
    <form @submit.prevent="submitForm" class="space-y-8">
      <!-- Personal Information -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
          </svg>
          Informações Pessoais
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Completo *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Nome completo do funcionário"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="email@empresa.com"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Bilhete de Identidade *</label>
            <input
              v-model="form.bi"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="000000000LA000"
              maxlength="14"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
            <input
              v-model="form.phone"
              type="text"
              v-mask="'+244 ### ### ###'"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="+244 900 000 000"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
            <input
              v-model="form.birth_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gênero</label>
            <select
              v-model="form.gender"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Selecione</option>
              <option value="male">Masculino</option>
              <option value="female">Feminino</option>
              <option value="other">Outro</option>
              <option value="prefer_not_to_say">Prefiro não informar</option>
            </select>
          </div>
        </div>
        
        <!-- Address -->
        <h3 class="text-md font-medium text-gray-900 mt-6 mb-4">Endereço</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
            <input
              v-model="form.address"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Rua, número, complemento"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
            <input
              v-model="form.city"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Cidade"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Código Postal</label>
            <input
              v-model="form.zip_code"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Luanda"
            >
          </div>
        </div>
      </div>

      <!-- Professional Information -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2V6"></path>
          </svg>
          Informações Profissionais
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cargo *</label>
            <input
              v-model="form.position"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="Cargo do funcionário"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Departamento *</label>
            <select
              v-model="form.department_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Selecione o departamento</option>
              <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                {{ dept.name }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gestor Direto</label>
            <select
              v-model="form.manager_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Selecione o gestor</option>
              <option v-for="manager in managers" :key="manager.id" :value="manager.id">
                {{ manager.name }} - {{ manager.position }}
              </option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Admissão *</label>
            <input
              v-model="form.hire_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Salário (AOA)</label>
            <input
              v-model="form.salary"
              type="number"
              step="0.01"
              min="0"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              placeholder="0.00 Kz"
            >
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Contrato</label>
            <select
              v-model="form.contract_type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Selecione</option>
              <option value="permanente">Contrato Permanente</option>
              <option value="termo_certo">Contrato a Termo Certo</option>
              <option value="termo_incerto">Contrato a Termo Incerto</option>
              <option value="estagio">Estágio</option>
              <option value="temporario">Trabalho Temporário</option>
              <option value="prestacao_servicos">Prestação de Serviços</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Onboarding Configuration -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
          </svg>
          Configuração de Onboarding
        </h2>
        
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Roteiro de Onboarding</label>
            <select
              v-model="form.onboarding_roadmap_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Selecione um roteiro (opcional)</option>
              <option v-for="roadmap in filteredRoadmaps" :key="roadmap.id" :value="roadmap.id">
                {{ roadmap.name }} - {{ roadmap.department?.name }} ({{ roadmap.estimated_duration }} dias)
              </option>
            </select>
            <p class="text-sm text-gray-500 mt-1">
              O roteiro será atribuído automaticamente ao funcionário após o cadastro
            </p>
          </div>
          
          <div v-if="selectedRoadmap">
            <h3 class="text-md font-medium text-gray-900 mb-2">Detalhes do Roteiro Selecionado</h3>
            <div class="bg-purple-50 p-4 rounded-lg">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                <div>
                  <p class="text-sm text-purple-600">Nome</p>
                  <p class="font-medium text-purple-900">{{ selectedRoadmap.name }}</p>
                </div>
                <div>
                  <p class="text-sm text-purple-600">Duração Estimada</p>
                  <p class="font-medium text-purple-900">{{ selectedRoadmap.estimated_duration }} dias</p>
                </div>
                <div>
                  <p class="text-sm text-purple-600">Total de Etapas</p>
                  <p class="font-medium text-purple-900">{{ selectedRoadmap.steps?.length || 0 }}</p>
                </div>
              </div>
              <div v-if="selectedRoadmap.description">
                <p class="text-sm text-purple-600">Descrição</p>
                <p class="text-purple-900">{{ selectedRoadmap.description }}</p>
              </div>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Início do Onboarding</label>
            <input
              v-model="form.onboarding_start_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <p class="text-sm text-gray-500 mt-1">
              Se não informada, será considerada a data de admissão
            </p>
          </div>
        </div>
      </div>

      <!-- Access Configuration -->
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
          </svg>
          Configuração de Acesso
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="flex items-center space-x-3">
              <input
                v-model="form.send_welcome_email"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <span class="text-sm text-gray-700">
                Enviar e-mail de boas-vindas com credenciais de acesso
              </span>
            </label>
          </div>
          
          <div>
            <label class="flex items-center space-x-3">
              <input
                v-model="form.auto_assign_roadmap"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <span class="text-sm text-gray-700">
                Iniciar roteiro de onboarding automaticamente
              </span>
            </label>
          </div>
        </div>
        
        <div class="mt-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Observações Adicionais</label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Observações sobre o funcionário ou processo de onboarding..."
          ></textarea>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-end space-x-4">
        <button
          type="button"
          @click="resetForm"
          class="px-6 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors"
        >
          Limpar Formulário
        </button>
        <button
          type="button"
          @click="saveAsDraft"
          :disabled="submitting"
          class="px-6 py-2 text-blue-700 border border-blue-300 rounded-md hover:bg-blue-50 transition-colors disabled:opacity-50"
        >
          Salvar como Rascunho
        </button>
        <button
          type="submit"
          :disabled="submitting || !isFormValid"
          class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <svg v-if="submitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ submitting ? 'Cadastrando...' : 'Cadastrar Funcionário' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useToast } from '@/composables/useToast'

export default {
  name: 'NewEmployeeForm',
  setup() {
    const { showToast } = useToast()
    
    const submitting = ref(false)
    const departments = ref([])
    const managers = ref([])
    const roadmaps = ref([])
    
    const form = ref({
      // Personal Information
      name: '',
      email: '',
      bi: '',
      phone: '',
      birth_date: '',
      gender: '',
      address: '',
      city: '',
      zip_code: '',
      
      // Professional Information
      position: '',
      department_id: '',
      manager_id: '',
      hire_date: '',
      salary: '',
      contract_type: '',
      
      // Onboarding Configuration
      onboarding_roadmap_id: '',
      onboarding_start_date: '',
      
      // Access Configuration
      send_welcome_email: true,
      auto_assign_roadmap: true,
      notes: ''
    })
    
    const isFormValid = computed(() => {
      return form.value.name && 
             form.value.email && 
             form.value.bi && 
             form.value.position && 
             form.value.department_id && 
             form.value.hire_date
    })
    
    const filteredRoadmaps = computed(() => {
      if (!form.value.department_id) {
        return roadmaps.value
      }
      return roadmaps.value.filter(roadmap => 
        roadmap.department_id == form.value.department_id || !roadmap.department_id
      )
    })
    
    const selectedRoadmap = computed(() => {
      if (!form.value.onboarding_roadmap_id) return null
      return roadmaps.value.find(roadmap => roadmap.id == form.value.onboarding_roadmap_id)
    })
    
    const loadDepartments = async () => {
      try {
        const response = await fetch('/api/departments', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          departments.value = data.data || data
        }
      } catch (error) {
        console.error('Erro ao carregar departamentos:', error)
      }
    }
    
    const loadManagers = async () => {
      try {
        const response = await fetch('/api/users?role=manager', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          managers.value = data.data || data
        }
      } catch (error) {
        console.error('Erro ao carregar gestores:', error)
      }
    }
    
    const loadRoadmaps = async () => {
      try {
        const response = await fetch('/api/onboarding-roadmaps?status=active', {
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`
          }
        })
        
        if (response.ok) {
          const data = await response.json()
          roadmaps.value = data.data || data
        }
      } catch (error) {
        console.error('Erro ao carregar roteiros:', error)
      }
    }
    
    const submitForm = async () => {
      if (!isFormValid.value) {
        showToast('Por favor, preencha todos os campos obrigatórios', 'warning')
        return
      }
      
      submitting.value = true
      
      try {
        const response = await fetch('/api/employees', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            ...form.value,
            status: 'active'
          })
        })
        
        if (response.ok) {
          const data = await response.json()
          showToast('Funcionário cadastrado com sucesso!', 'success')
          resetForm()
          
          // If auto-assign roadmap is enabled and roadmap is selected
          if (form.value.auto_assign_roadmap && form.value.onboarding_roadmap_id) {
            await assignRoadmap(data.data.id || data.id)
          }
        } else {
          const errorData = await response.json()
          throw new Error(errorData.message || 'Erro ao cadastrar funcionário')
        }
      } catch (error) {
        showToast('Erro ao cadastrar funcionário: ' + error.message, 'error')
      } finally {
        submitting.value = false
      }
    }
    
    const saveAsDraft = async () => {
      submitting.value = true
      
      try {
        const response = await fetch('/api/employees', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            ...form.value,
            status: 'draft'
          })
        })
        
        if (response.ok) {
          showToast('Rascunho salvo com sucesso!', 'success')
          resetForm()
        } else {
          const errorData = await response.json()
          throw new Error(errorData.message || 'Erro ao salvar rascunho')
        }
      } catch (error) {
        showToast('Erro ao salvar rascunho: ' + error.message, 'error')
      } finally {
        submitting.value = false
      }
    }
    
    const assignRoadmap = async (employeeId) => {
      try {
        const response = await fetch('/api/user-onboarding-progress', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            user_id: employeeId,
            onboarding_roadmap_id: form.value.onboarding_roadmap_id,
            start_date: form.value.onboarding_start_date || form.value.hire_date
          })
        })
        
        if (response.ok) {
          showToast('Roteiro de onboarding atribuído com sucesso!', 'success')
        }
      } catch (error) {
        console.error('Erro ao atribuir roteiro:', error)
        showToast('Funcionário cadastrado, mas houve erro ao atribuir roteiro', 'warning')
      }
    }
    
    const resetForm = () => {
      form.value = {
        name: '',
        email: '',
        bi: '',
        phone: '',
        birth_date: '',
        gender: '',
        address: '',
        city: '',
        zip_code: '',
        position: '',
        department_id: '',
        manager_id: '',
        hire_date: '',
        salary: '',
        contract_type: '',
        onboarding_roadmap_id: '',
        onboarding_start_date: '',
        send_welcome_email: true,
        auto_assign_roadmap: true,
        notes: ''
      }
    }
    
    // Watch department change to filter roadmaps
    watch(() => form.value.department_id, () => {
      if (form.value.onboarding_roadmap_id) {
        const roadmapStillValid = filteredRoadmaps.value.some(
          roadmap => roadmap.id == form.value.onboarding_roadmap_id
        )
        if (!roadmapStillValid) {
          form.value.onboarding_roadmap_id = ''
        }
      }
    })
    
    onMounted(() => {
      loadDepartments()
      loadManagers()
      loadRoadmaps()
      
      // Set default hire date to today
      form.value.hire_date = new Date().toISOString().split('T')[0]
    })
    
    return {
      form,
      submitting,
      departments,
      managers,
      roadmaps,
      isFormValid,
      filteredRoadmaps,
      selectedRoadmap,
      submitForm,
      saveAsDraft,
      resetForm
    }
  }
}
</script>

<style scoped>
/* Custom styles for form sections */
.form-section {
  @apply bg-white rounded-lg shadow-sm border border-gray-200 p-6;
}

.section-title {
  @apply text-lg font-medium text-gray-900 mb-4 flex items-center gap-2;
}
</style>