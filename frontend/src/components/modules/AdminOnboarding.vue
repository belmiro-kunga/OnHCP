<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Gestão de Integração</h2>
      <p class="text-gray-600">Configure e acompanhe o processo de integração dos utilizadores</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex space-x-4">
        <button @click="showCreateFlowModal = true" class="btn-primary">
          + Novo Fluxo de Integração
        </button>
        <button @click="exportProgress" class="btn-secondary">
          Exportar Progresso
        </button>
      </div>
      <div class="flex space-x-4">
        <select v-model="filterStatus" class="form-input">
          <option value="">Todos os Status</option>
          <option value="ativo">Ativo</option>
          <option value="inativo">Inativo</option>
          <option value="rascunho">Rascunho</option>
        </select>
        <input 
          v-model="searchTerm" 
          type="text" 
          placeholder="Procurar fluxos..."
          class="form-input"
        >
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
            <p class="text-sm font-medium text-gray-600">Fluxos Ativos</p>
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
            <p class="text-sm font-medium text-gray-600">Integrações Completas</p>
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

    <!-- Onboarding Flows Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fluxo de Integração</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Departamento</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etapas</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizadores</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taxa Conclusão</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="fluxo in filteredFluxos" :key="fluxo.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ fluxo.nome }}</div>
                <div class="text-sm text-gray-500">{{ fluxo.descricao }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ fluxo.departamento }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ fluxo.totalEtapas }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ fluxo.utilizadores }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div class="bg-green-600 h-2 rounded-full" :style="{width: fluxo.taxaConclusao + '%'}"></div>
                  </div>
                  <span class="text-sm text-gray-900">{{ fluxo.taxaConclusao }}%</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(fluxo.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ fluxo.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button @click="editFluxo(fluxo)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                  <button @click="viewProgress(fluxo)" class="text-green-600 hover:text-green-900">Progresso</button>
                  <button @click="deleteFluxo(fluxo.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Flow Modal -->
    <div v-if="showCreateFlowModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Novo Fluxo de Integração</h3>
          <form @submit.prevent="createFluxo">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Fluxo</label>
              <input v-model="newFluxo.nome" type="text" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
              <textarea v-model="newFluxo.descricao" rows="3" class="form-input w-full"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Departamento</label>
              <select v-model="newFluxo.departamento" required class="form-input w-full">
                <option value="">Selecionar departamento</option>
                <option value="TI">Tecnologia da Informação</option>
                <option value="RH">Recursos Humanos</option>
                <option value="Financeiro">Financeiro</option>
                <option value="Operações">Operações</option>
                <option value="Vendas">Vendas</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Duração Estimada (dias)</label>
              <input v-model="newFluxo.duracaoEstimada" type="number" min="1" required class="form-input w-full">
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showCreateFlowModal = false" class="btn-secondary">Cancelar</button>
              <button type="submit" class="btn-primary">Criar Fluxo</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminOnboarding',
  data() {
    return {
      searchTerm: '',
      filterStatus: '',
      showCreateFlowModal: false,
      stats: {
        fluxosAtivos: 5,
        integracoesCompletas: 89,
        emProgresso: 23,
        tempoMedio: 7
      },
      newFluxo: {
        nome: '',
        descricao: '',
        departamento: '',
        duracaoEstimada: 7
      },
      fluxos: [
        {
          id: 1,
          nome: 'Integração TI',
          descricao: 'Processo de integração para colaboradores de TI',
          departamento: 'TI',
          totalEtapas: 8,
          utilizadores: 15,
          taxaConclusao: 87,
          status: 'ativo'
        },
        {
          id: 2,
          nome: 'Integração RH',
          descricao: 'Fluxo padrão para novos colaboradores de RH',
          departamento: 'RH',
          totalEtapas: 6,
          utilizadores: 8,
          taxaConclusao: 92,
          status: 'ativo'
        },
        {
          id: 3,
          nome: 'Integração Vendas',
          descricao: 'Processo específico para equipa comercial',
          departamento: 'Vendas',
          totalEtapas: 10,
          utilizadores: 22,
          taxaConclusao: 78,
          status: 'ativo'
        },
        {
          id: 4,
          nome: 'Integração Financeiro',
          descricao: 'Fluxo para departamento financeiro',
          departamento: 'Financeiro',
          totalEtapas: 7,
          utilizadores: 5,
          taxaConclusao: 95,
          status: 'rascunho'
        }
      ]
    }
  },
  computed: {
    filteredFluxos() {
      return this.fluxos.filter(fluxo => {
        const matchesSearch = fluxo.nome.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            fluxo.departamento.toLowerCase().includes(this.searchTerm.toLowerCase())
        const matchesStatus = !this.filterStatus || fluxo.status === this.filterStatus
        return matchesSearch && matchesStatus
      })
    }
  },
  methods: {
    getStatusClass(status) {
      switch (status) {
        case 'ativo':
          return 'bg-green-100 text-green-800'
        case 'inativo':
          return 'bg-red-100 text-red-800'
        case 'rascunho':
          return 'bg-yellow-100 text-yellow-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    },
    createFluxo() {
      const newId = Math.max(...this.fluxos.map(f => f.id)) + 1
      this.fluxos.push({
        id: newId,
        ...this.newFluxo,
        totalEtapas: 0,
        utilizadores: 0,
        taxaConclusao: 0,
        status: 'rascunho'
      })
      this.newFluxo = { nome: '', descricao: '', departamento: '', duracaoEstimada: 7 }
      this.showCreateFlowModal = false
    },
    editFluxo(fluxo) {
      // Implementar edição de fluxo
      console.log('Editar fluxo:', fluxo)
    },
    viewProgress(fluxo) {
      // Implementar visualização de progresso
      console.log('Ver progresso:', fluxo)
    },
    deleteFluxo(id) {
      if (confirm('Tem a certeza que deseja eliminar este fluxo de integração?')) {
        this.fluxos = this.fluxos.filter(f => f.id !== id)
      }
    },
    exportProgress() {
      // Implementar exportação de progresso
      console.log('Exportar progresso')
    }
  }
}
</script>