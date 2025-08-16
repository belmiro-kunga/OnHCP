<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Gestão de Simulados</h2>
      <p class="text-gray-600">Crie e gira simulados para avaliação dos utilizadores</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex space-x-4">
        <button @click="showCreateModal = true" class="btn-primary">
          + Novo Simulado
        </button>
        <button @click="exportResults" class="btn-secondary">
          Exportar Resultados
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
          placeholder="Procurar simulados..."
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
            <p class="text-sm font-medium text-gray-600">Total Simulados</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalSimulados }}</p>
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
            <p class="text-sm font-medium text-gray-600">Simulados Ativos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.simuladosAtivos }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Participantes</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalParticipantes }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Média de Pontuação</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.mediaPontuacao }}%</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Simulados Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Simulado</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Questões</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participantes</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Criação</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="simulado in filteredSimulados" :key="simulado.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ simulado.titulo }}</div>
                <div class="text-sm text-gray-500">{{ simulado.descricao }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ simulado.categoria }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ simulado.totalQuestoes }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ simulado.participantes }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(simulado.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ simulado.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ simulado.dataCriacao }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button @click="editSimulado(simulado)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                  <button @click="viewResults(simulado)" class="text-green-600 hover:text-green-900">Resultados</button>
                  <button @click="deleteSimulado(simulado.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Simulado Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Novo Simulado</h3>
          <form @submit.prevent="createSimulado">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Título</label>
              <input v-model="newSimulado.titulo" type="text" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
              <textarea v-model="newSimulado.descricao" rows="3" class="form-input w-full"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
              <select v-model="newSimulado.categoria" required class="form-input w-full">
                <option value="">Selecionar categoria</option>
                <option value="Segurança">Segurança</option>
                <option value="Compliance">Compliance</option>
                <option value="Técnico">Técnico</option>
                <option value="Geral">Geral</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Duração (minutos)</label>
              <input v-model="newSimulado.duracao" type="number" min="1" required class="form-input w-full">
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showCreateModal = false" class="btn-secondary">Cancelar</button>
              <button type="submit" class="btn-primary">Criar Simulado</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminSimulado',
  data() {
    return {
      searchTerm: '',
      filterStatus: '',
      showCreateModal: false,
      stats: {
        totalSimulados: 12,
        simuladosAtivos: 8,
        totalParticipantes: 156,
        mediaPontuacao: 78
      },
      newSimulado: {
        titulo: '',
        descricao: '',
        categoria: '',
        duracao: 30
      },
      simulados: [
        {
          id: 1,
          titulo: 'Simulado de Segurança Básica',
          descricao: 'Avaliação dos conhecimentos básicos de segurança',
          categoria: 'Segurança',
          totalQuestoes: 20,
          participantes: 45,
          status: 'ativo',
          dataCriacao: '2024-01-15'
        },
        {
          id: 2,
          titulo: 'Teste de Compliance',
          descricao: 'Verificação do conhecimento em normas e regulamentos',
          categoria: 'Compliance',
          totalQuestoes: 15,
          participantes: 32,
          status: 'ativo',
          dataCriacao: '2024-01-10'
        },
        {
          id: 3,
          titulo: 'Avaliação Técnica',
          descricao: 'Teste de conhecimentos técnicos específicos',
          categoria: 'Técnico',
          totalQuestoes: 25,
          participantes: 18,
          status: 'rascunho',
          dataCriacao: '2024-01-08'
        }
      ]
    }
  },
  computed: {
    filteredSimulados() {
      return this.simulados.filter(simulado => {
        const matchesSearch = simulado.titulo.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            simulado.categoria.toLowerCase().includes(this.searchTerm.toLowerCase())
        const matchesStatus = !this.filterStatus || simulado.status === this.filterStatus
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
    createSimulado() {
      const newId = Math.max(...this.simulados.map(s => s.id)) + 1
      this.simulados.push({
        id: newId,
        ...this.newSimulado,
        totalQuestoes: 0,
        participantes: 0,
        status: 'rascunho',
        dataCriacao: new Date().toLocaleDateString('pt-PT')
      })
      this.newSimulado = { titulo: '', descricao: '', categoria: '', duracao: 30 }
      this.showCreateModal = false
    },
    editSimulado(simulado) {
      // Implementar edição de simulado
      console.log('Editar simulado:', simulado)
    },
    viewResults(simulado) {
      // Implementar visualização de resultados
      console.log('Ver resultados:', simulado)
    },
    deleteSimulado(id) {
      if (confirm('Tem a certeza que deseja eliminar este simulado?')) {
        this.simulados = this.simulados.filter(s => s.id !== id)
      }
    },
    exportResults() {
      // Implementar exportação de resultados
      console.log('Exportar resultados')
    }
  }
}
</script>