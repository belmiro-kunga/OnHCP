<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Gestão de Onboarding</h3>
      <div class="flex space-x-3">
        <button @click="exportProgress" class="btn-secondary">
          Exportar Progresso
        </button>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'py-2 px-1 border-b-2 font-medium text-sm',
            activeTab === tab.id
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
          ]"
        >
          {{ tab.name }}
        </button>
      </nav>
    </div>

    <!-- Stats Cards -->
    <div v-if="activeTab === 'overview'" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Roteiros Ativos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.fluxosAtivos }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Funcionários Ativos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.integracoesCompletas }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Em Progresso</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.emProgresso }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Tempo Médio</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.tempoMedio }} dias</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
      <!-- Overview Tab -->
      <div v-if="activeTab === 'overview'" class="card mb-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">Visão Geral do Onboarding</h3>
            <p class="text-gray-600 text-sm">Acompanhe o progresso geral dos processos de integração.</p>
          </div>
        </div>
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Quick Actions -->
          <div>
            <h4 class="font-medium text-gray-900 mb-4">Ações Rápidas</h4>
            <div class="space-y-3">
              <button @click="activeTab = 'employees'" class="w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Cadastrar Novo Funcionário</p>
                    <p class="text-sm text-gray-500">Adicionar funcionário e configurar onboarding</p>
                  </div>
                </div>
              </button>
              <button @click="activeTab = 'roadmaps'" class="w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Gerenciar Roteiros</p>
                    <p class="text-sm text-gray-500">Criar e personalizar roteiros de onboarding</p>
                  </div>
                </div>
              </button>
              <button @click="activeTab = 'documents'" class="w-full text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="font-medium text-gray-900">Gerenciar Documentos</p>
                    <p class="text-sm text-gray-500">Upload e organização de documentos</p>
                  </div>
                </div>
              </button>
            </div>
          </div>
          <!-- Recent Activity -->
          <div>
            <h4 class="font-medium text-gray-900 mb-4">Atividade Recente</h4>
            <div class="space-y-3">
              <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">João Silva completou o onboarding</p>
                  <p class="text-xs text-gray-500">há 2 horas</p>
                </div>
              </div>
              <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">Novo roteiro criado para TI</p>
                  <p class="text-xs text-gray-500">há 4 horas</p>
                </div>
              </div>
              <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">Maria Santos iniciou onboarding</p>
                  <p class="text-xs text-gray-500">há 6 horas</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Roadmaps Tab -->
      <OnboardingRoadmaps v-if="activeTab === 'roadmaps'" />

      <!-- Documents Tab -->
      <DocumentManagement v-if="activeTab === 'documents'" />

      <!-- Signatures Tab -->
      <DocumentSignatures v-if="activeTab === 'signatures'" />

      <!-- Employees Tab -->
      <NewEmployeeForm v-if="activeTab === 'employees'" />

      <!-- Templates Tab -->
      <AdminTemplates v-if="activeTab === 'templates'" />

      <!-- Hierarquia Tab -->
      <AdminHierarquia v-if="activeTab === 'hierarquia'" />

      <!-- Checklist Tab -->
      <AdminChecklist v-if="activeTab === 'checklist'" />

      <!-- Recursos Tab -->
      <AdminRecursos v-if="activeTab === 'recursos'" />
    </div>

  </div>
</template>

<script>
import { ref } from 'vue'
import OnboardingRoadmaps from './OnboardingRoadmaps.vue'
import DocumentManagement from './DocumentManagement.vue'
import DocumentSignatures from './DocumentSignatures.vue'
import NewEmployeeForm from './NewEmployeeForm.vue'
import AdminTemplates from './AdminTemplates.vue'
import AdminHierarquia from './AdminHierarquia.vue'
import AdminChecklist from './AdminChecklist.vue'
import AdminRecursos from './AdminRecursos.vue'

export default {
  name: 'AdminOnboarding',
  components: {
    OnboardingRoadmaps,
    DocumentManagement,
    DocumentSignatures,
    NewEmployeeForm,
    AdminTemplates,
    AdminHierarquia,
    AdminChecklist,
    AdminRecursos
  },
  setup() {
    const activeTab = ref('overview')
    
    const tabs = [
      { id: 'overview', name: 'Visão Geral' },
      { id: 'roadmaps', name: 'Roteiros' },
      { id: 'employees', name: 'Funcionários' },
      { id: 'documents', name: 'Documentos' },
      { id: 'signatures', name: 'Assinaturas' },
      { id: 'templates', name: 'Templates' },
      { id: 'hierarquia', name: 'Hierarquia' },
      { id: 'checklist', name: 'Checklist' },
      { id: 'recursos', name: 'Recursos' }
    ]
    
    const stats = ref({
      fluxosAtivos: 12,
      integracoesCompletas: 45,
      emProgresso: 8,
      tempoMedio: 14
    })
    
    const exportProgress = () => {
      console.log('Exportar progresso')
      // Implementar exportação de progresso
    }
    
    return {
      activeTab,
      tabs,
      stats,
      exportProgress
    }
  }
}
</script>