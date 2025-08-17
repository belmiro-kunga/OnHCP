<template>
  <div>
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-900">Autenticação e Onboarding</h3>
      <div class="flex space-x-3">
        <button @click="showCreateFlowModal = true" class="btn-primary">
          + Novo Fluxo de Integração
        </button>
        <button @click="exportProgress" class="btn-secondary">
          Exportar Progresso
        </button>
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

    <!-- Fluxo de Entrada -->
    <div class="card mb-8">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900">Fluxo de Entrada</h3>
          <p class="text-gray-600 text-sm">Criação/Importação de contas, mensagens de boas-vindas e checklist inicial.</p>
        </div>
        <div class="flex flex-wrap gap-3">
          <button class="btn-primary" @click="showCreateAccountModal = true">Criar Conta</button>
          <button class="btn-secondary" @click="showImportUsersModal = true">Importar Utilizadores</button>
          <button class="btn-secondary" @click="showWelcomeModal = true">Enviar Boas-vindas</button>
        </div>
      </div>
      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Checklist Inicial -->
        <div>
          <h4 class="font-medium text-gray-900 mb-2">Checklist Inicial</h4>
          <ul class="space-y-2">
            <li v-for="item in checklist" :key="item.id" class="flex items-center justify-between">
              <span class="text-gray-800">{{ item.label }}</span>
              <label class="inline-flex items-center text-sm text-gray-600">
                <input type="checkbox" class="form-checkbox mr-2" v-model="item.required">
                Obrigatório
              </label>
            </li>
          </ul>
        </div>
        <!-- Termos e Políticas -->
        <div>
          <h4 class="font-medium text-gray-900 mb-2">Aceitação de Termos e Políticas</h4>
          <div class="space-y-3">
            <label class="flex items-center text-gray-800">
              <input type="checkbox" class="form-checkbox mr-2" v-model="requireTermsAcceptance">
              Exigir aceitação dos termos na primeira entrada
            </label>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">URL dos Termos/Políticas</label>
              <input type="url" v-model="termsUrl" class="form-input w-full" placeholder="https://...">
            </div>
            <div class="text-right">
              <button class="btn-secondary" @click="saveChecklistAndTerms">Guardar Configurações</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Onboarding Flows Table -->
    <div class="card">
      <div class="flex justify-between items-center mb-4">
        <h4 class="text-md font-semibold text-gray-900">Fluxos de Integração</h4>
        <div class="flex space-x-2">
          <select v-model="filterStatus" class="form-input text-sm">
            <option value="">Todos os Status</option>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
            <option value="rascunho">Rascunho</option>
          </select>
          <input
            v-model="searchTerm"
            type="text"
            placeholder="Procurar fluxos..."
            class="form-input text-sm"
          />
        </div>
      </div>
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

    <!-- Create Account Modal -->
    <div v-if="showCreateAccountModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showCreateAccountModal = false" role="dialog" aria-modal="true" aria-labelledby="createAccountTitle">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" tabindex="-1">
        <div class="mt-1">
          <h3 id="createAccountTitle" class="text-lg font-medium text-gray-900 mb-4">Criar Conta</h3>
          <form @submit.prevent="createAccount" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
              <input v-model="newAccount.name" type="text" class="form-input w-full" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="newAccount.email" type="email" class="form-input w-full" required>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input v-model="newAccount.phone" type="tel" class="form-input w-full">
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Função (Role ID)</label>
                <input v-model="newAccount.role_id" type="text" class="form-input w-full" placeholder="opcional">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departamento (ID)</label>
                <input v-model="newAccount.department_id" type="text" class="form-input w-full" placeholder="opcional">
              </div>
            </div>
            <div class="flex items-center gap-6">
              <label class="inline-flex items-center text-sm">
                <input type="checkbox" class="form-checkbox mr-2" v-model="newAccount.sendWelcomeEmail">
                Enviar email de boas-vindas
              </label>
            </div>
            <div class="flex justify-end gap-3">
              <button type="button" class="btn-secondary" @click="showCreateAccountModal = false">Cancelar</button>
              <button type="submit" class="btn-primary">Criar Conta</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Import Users Modal -->
    <div v-if="showImportUsersModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showImportUsersModal = false" role="dialog" aria-modal="true" aria-labelledby="importUsersTitle">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" tabindex="-1">
        <div class="mt-1">
          <h3 id="importUsersTitle" class="text-lg font-medium text-gray-900 mb-4">Importar Utilizadores</h3>
          <form @submit.prevent="handleImportUsers" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Ficheiro (.csv, .xlsx)</label>
              <input type="file" accept=".csv,.xlsx" class="form-input w-full" @change="onFileSelected">
            </div>
            <div class="flex items-center gap-6">
              <label class="inline-flex items-center text-sm">
                <input type="checkbox" class="form-checkbox mr-2" v-model="importPayload.updateExisting">
                Atualizar existentes
              </label>
              <label class="inline-flex items-center text-sm">
                <input type="checkbox" class="form-checkbox mr-2" v-model="importPayload.sendWelcomeEmail">
                Enviar email de boas-vindas
              </label>
            </div>
            <div class="flex justify-end gap-3">
              <button type="button" class="btn-secondary" @click="showImportUsersModal = false">Cancelar</button>
              <button type="submit" class="btn-primary" :disabled="!importPayload.file">Importar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Welcome Modal -->
    <div v-if="showWelcomeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click.self="showWelcomeModal = false" role="dialog" aria-modal="true" aria-labelledby="welcomeTitle">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" tabindex="-1">
        <div class="mt-1">
          <h3 id="welcomeTitle" class="text-lg font-medium text-gray-900 mb-4">Enviar Boas-vindas</h3>
          <form @submit.prevent="sendWelcome" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input v-model="welcomeForm.email" type="email" class="form-input w-full">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                <input v-model="welcomeForm.phone" type="tel" class="form-input w-full">
              </div>
            </div>
            <div class="flex items-center gap-6">
              <label class="inline-flex items-center text-sm">
                <input type="checkbox" class="form-checkbox mr-2" v-model="welcomeForm.sendEmail">
                Email
              </label>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem</label>
              <textarea v-model="welcomeForm.message" rows="3" class="form-input w-full"></textarea>
            </div>
            <div class="flex justify-end gap-3">
              <button type="button" class="btn-secondary" @click="showWelcomeModal = false">Cancelar</button>
              <button type="submit" class="btn-primary">Enviar</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useUsers } from '../../composables/useUsers'
