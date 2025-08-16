<template>
  <div>
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Gestão de Certificados</h2>
      <p class="text-gray-600">Crie e gira certificados para cursos e conquistas dos utilizadores</p>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between items-center">
      <div class="flex space-x-4">
        <button @click="showCreateTemplateModal = true" class="btn-primary">
          + Novo Modelo
        </button>
        <button @click="generateBulkCertificates" class="btn-secondary">
          Gerar em Lote
        </button>
        <button @click="exportCertificates" class="btn-secondary">
          Exportar Relatório
        </button>
      </div>
      <div class="flex space-x-4">
        <input v-model="searchTerm" type="text" placeholder="Buscar certificados..." class="form-input">
        <select v-model="statusFilter" class="form-input">
          <option value="">Todos os Status</option>
          <option value="emitido">Emitido</option>
          <option value="pendente">Pendente</option>
          <option value="revogado">Revogado</option>
        </select>
      </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2V7.414A2 2 0 0015.414 6L12 2.586A2 2 0 0010.586 2H6zm5 6a1 1 0 10-2 0v3.586l-1.293-1.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V8z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Total Certificados</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalCertificados }}</p>
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
            <p class="text-sm font-medium text-gray-600">Certificados Emitidos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.certificadosEmitidos }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Pendentes</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.certificadosPendentes }}</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Modelos Ativos</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.modelosAtivos }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Certificates Table -->
    <div class="card">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium text-gray-900">Certificados Recentes</h3>
        <div class="flex space-x-2">
          <button @click="activeView = 'certificates'" :class="activeView === 'certificates' ? 'btn-primary' : 'btn-secondary'">Certificados</button>
          <button @click="activeView = 'templates'" :class="activeView === 'templates' ? 'btn-primary' : 'btn-secondary'">Modelos</button>
        </div>
      </div>

      <!-- Certificates View -->
      <div v-if="activeView === 'certificates'" class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilizador</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Curso/Conquista</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Emissão</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="certificate in filteredCertificates" :key="certificate.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                      <span class="text-white font-bold text-xs">{{ certificate.utilizador.charAt(0) }}</span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ certificate.utilizador }}</div>
                    <div class="text-sm text-gray-500">{{ certificate.email }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">{{ certificate.titulo }}</div>
                <div class="text-sm text-gray-500">{{ certificate.descricao }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getTypeClass(certificate.tipo)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ certificate.tipo }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ certificate.dataEmissao }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(certificate.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ certificate.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">{{ certificate.codigo }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button @click="viewCertificate(certificate)" class="text-indigo-600 hover:text-indigo-900">Ver</button>
                  <button @click="downloadCertificate(certificate)" class="text-green-600 hover:text-green-900">Descarregar</button>
                  <button @click="revokeCertificate(certificate.id)" class="text-red-600 hover:text-red-900">Revogar</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Templates View -->
      <div v-if="activeView === 'templates'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="template in templates" :key="template.id" class="border rounded-lg p-4 hover:shadow-md transition-shadow">
          <div class="mb-4">
            <div class="w-full h-32 bg-gradient-to-br rounded-lg flex items-center justify-center" :style="{background: template.gradient}">
              <div class="text-center text-white">
                <h4 class="font-bold text-lg">{{ template.nome }}</h4>
                <p class="text-sm opacity-90">Certificado de Conclusão</p>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <h4 class="font-medium text-gray-900 mb-1">{{ template.nome }}</h4>
            <p class="text-sm text-gray-600">{{ template.descricao }}</p>
          </div>
          <div class="flex justify-between items-center text-xs text-gray-500 mb-3">
            <span>Usado {{ template.utilizacoes }} vezes</span>
            <span :class="template.ativo ? 'text-green-600' : 'text-red-600'">{{ template.ativo ? 'Ativo' : 'Inativo' }}</span>
          </div>
          <div class="flex justify-between space-x-2">
            <button @click="editTemplate(template)" class="text-xs text-indigo-600 hover:text-indigo-900">Editar</button>
            <button @click="previewTemplate(template)" class="text-xs text-green-600 hover:text-green-900">Pré-visualizar</button>
            <button @click="duplicateTemplate(template)" class="text-xs text-blue-600 hover:text-blue-900">Duplicar</button>
            <button @click="deleteTemplate(template.id)" class="text-xs text-red-600 hover:text-red-900">Eliminar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Template Modal -->
    <div v-if="showCreateTemplateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Criar Novo Modelo de Certificado</h3>
          <form @submit.prevent="createTemplate">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Modelo</label>
              <input v-model="newTemplate.nome" type="text" required class="form-input w-full">
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
              <textarea v-model="newTemplate.descricao" rows="3" class="form-input w-full"></textarea>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Certificado</label>
              <select v-model="newTemplate.tipo" required class="form-input w-full">
                <option value="">Selecionar tipo</option>
                <option value="curso">Curso</option>
                <option value="conquista">Conquista</option>
                <option value="participacao">Participação</option>
                <option value="especializacao">Especialização</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Esquema de Cores</label>
              <select v-model="newTemplate.esquemaCores" required class="form-input w-full">
                <option value="">Selecionar esquema</option>
                <option value="azul">Azul Profissional</option>
                <option value="verde">Verde Sucesso</option>
                <option value="dourado">Dourado Premium</option>
                <option value="roxo">Roxo Criativo</option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Orientação</label>
              <select v-model="newTemplate.orientacao" required class="form-input w-full">
                <option value="horizontal">Horizontal</option>
                <option value="vertical">Vertical</option>
              </select>
            </div>
            <div class="flex justify-end space-x-3">
              <button type="button" @click="showCreateTemplateModal = false" class="btn-secondary">Cancelar</button>
              <button type="submit" class="btn-primary">Criar Modelo</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Certificate Preview Modal -->
    <div v-if="showPreviewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Pré-visualização do Certificado</h3>
            <button @click="showPreviewModal = false" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <div class="bg-gray-100 p-8 rounded-lg">
            <div class="bg-white p-8 rounded-lg shadow-lg mx-auto" style="width: 800px; height: 600px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
              <div class="text-center text-white h-full flex flex-col justify-center">
                <h1 class="text-4xl font-bold mb-4">CERTIFICADO</h1>
                <p class="text-xl mb-8">Este certificado é concedido a</p>
                <h2 class="text-3xl font-bold mb-8 border-b-2 border-white pb-4 mx-16">{{ previewData.utilizador || 'Nome do Utilizador' }}</h2>
                <p class="text-lg mb-4">pela conclusão bem-sucedida do</p>
                <h3 class="text-2xl font-semibold mb-8">{{ previewData.titulo || 'Nome do Curso' }}</h3>
                <div class="flex justify-between items-end mt-auto">
                  <div class="text-left">
                    <p class="text-sm">Data: {{ previewData.dataEmissao || new Date().toLocaleDateString('pt-PT') }}</p>
                    <p class="text-sm">Código: {{ previewData.codigo || 'CERT-2024-001' }}</p>
                  </div>
                  <div class="text-right">
                    <div class="border-t border-white pt-2">
                      <p class="text-sm">Assinatura Digital</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="flex justify-end space-x-3 mt-4">
            <button @click="showPreviewModal = false" class="btn-secondary">Fechar</button>
            <button @click="downloadPreview" class="btn-primary">Descarregar PDF</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminCertificados',
  data() {
    return {
      activeView: 'certificates',
      searchTerm: '',
      statusFilter: '',
      showCreateTemplateModal: false,
      showPreviewModal: false,
      previewData: {},
      stats: {
        totalCertificados: 156,
        certificadosEmitidos: 142,
        certificadosPendentes: 14,
        modelosAtivos: 6
      },
      newTemplate: {
        nome: '',
        descricao: '',
        tipo: '',
        esquemaCores: '',
        orientacao: 'horizontal'
      },
      certificates: [
        {
          id: 1,
          utilizador: 'Ana Silva',
          email: 'ana.silva@empresa.com',
          titulo: 'Segurança da Informação',
          descricao: 'Curso completo de segurança',
          tipo: 'curso',
          dataEmissao: '2024-01-15',
          status: 'emitido',
          codigo: 'CERT-2024-001'
        },
        {
          id: 2,
          utilizador: 'João Santos',
          email: 'joao.santos@empresa.com',
          titulo: 'Especialista em Dados',
          descricao: 'Conquista por análise de dados',
          tipo: 'conquista',
          dataEmissao: '2024-01-18',
          status: 'emitido',
          codigo: 'CERT-2024-002'
        },
        {
          id: 3,
          utilizador: 'Maria Costa',
          email: 'maria.costa@empresa.com',
          titulo: 'Gestão de Projetos',
          descricao: 'Curso de metodologias ágeis',
          tipo: 'curso',
          dataEmissao: '2024-01-20',
          status: 'pendente',
          codigo: 'CERT-2024-003'
        },
        {
          id: 4,
          utilizador: 'Pedro Oliveira',
          email: 'pedro.oliveira@empresa.com',
          titulo: 'Participação Ativa',
          descricao: 'Participação em eventos',
          tipo: 'participacao',
          dataEmissao: '2024-01-22',
          status: 'emitido',
          codigo: 'CERT-2024-004'
        }
      ],
      templates: [
        {
          id: 1,
          nome: 'Certificado Padrão',
          descricao: 'Modelo padrão para cursos gerais',
          tipo: 'curso',
          gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
          utilizacoes: 45,
          ativo: true
        },
        {
          id: 2,
          nome: 'Certificado Premium',
          descricao: 'Modelo premium para especializações',
          tipo: 'especializacao',
          gradient: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
          utilizacoes: 23,
          ativo: true
        },
        {
          id: 3,
          nome: 'Certificado de Conquista',
          descricao: 'Modelo para distintivos e conquistas',
          tipo: 'conquista',
          gradient: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
          utilizacoes: 67,
          ativo: true
        },
        {
          id: 4,
          nome: 'Certificado de Participação',
          descricao: 'Modelo para eventos e workshops',
          tipo: 'participacao',
          gradient: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
          utilizacoes: 12,
          ativo: false
        }
      ]
    }
  },
  computed: {
    filteredCertificates() {
      return this.certificates.filter(cert => {
        const matchesSearch = cert.utilizador.toLowerCase().includes(this.searchTerm.toLowerCase()) ||
                            cert.titulo.toLowerCase().includes(this.searchTerm.toLowerCase())
        const matchesStatus = !this.statusFilter || cert.status === this.statusFilter
        return matchesSearch && matchesStatus
      })
    }
  },
  methods: {
    getStatusClass(status) {
      switch (status) {
        case 'emitido':
          return 'bg-green-100 text-green-800'
        case 'pendente':
          return 'bg-yellow-100 text-yellow-800'
        case 'revogado':
          return 'bg-red-100 text-red-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    },
    getTypeClass(tipo) {
      switch (tipo) {
        case 'curso':
          return 'bg-blue-100 text-blue-800'
        case 'conquista':
          return 'bg-purple-100 text-purple-800'
        case 'participacao':
          return 'bg-green-100 text-green-800'
        case 'especializacao':
          return 'bg-indigo-100 text-indigo-800'
        default:
          return 'bg-gray-100 text-gray-800'
      }
    },
    createTemplate() {
      const gradients = {
        azul: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        verde: 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
        dourado: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
        roxo: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'
      }
      
      const newId = Math.max(...this.templates.map(t => t.id)) + 1
      this.templates.push({
        id: newId,
        ...this.newTemplate,
        gradient: gradients[this.newTemplate.esquemaCores] || gradients.azul,
        utilizacoes: 0,
        ativo: true
      })
      
      this.newTemplate = { nome: '', descricao: '', tipo: '', esquemaCores: '', orientacao: 'horizontal' }
      this.showCreateTemplateModal = false
    },
    editTemplate(template) {
      console.log('Editar modelo:', template)
    },
    previewTemplate(template) {
      this.previewData = {
        utilizador: 'Exemplo de Utilizador',
        titulo: 'Exemplo de Curso',
        dataEmissao: new Date().toLocaleDateString('pt-PT'),
        codigo: 'CERT-PREV-001'
      }
      this.showPreviewModal = true
    },
    duplicateTemplate(template) {
      const newId = Math.max(...this.templates.map(t => t.id)) + 1
      const duplicated = {
        ...template,
        id: newId,
        nome: template.nome + ' (Cópia)',
        utilizacoes: 0
      }
      this.templates.push(duplicated)
    },
    deleteTemplate(id) {
      if (confirm('Tem a certeza que deseja eliminar este modelo?')) {
        this.templates = this.templates.filter(t => t.id !== id)
      }
    },
    viewCertificate(certificate) {
      this.previewData = certificate
      this.showPreviewModal = true
    },
    downloadCertificate(certificate) {
      console.log('Descarregar certificado:', certificate)
    },
    revokeCertificate(id) {
      if (confirm('Tem a certeza que deseja revogar este certificado?')) {
        const certificate = this.certificates.find(c => c.id === id)
        if (certificate) {
          certificate.status = 'revogado'
        }
      }
    },
    generateBulkCertificates() {
      console.log('Gerar certificados em lote')
    },
    exportCertificates() {
      console.log('Exportar relatório de certificados')
    },
    downloadPreview() {
      console.log('Descarregar pré-visualização como PDF')
    }
  }
}
</script>