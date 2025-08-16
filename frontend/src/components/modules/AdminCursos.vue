<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Gestão de Cursos</h2>
      <p class="text-gray-600">Crie e gira cursos de formação para os utilizadores</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex space-x-4">
        <button @click="showCreateModal = true" class="btn-primary">
          + Novo Curso
        </button>
        <button @click="importCourse" class="btn-secondary">
          Importar Curso
        </button>
        <button @click="exportCourses" class="btn-secondary">
          Exportar Cursos
        </button>
      </div>
      <div class="flex space-x-4">
        <select v-model="filterCategory" class="form-input">
          <option value="">Todas as Categorias</option>
          <option value="Segurança">Segurança</option>
          <option value="Compliance">Compliance</option>
          <option value="Técnico">Técnico</option>
          <option value="Soft Skills">Soft Skills</option>
        </select>
        <select v-model="filterStatus" class="form-input">
          <option value="">Todos os Status</option>
          <option value="publicado">Publicado</option>
          <option value="rascunho">Rascunho</option>
          <option value="arquivado">Arquivado</option>
        </select>
        <input 
          v-model="searchTerm" 
          type="text" 
          placeholder="Procurar cursos..."
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
                <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Cursos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalCursos }}</p>
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
            <p class="text-sm font-medium text-gray-600">Cursos Publicados</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.cursosPublicados }}</p>
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
            <p class="text-sm font-medium text-gray-600">Estudantes Inscritos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.estudantesInscritos }}</p>
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
            <p class="text-sm font-medium text-gray-600">Avaliação Média</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.avaliacaoMedia }}/5</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Courses Table -->
    <div class="card">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duração</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Inscritos</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conclusões</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Avaliação</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="curso in filteredCursos" :key="curso.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div class="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                      <span class="text-white font-bold text-sm">{{ curso.titulo.charAt(0) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ curso.titulo }}</div>
                    <div class="text-sm text-gray-500">{{ curso.instrutor }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curso.categoria }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curso.duracao }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ curso.inscritos }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                    <div class="bg-green-600 h-2 rounded-full" :style="{width: (curso.conclusoes / curso.inscritos * 100) + '%'}"></div>
                  </div>
                  <span class="text-sm text-gray-900">{{ curso.conclusoes }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex text-yellow-400">
                    <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= curso.avaliacao ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                    </svg>
                  </div>
                  <span class="ml-1 text-sm text-gray-600">({{ curso.totalAvaliacoes }})</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(curso.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ curso.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button @click="editCurso(curso)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                  <button @click="viewAnalytics(curso)" class="text-green-600 hover:text-green-900">Análises</button>
                  <button @click="deleteCurso(curso.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Course Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Novo Curso</h3>
          <form @submit.prevent="createCurso">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Título do Curso</label>
              <input v-model="newCurso.titulo" type="text" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
              <textarea v-model="newCurso.descricao" rows="3" class="form-input w-full"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
              <select v-model="newCurso.categoria" required class="form-input w-full">
                <option value="">Selecionar categoria</option>
                <option value="Segurança">Segurança</option>
                <option value="Compliance">Compliance</option>
                <option value="Técnico">Técnico</option>
                <option value="Soft Skills">Soft Skills</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Instrutor</label>
              <input v-model="newCurso.instrutor" type="text" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Duração</label>
              <input v-model="newCurso.duracao" type="text" placeholder="ex: 2h 30min" required class="form-input w-full">
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showCreateModal = false" class="btn-secondary">Cancelar</button>
              <button type="submit" class="btn-primary">Criar Curso</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminCursos',
  data() {
    return {
      searchTerm: '',
      filterCategory: '',
      filterStatus: '',
      showCreateModal: false,
      stats: {
        totalCursos: 24,
        cursosPublicados: 18,
        estudantesInscritos: 342,
        avaliacaoMedia: 4.2
      },
      newCurso: {
        titulo: '',
        descricao: '',
        categoria: '',
        instrutor: '',
        duracao: ''
      },
      cursos: [
        {
          id: 1,
          titulo: 'Fundamentos de Cibersegurança',
          categoria: 'Segurança',
          instrutor: 'Dr. João Silva',
          duracao: '4h 30min',
          inscritos: 45,
          conclusoes: 38,
          avaliacao: 4,
          totalAvaliacoes: 35,
          status: 'publicado'
        },
        {
          id: 2,
          titulo: 'GDPR e Proteção de Dados',
          categoria: 'Compliance',
          instrutor: 'Ana Costa',
          duracao: '3h 15min',
          inscritos: 32,
          conclusoes: 28,
          avaliacao: 5,
          totalAvaliacoes: 28,
          status: 'publicado'
        },
        {
          id: 3,
          titulo: 'Desenvolvimento Web Moderno',
          categoria: 'Técnico',
          instrutor: 'Carlos Mendes',
          duracao: '8h 45min',
          inscritos: 28,
          conclusoes: 15,
          avaliacao: 4,
          totalAvaliacoes: 18,
          status: 'publicado'
        },
        {
          id: 4,
          titulo: 'Liderança e Comunicação',
          categoria: 'Soft Skills',
          instrutor: 'Maria Santos',
          duracao: '2h 20min',
          inscritos: 52,
          conclusoes: 47,
          avaliacao: 5,
          totalAvaliacoes: 45,
          status: 'publicado'
        },
        {
          id: 5,
          titulo: 'Análise de Riscos',
          categoria: 'Segurança',
          instrutor: 'Pedro Oliveira',
          duracao: '5h 10min',
          inscritos: 0,
          conclusoes: 0,
          avaliacao: 0,
          totalAvaliacoes: 0,
          status: 'rascunho'
        }
      ]
    }
  },
  computed: {
    filteredCursos() {
      return this.cursos.filter(curso => {
        const matchesSearch = curso.titulo.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            curso.instrutor.toLowerCase().includes(this.searchTerm.toLowerCase())
        const matchesCategory = !this.filterCategory || curso.categoria === this.filterCategory
        const matchesStatus = !this.filterStatus || curso.status === this.filterStatus
        return matchesSearch && matchesCategory && matchesStatus
      })
    }
  },
  methods: {
    getStatusClass(status) {
      switch (status) {
        case 'publicado':
          return 'bg-green-100 text-green-800'
        case 'rascunho':
          return 'bg-yellow-100 text-yellow-800'
        case 'arquivado':
          return 'bg-gray-100 text-gray-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    },
    createCurso() {
      const newId = Math.max(...this.cursos.map(c => c.id)) + 1
      this.cursos.push({
        id: newId,
        ...this.newCurso,
        inscritos: 0,
        conclusoes: 0,
        avaliacao: 0,
        totalAvaliacoes: 0,
        status: 'rascunho'
      })
      this.newCurso = { titulo: '', descricao: '', categoria: '', instrutor: '', duracao: '' }
      this.showCreateModal = false
    },
    editCurso(curso) {
      // Implementar edição de curso
      console.log('Editar curso:', curso)
    },
    viewAnalytics(curso) {
      // Implementar visualização de análises
      console.log('Ver análises:', curso)
    },
    deleteCurso(id) {
      if (confirm('Tem a certeza que deseja eliminar este curso?')) {
        this.cursos = this.cursos.filter(c => c.id !== id)
      }
    },
    importCourse() {
      // Implementar importação de curso
      console.log('Importar curso')
    },
    exportCourses() {
      // Implementar exportação de cursos
      console.log('Exportar cursos')
    }
  }
}
</script>