import api from '../../composables/api'
export default {
  name: 'AdminOnboarding',
  setup() {
    // Expor métodos do módulo de utilizadores para reutilizar no onboarding
    const { createUser, importUsers } = useUsers()
    return { createUser, importUsers }
  },
  data() {
    return {
      searchTerm: '',
      filterStatus: '',
      showCreateFlowModal: false,
      // Fluxo de Entrada: estados dos modais
      showCreateAccountModal: false,
      showImportUsersModal: false,
      showWelcomeModal: false,
      // Formulários do Fluxo de Entrada
      newAccount: {
        name: '',
        email: '',
        phone: '',
        role_id: '',
        department_id: '',
        sendWelcomeEmail: true,
        
      },
      importPayload: {
        file: null,
        updateExisting: true,
        sendWelcomeEmail: true,
        
      },
      welcomeForm: {
        email: '',
        phone: '',
        sendEmail: true,
        message: 'Bem-vindo(a) à empresa! Siga as instruções no portal para completar a sua integração.'
      },
      // Checklist inicial e termos
      checklist: [
        { id: 'doc_identificacao', label: 'Documento de Identificação', required: true },
        { id: 'politicas_seg', label: 'Leitura das Políticas de Segurança', required: true },
        { id: 'contrato_trabalho', label: 'Assinatura do Contrato de Trabalho', required: true },
      ],
      requireTermsAcceptance: true,
      termsUrl: '',
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
    onFileSelected(e) {
      const f = e?.target?.files?.[0]
      this.importPayload.file = f || null
    },
    // ===== Fluxo de Entrada: Ações =====
    async createAccount() {
      try {
        const payload = {
          name: this.newAccount.name,
          email: this.newAccount.email,
          phone: this.newAccount.phone,
          role_id: this.newAccount.role_id || undefined,
          department_id: this.newAccount.department_id || undefined,
        }
        await this.createUser(payload)
        if (this.newAccount.sendWelcomeEmail) {
          await this.sendWelcomeInternal({
            email: this.newAccount.email,
            phone: this.newAccount.phone,
            sendEmail: this.newAccount.sendWelcomeEmail,
            message: this.welcomeForm.message
          })
        }
        this.newAccount = { name: '', email: '', phone: '', role_id: '', department_id: '', sendWelcomeEmail: true }
        this.showCreateAccountModal = false
      } catch (e) {
        console.error('Erro ao criar conta no onboarding:', e)
      }
    },
    async handleImportUsers() {
      try {
        if (!this.importPayload.file) return
        await this.importUsers(this.importPayload.file, {
          updateExisting: this.importPayload.updateExisting ? '1' : '',
          sendWelcomeEmail: this.importPayload.sendWelcomeEmail ? '1' : '',
        })
        this.importPayload = { file: null, updateExisting: true, sendWelcomeEmail: true }
        this.showImportUsersModal = false
      } catch (e) {
        console.error('Erro ao importar utilizadores no onboarding:', e)
      }
    },
    async sendWelcome() {
      try {
        await this.sendWelcomeInternal(this.welcomeForm)
        this.showWelcomeModal = false
      } catch (e) {
        console.error('Erro ao enviar mensagem de boas-vindas:', e)
      }
    },
    async sendWelcomeInternal({ email, phone, sendEmail, message }) {
      // Endpoint de exemplo; ajuste conforme backend disponível
      try {
        await api.post('/onboarding/welcome', { email, phone, sendEmail, message })
      } catch (e) {
        // Caso o endpoint não exista ainda, não bloquear o fluxo
        console.warn('Endpoint /onboarding/welcome indisponível, simulação local.')
      }
    },
    saveChecklistAndTerms() {
      // Persistência pode ser feita via API quando disponível
      console.log('Checklist/Termos salvos:', {
        checklist: this.checklist,
        requireTermsAcceptance: this.requireTermsAcceptance,
        termsUrl: this.termsUrl
      })
      alert('Configurações de checklist e termos guardadas.')
    },
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