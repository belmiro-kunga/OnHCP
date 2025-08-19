<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Sistema de Gamificação</h2>
      <p class="text-gray-600">Configure pontos, distintivos e rankings para motivar os utilizadores</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex space-x-4">
        <button @click="showCreateBadgeModal = true" class="btn-primary">
          + Novo Distintivo
        </button>
        <button @click="showCreateChallengeModal = true" class="btn-secondary">
          + Novo Desafio
        </button>
        <button @click="exportRankings" class="btn-secondary">
          Exportar Rankings
        </button>
      </div>
      <div class="flex space-x-4">
        <select v-model="activeTab" class="form-input">
          <option value="overview">Visão Geral</option>
          <option value="badges">Distintivos</option>
          <option value="challenges">Desafios</option>
          <option value="rankings">Rankings</option>
        </select>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
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
            <p class="text-sm font-medium text-gray-600">Total Pontos Atribuídos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalPontos.toLocaleString() }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Distintivos Ativos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.distintivosAtivos }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Utilizadores Ativos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.utilizadoresAtivos }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Desafios Ativos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.desafiosAtivos }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Tabs -->
    <div class="card">
      <!-- Badges Tab -->
      <div v-if="activeTab === 'badges' || activeTab === 'overview'">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Distintivos</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
          <div v-for="badge in badges" :key="badge.id" class="border rounded-lg p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-center mb-3">
              <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl" :style="{backgroundColor: badge.color}">
                {{ badge.icon }}
              </div>
            </div>
            <h4 class="font-medium text-center text-gray-900 mb-1">{{ badge.nome }}</h4>
            <p class="text-sm text-gray-600 text-center mb-2">{{ badge.descricao }}</p>
            <div class="text-center">
              <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">{{ badge.conquistado }} utilizadores</span>
            </div>
            <div class="flex justify-center space-x-2 mt-3">
              <button @click="editBadge(badge)" class="text-xs text-indigo-600 hover:text-indigo-900">Editar</button>
              <button @click="deleteBadge(badge.id)" class="text-xs text-red-600 hover:text-red-900">Eliminar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Challenges Tab -->
      <div v-if="activeTab === 'challenges' || activeTab === 'overview'">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Desafios Ativos</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Desafio</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pontos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participantes</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Fim</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="challenge in challenges" :key="challenge.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ challenge.nome }}</div>
                  <div class="text-sm text-gray-500">{{ challenge.descricao }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ challenge.tipo }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ challenge.pontos }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ challenge.participantes }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ challenge.dataFim }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusClass(challenge.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                    {{ challenge.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button @click="editChallenge(challenge)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                    <button @click="viewParticipants(challenge)" class="text-green-600 hover:text-green-900">Participantes</button>
                    <button @click="deleteChallenge(challenge.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Rankings Tab -->
      <div v-if="activeTab === 'rankings'">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Ranking de Utilizadores</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Posição</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizador</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pontos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Distintivos</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nível</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Última Atividade</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(user, index) in rankings" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span class="text-lg font-bold" :class="getRankingClass(index + 1)">{{ index + 1 }}</span>
                    <span v-if="index < 3" class="ml-2 text-lg">{{ getRankingIcon(index + 1) }}</span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8">
                      <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                        <span class="text-white font-bold text-xs">{{ user.nome.charAt(0) }}</span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.nome }}</div>
                      <div class="text-sm text-gray-500">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.pontos.toLocaleString() }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.distintivos }}</td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    Nível {{ user.nivel }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ user.ultimaAtividade }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create Badge Modal -->
    <div v-if="showCreateBadgeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Novo Distintivo</h3>
          <form @submit.prevent="createBadge">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Distintivo</label>
              <input v-model="newBadge.nome" type="text" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
              <textarea v-model="newBadge.descricao" rows="3" class="form-input w-full"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Ícone (Emoji)</label>
              <input v-model="newBadge.icon" type="text" placeholder="🏆" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Cor de Fundo</label>
              <input v-model="newBadge.color" type="color" required class="form-input w-full h-10">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Critério de Conquista</label>
              <select v-model="newBadge.criterio" required class="form-input w-full">
                <option value="">Selecionar critério</option>
                <option value="pontos">Pontos acumulados</option>
                <option value="cursos">Cursos concluídos</option>
                <option value="tempo">Tempo no sistema</option>
                <option value="atividade">Atividade diária</option>
              </select>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showCreateBadgeModal = false" class="btn-secondary">Cancelar</button>
              <button type="submit" class="btn-primary">Criar Distintivo</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Create Challenge Modal -->
    <div v-if="showCreateChallengeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Novo Desafio</h3>
          <form @submit.prevent="createChallenge">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Desafio</label>
              <input v-model="newChallenge.nome" type="text" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
              <textarea v-model="newChallenge.descricao" rows="3" class="form-input w-full"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
              <select v-model="newChallenge.tipo" required class="form-input w-full">
                <option value="">Selecionar tipo</option>
                <option value="Individual">Individual</option>
                <option value="Equipa">Equipa</option>
                <option value="Global">Global</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Pontos de Recompensa</label>
              <input v-model="newChallenge.pontos" type="number" min="1" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Data de Fim</label>
              <input v-model="newChallenge.dataFim" type="date" required class="form-input w-full">
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showCreateChallengeModal = false" class="btn-secondary">Cancelar</button>
              <button type="submit" class="btn-primary">Criar Desafio</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminGamificacao',
  data() {
    return {
      activeTab: 'overview',
      showCreateBadgeModal: false,
      showCreateChallengeModal: false,
      stats: {
        totalPontos: 0,
        distintivosAtivos: 0,
        utilizadoresAtivos: 0,
        desafiosAtivos: 0
      },
      newBadge: {
        nome: '',
        descricao: '',
        icon: '',
        color: '#3B82F6',
        criterio: ''
      },
      newChallenge: {
        nome: '',
        descricao: '',
        tipo: '',
        pontos: 100,
        dataFim: ''
      },
      badges: [],
      challenges: [],
      rankings: []
    }
  },
  methods: {
    getStatusClass(status) {
      switch (status) {
        case 'ativo':
          return 'bg-green-100 text-green-800'
        case 'inativo':
          return 'bg-red-100 text-red-800'
        case 'concluído':
          return 'bg-blue-100 text-blue-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    },
    getRankingClass(position) {
      switch (position) {
        case 1:
          return 'text-yellow-600'
        case 2:
          return 'text-gray-500'
        case 3:
          return 'text-yellow-700'
        default:
          return 'text-gray-900'
      }
    },
    getRankingIcon(position) {
      switch (position) {
        case 1:
          return '🥇'
        case 2:
          return '🥈'
        case 3:
          return '🥉'
        default:
          return ''
      }
    },
    createBadge() {
      const newId = Math.max(...this.badges.map(b => b.id)) + 1
      this.badges.push({
        id: newId,
        ...this.newBadge,
        conquistado: 0
      })
      this.newBadge = { nome: '', descricao: '', icon: '', color: '#3B82F6', criterio: '' }
      this.showCreateBadgeModal = false
    },
    createChallenge() {
      const newId = Math.max(...this.challenges.map(c => c.id)) + 1
      this.challenges.push({
        id: newId,
        ...this.newChallenge,
        participantes: 0,
        status: 'ativo'
      })
      this.newChallenge = { nome: '', descricao: '', tipo: '', pontos: 100, dataFim: '' }
      this.showCreateChallengeModal = false
    },
    editBadge(badge) {
      console.log('Editar distintivo:', badge)
    },
    deleteBadge(id) {
      if (confirm('Tem a certeza que deseja eliminar este distintivo?')) {
        this.badges = this.badges.filter(b => b.id !== id)
      }
    },
    editChallenge(challenge) {
      console.log('Editar desafio:', challenge)
    },
    viewParticipants(challenge) {
      console.log('Ver participantes:', challenge)
    },
    deleteChallenge(id) {
      if (confirm('Tem a certeza que deseja eliminar este desafio?')) {
        this.challenges = this.challenges.filter(c => c.id !== id)
      }
    },
    exportRankings() {
      console.log('Exportar rankings')
    }
  }
}
</script>