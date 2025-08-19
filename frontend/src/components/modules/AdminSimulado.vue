<template>
  <section class="p-4 md:p-6">
    <header class="mb-4 flex items-center justify-between">
      <h2 class="text-xl md:text-2xl font-semibold text-text-primary">Gestão de Simulados</h2>
      <div class="flex items-center gap-2">
        <button
          class="px-3 py-2 rounded-md border hover:bg-muted"
          :disabled="loading"
          @click="openCreate()"
        >
          Novo Simulado
        </button>
        <button
          class="px-3 py-2 rounded-md bg-primary text-white disabled:opacity-60"
          :disabled="loading"
          @click="loadSimulados"
        >
          Recarregar
        </button>
      </div>
    </header>

    <div v-if="error" class="mb-4 p-3 rounded-md border border-red-300 bg-red-50 text-red-700">
      {{ error }}
    </div>

    <div v-if="loading" class="mb-4 p-3 rounded-md border border-border bg-surface text-text-secondary">
      A carregar simulados...
    </div>

    <div class="rounded-lg border border-border overflow-hidden">
      <table class="min-w-full divide-y divide-border">
        <thead class="bg-muted/50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Título</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Duração</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Mín. Nota</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Máx. Tentativas</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Tipo</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border bg-surface">
          <tr v-for="s in simulados" :key="s.id">
            <td class="px-4 py-3">
              <div class="font-medium text-text-primary">{{ s.title }}</div>
              <div class="text-xs text-text-secondary/80">{{ s.description }}</div>
            </td>
            <td class="px-4 py-3 text-sm">{{ formatDuration(s.duration) }}</td>
            <td class="px-4 py-3 text-sm">{{ s.min_score ?? s.minScore }}%</td>
            <td class="px-4 py-3 text-sm">{{ s.max_attempts ?? s.maxAttempts }}</td>
            <td class="px-4 py-3 text-sm capitalize">{{ s.type }}</td>
            <td class="px-4 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <button class="px-3 py-1.5 text-sm rounded-md border hover:bg-muted" @click="viewDetails(s)">Detalhes</button>
                <button class="px-3 py-1.5 text-sm rounded-md border hover:bg-muted" @click="openEdit(s)">Editar</button>
                <button class="px-3 py-1.5 text-sm rounded-md border hover:bg-muted" @click="openAssign(s)">Atribuir</button>
                <button class="px-3 py-1.5 text-sm rounded-md border hover:bg-muted" @click="toggleAttempts(s)">
                  {{ expandedSimuladoId === s.id ? 'Ocultar tentativas' : 'Ver tentativas' }}
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="simulados.length === 0 && !loading">
            <td colspan="6" class="px-4 py-6 text-center text-text-secondary">Sem simulados</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Tentativas -->
    <div v-if="expandedSimuladoId" class="mt-6 rounded-lg border border-border overflow-hidden">
      <div class="px-4 py-3 bg-muted/50 flex items-center justify-between">
        <div class="font-semibold text-text-primary">Tentativas do Simulado #{{ expandedSimuladoId }}</div>
        <button class="px-3 py-1.5 text-sm rounded-md border hover:bg-muted" :disabled="attemptsLoading" @click="loadAttempts(expandedSimuladoId)">Recarregar</button>
      </div>
      <div v-if="attemptsError" class="m-4 p-3 rounded-md border border-red-300 bg-red-50 text-red-700">{{ attemptsError }}</div>
      <table class="min-w-full divide-y divide-border">
        <thead class="bg-muted/50">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">ID</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Criado em</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Questão Atual</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Respostas</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Tempo Restante</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-text-secondary">Estado</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border bg-surface">
          <tr v-for="a in attempts" :key="a.id">
            <td class="px-4 py-3 text-sm font-mono">{{ a.id }}</td>
            <td class="px-4 py-3 text-sm">{{ formatDate(a.createdAt) }}</td>
            <td class="px-4 py-3 text-sm">{{ a.currentQuestion }}</td>
            <td class="px-4 py-3 text-sm">{{ Object.keys(a.answers || {}).length }}</td>
            <td class="px-4 py-3 text-sm">{{ formatDuration(a.timeRemaining) }}</td>
            <td class="px-4 py-3 text-sm">
              <span v-if="a.result" class="px-2 py-0.5 rounded-full text-xs" :class="a.result.passed ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                {{ a.result.passed ? `Aprovado (${a.result.score}%)` : `Reprovado (${a.result.score}%)` }}
              </span>
              <span v-else class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-800">Em Progresso</span>
            </td>
          </tr>
          <tr v-if="attempts.length === 0 && !attemptsLoading">
            <td colspan="6" class="px-4 py-6 text-center text-text-secondary">Sem tentativas</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Detalhes Simulado -->
    <div v-if="detailSimulado" class="fixed inset-0 bg-black/40 flex items-start justify-center z-50 overflow-y-auto p-4">
      <div class="w-full max-w-2xl rounded-lg shadow-lg border border-border bg-surface modal-panel">
        <div class="px-4 py-3 border-b border-border flex items-center justify-between">
          <h3 class="font-semibold text-text-primary">Detalhes do Simulado</h3>
          <button class="p-1 rounded hover:bg-muted" @click="detailSimulado = null">✕</button>
        </div>
        <div class="p-4 text-sm modal-body">
          <div class="font-medium text-lg mb-1">{{ detailSimulado.title }}</div>
          <div class="text-text-secondary mb-1">{{ detailSimulado.description }}</div>
          <div class="text-xs text-text-secondary mb-4" v-if="detailSimulado.category">Categoria: {{ detailSimulado.category.name }}</div>
          <ul class="grid grid-cols-2 gap-2 text-text-secondary">
            <li><strong>Duração:</strong> {{ formatDuration(detailSimulado.duration) }}</li>
            <li><strong>Mín. Nota:</strong> {{ detailSimulado.min_score ?? detailSimulado.minScore }}%</li>
            <li><strong>Máx. Tentativas:</strong> {{ detailSimulado.max_attempts ?? detailSimulado.maxAttempts }}</li>
            <li><strong>Tipo:</strong> {{ detailSimulado.type }}</li>
            <li><strong>Navegação:</strong> {{ (detailSimulado.allow_navigation ?? detailSimulado.allowNavigation) ? 'Livre' : 'Sequencial' }}</li>
            <li><strong>Guardar Progresso:</strong> {{ (detailSimulado.allow_save_progress ?? detailSimulado.allowSaveProgress) ? 'Sim' : 'Não' }}</li>
            <li><strong>Feedback:</strong> {{ (detailSimulado.show_feedback ?? detailSimulado.showFeedback) ? 'Sim' : 'Não' }}</li>
            <li><strong>Questões:</strong> {{ (detailSimulado.questions || []).length }}</li>
          </ul>
        </div>
        <div class="px-4 py-3 border-t border-border flex justify-end">
          <button class="px-3 py-1.5 rounded-md border hover:bg-muted" @click="detailSimulado = null">Fechar</button>
        </div>
      </div>
    </div>

    <!-- Modal Criar Simulado Melhorado -->
    <ImprovedSimuladoModal 
      v-if="showCreate" 
      @close="closeCreate" 
      @create="handleCreateSimulado"
    />

    <!-- Modal Editar Simulado -->
    <div v-if="showEdit" class="fixed inset-0 bg-black/40 flex items-start justify-center z-50 overflow-y-auto p-4">
      <div class="w-full max-w-xl rounded-lg shadow-lg border border-border bg-surface modal-panel">
        <div class="px-4 py-3 border-b border-border flex items-center justify-between">
          <h3 class="font-semibold text-text-primary">Editar Simulado</h3>
          <button class="p-1 rounded hover:bg-muted" @click="closeEdit">✕</button>
        </div>
        <div class="p-4 grid gap-3 text-sm modal-body">
          <label class="grid gap-1">
            <span>Título</span>
            <input v-model="formEdit.title" class="border rounded px-2 py-1" placeholder="Título" />
          </label>
          <label class="grid gap-1">
            <span>Descrição</span>
            <textarea v-model="formEdit.description" class="border rounded px-2 py-1" placeholder="Descrição" />
          </label>
          <div class="grid grid-cols-3 gap-3">
            <label class="grid gap-1">
              <span>Duração (seg)</span>
              <input type="number" v-model.number="formEdit.duration" class="border rounded px-2 py-1" />
            </label>
            <label class="grid gap-1">
              <span>Mín. Nota (%)</span>
              <input type="number" v-model.number="formEdit.min_score" class="border rounded px-2 py-1" />
            </label>
            <label class="grid gap-1">
              <span>Máx. Tentativas</span>
              <input type="number" v-model.number="formEdit.max_attempts" class="border rounded px-2 py-1" />
            </label>
          </div>
          <div class="grid grid-cols-2 gap-3 items-center">
            <label class="grid gap-1">
              <span>Tipo</span>
              <select v-model="formEdit.type" class="border rounded px-2 py-1">
                <option value="obrigatorio">obrigatorio</option>
                <option value="pratica">pratica</option>
              </select>
            </label>
            <label class="grid gap-1">
              <span>Categoria</span>
              <select v-model="formEdit.category_id" class="border rounded px-2 py-1">
                <option :value="null">Sem categoria</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
            </label>
            <label class="flex items-center gap-2 mt-6">
              <input type="checkbox" v-model="formEdit.allow_navigation" />
              <span>Navegação livre</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="formEdit.allow_save_progress" />
              <span>Guardar progresso</span>
            </label>
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="formEdit.show_feedback" />
              <span>Mostrar feedback</span>
            </label>
          </div>

          <div class="mt-4">
            <div class="flex items-center justify-between mb-2">
              <h4 class="font-semibold">Questões</h4>
              <button class="px-2 py-1 text-sm rounded bg-primary text-white hover:opacity-90" @click="addQuestionEdit">+ Adicionar questão</button>
            </div>
            <div v-if="!formEdit.questions.length" class="text-text-secondary text-sm">Nenhuma questão adicionada.</div>

            <div v-for="(q, idx) in formEdit.questions" :key="'e-'+idx" class="border border-border rounded p-3 mb-3">
              <div class="flex items-center justify-between mb-2">
                <div class="font-medium">Questão {{ idx + 1 }}</div>
                <button class="text-red-600 text-sm" @click="removeQuestionEdit(idx)">Remover</button>
              </div>

              <div class="grid grid-cols-2 gap-3 mb-2">
                <label class="grid gap-1 col-span-2">
                  <span>Enunciado</span>
                  <textarea v-model="q.statement" class="border rounded px-2 py-1" rows="2" placeholder="Texto da questão..."></textarea>
                  <span v-if="!q.statement" class="text-xs text-red-600">Obrigatório</span>
                </label>
                <label class="grid gap-1">
                  <span>Tipo</span>
                  <select v-model="q.q_type" class="border rounded px-2 py-1">
                    <option value="multiple_choice">multiple_choice</option>
                    <option value="true_false">true_false</option>
                    <option value="essay">essay</option>
                    <option value="ordering">ordering</option>
                  </select>
                </label>
                <label class="grid gap-1">
                  <span>Peso</span>
                  <input type="number" min="1" v-model.number="q.weight" class="border rounded px-2 py-1" />
                </label>
                <label class="grid gap-1">
                  <span>Dificuldade</span>
                  <select v-model="q.difficulty" class="border rounded px-2 py-1">
                    <option value="easy">easy</option>
                    <option value="medium">medium</option>
                    <option value="hard">hard</option>
                  </select>
                </label>
              </div>

              <div v-if="q.q_type === 'multiple_choice'" class="mb-2">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-sm text-text-secondary">Opções</span>
                  <button class="text-sm" @click="addOptionEdit(q)">+ opção</button>
                </div>
                <div v-for="(opt, oi) in q.options" :key="'ec-'+oi" class="flex items-center gap-2 mb-1">
                  <input v-model="q.options[oi]" class="border rounded px-2 py-1 flex-1" placeholder="Opção" />
                  <button class="text-xs text-red-600" @click="removeOptionEdit(q, oi)">remover</button>
                </div>
                <label class="grid gap-1 mt-2">
                  <span>Resposta correta</span>
                  <select v-model="q.correct_answer" class="border rounded px-2 py-1">
                    <option v-for="(opt, oi) in q.options" :key="'ec-'+oi" :value="opt">{{ opt || `Opção ${oi+1}` }}</option>
                  </select>
                </label>
                <div class="text-xs text-red-600 mt-1" v-if="(q.options || []).filter(o => (o||'').length > 0).length < 2">Mínimo 2 opções</div>
                <div class="text-xs text-red-600" v-if="q.correct_answer && !(q.options || []).includes(q.correct_answer)">Resposta correta deve ser uma das opções</div>
              </div>

              <div v-else-if="q.q_type === 'true_false'" class="mb-2">
                <label class="grid gap-1">
                  <span>Resposta correta</span>
                  <select v-model="q.correct_answer" class="border rounded px-2 py-1">
                    <option value="true">Verdadeiro</option>
                    <option value="false">Falso</option>
                  </select>
                </label>
              </div>

              <div v-else-if="q.q_type === 'essay'" class="text-xs text-text-secondary mb-2">
                Sem opções ou resposta correta automática. Correção manual.
              </div>

              <div v-else-if="q.q_type === 'ordering'" class="mb-2">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-sm text-text-secondary">Itens (a ordem final será o gabarito)</span>
                  <button class="text-sm" @click="addOptionEdit(q)">+ item</button>
                </div>
                <div v-for="(opt, oi) in q.options" :key="'eord-'+oi" class="flex items-center gap-2 mb-1">
                  <input v-model="q.options[oi]" class="border rounded px-2 py-1 flex-1" placeholder="Item" />
                  <button class="text-xs text-red-600" @click="removeOptionEdit(q, oi)">remover</button>
                </div>
                <div class="text-xs text-text-secondary">O gabarito será salvo como a ordem atual dos itens.</div>
                <div class="text-xs text-red-600 mt-1" v-if="(q.options || []).filter(o => (o||'').length > 0).length < 2">Mínimo 2 itens</div>
              </div>
            </div>
          </div>
          <div v-if="editError" class="p-2 rounded border border-red-300 bg-red-50 text-red-700">{{ editError }}</div>
        </div>
        <div class="flex justify-end gap-2 px-4 pb-4">
          <button class="px-3 py-2 rounded border border-border" @click="closeEdit">Cancelar</button>
          <button class="px-3 py-2 rounded bg-primary text-white hover:opacity-90 disabled:opacity-60" :disabled="editing || !isEditValid" @click="updateSimulado">
            {{ editing ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Atribuir -->
    <div v-if="assignTarget" class="fixed inset-0 bg-black/40 flex items-start justify-center z-50 overflow-y-auto p-4">
      <div class="w-full max-w-xl rounded-lg shadow-lg border border-border bg-surface modal-panel">
        <div class="px-4 py-3 border-b border-border flex items-center justify-between">
          <h3 class="font-semibold text-text-primary">Atribuir Simulado: {{ assignTarget.title }}</h3>
          <button class="p-1 rounded hover:bg-muted" @click="assignTarget = null">✕</button>
        </div>
        <div class="p-4 grid gap-3 text-sm modal-body">
          <label class="grid gap-1">
            <span>Tipo de destino</span>
            <select v-model="formAssign.target_type" class="border rounded px-2 py-1">
              <option value="user">Usuário</option>
              <option value="course">Curso</option>
              <option value="class">Turma</option>
            </select>
          </label>
          <label class="grid gap-1">
            <span>ID do destino</span>
            <input type="number" v-model.number="formAssign.target_id" class="border rounded px-2 py-1" />
          </label>
          <div class="grid grid-cols-2 gap-3">
            <label class="grid gap-1">
              <span>Máx. Tentativas (override)</span>
              <input type="number" v-model.number="formAssign.max_attempts_override" class="border rounded px-2 py-1" />
            </label>
            <label class="grid gap-1">
              <span>Mín. Nota (override)</span>
              <input type="number" v-model.number="formAssign.min_score_override" class="border rounded px-2 py-1" />
            </label>
          </div>
          <label class="grid gap-1">
            <span>Vence em (ISO ou yyyy-mm-dd)</span>
            <input type="datetime-local" v-model="formAssign.due_at" class="border rounded px-2 py-1" />
          </label>
          <div v-if="assignError" class="p-2 rounded border border-red-300 bg-red-50 text-red-700">{{ assignError }}</div>
        </div>
        <div class="px-4 py-3 border-t border-border flex justify-end gap-2">
          <button class="px-3 py-1.5 rounded-md border hover:bg-muted" @click="assignTarget = null">Cancelar</button>
          <button class="px-3 py-1.5 rounded-md bg-primary text-white" :disabled="assigning" @click="createAssignment">{{ assigning ? 'Atribuindo...' : 'Atribuir' }}</button>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { simuladoApi } from '../../composables/simuladoApi'
import { adminSimuladoApi } from '../../composables/adminSimuladoApi'
import ImprovedSimuladoModal from './ImprovedSimuladoModal.vue'

export default {
  name: 'AdminSimulado',
  components: {
    ImprovedSimuladoModal
  },
  setup() {
    const loading = ref(false)
    const error = ref(null)
    const simulados = ref([])

    const expandedSimuladoId = ref(null)
    const attempts = ref([])
    const attemptsLoading = ref(false)
    const attemptsError = ref(null)

    const detailSimulado = ref(null)

    // create modal state
    const showCreate = ref(false)
    const creating = ref(false)
    const createError = ref(null)
    const formCreate = ref({
      title: '',
      description: '',
      duration: 1800,
      min_score: 70,
      max_attempts: 3,
      type: 'obrigatorio',
      category_id: null,
      allow_navigation: true,
      allow_save_progress: true,
      show_feedback: true,
      questions: []
    })

    // categories
    const categories = ref([])
    const loadCategories = async () => {
      try {
        categories.value = await adminSimuladoApi.listCategories()
      } catch (_) { categories.value = [] }
    }

    // assign modal state
    const assignTarget = ref(null)
    const assigning = ref(false)
    const assignError = ref(null)
    const formAssign = ref({
      target_type: 'user',
      target_id: null,
      due_at: null,
      max_attempts_override: null,
      min_score_override: null,
    })

    const loadSimulados = async () => {
      loading.value = true
      error.value = null
      try {
        const data = await adminSimuladoApi.list()
        simulados.value = Array.isArray(data) ? data : []
        // If expanded, refresh attempts
        if (expandedSimuladoId.value) {
          await loadAttempts(expandedSimuladoId.value)
        }
      } catch (e) {
        error.value = e?.message || 'Erro ao carregar simulados'
      } finally {
        loading.value = false
      }
    }

    const loadAttempts = async (simuladoId) => {
      attemptsLoading.value = true
      attemptsError.value = null
      try {
        const data = await simuladoApi.listAttempts(simuladoId)
        attempts.value = Array.isArray(data) ? data : []
      } catch (e) {
        attemptsError.value = e?.message || 'Erro ao carregar tentativas'
      } finally {
        attemptsLoading.value = false
      }
    }

    const toggleAttempts = async (s) => {
      if (expandedSimuladoId.value === s.id) {
        expandedSimuladoId.value = null
        attempts.value = []
        return
      }
      expandedSimuladoId.value = s.id
      await loadAttempts(s.id)
    }

    const viewDetails = (s) => {
      detailSimulado.value = s
    }

    const openCreate = () => {
      createError.value = null
      formCreate.value = {
        title: '', description: '', duration: 1800, min_score: 70, max_attempts: 3, type: 'obrigatorio', category_id: null,
        allow_navigation: true, allow_save_progress: true, show_feedback: true,
        questions: []
      }
      showCreate.value = true
    }
    const closeCreate = () => { showCreate.value = false }
    // Shared normalizer for questions in simulado payloads
    const normalizeSimuladoPayload = (sim) => {
      const copy = JSON.parse(JSON.stringify(sim || {}))
      copy.questions = Array.isArray(copy.questions) ? copy.questions : []
      for (const q of copy.questions) {
        if (q.q_type === 'true_false') {
          q.options = ['true', 'false']
          if (q.correct_answer !== 'true' && q.correct_answer !== 'false') {
            q.correct_answer = 'true'
          }
        }
        if (q.q_type === 'multiple_choice') {
          q.options = (q.options || []).map(o => (o ?? '').toString()).filter(o => o.length > 0)
          if (!q.options.length) q.options = ['Opção 1', 'Opção 2']
          if (!q.options.includes(q.correct_answer)) q.correct_answer = q.options[0]
        }
        if (q.q_type === 'ordering') {
          q.options = (q.options || []).map(o => (o ?? '').toString()).filter(o => o.length > 0)
        }
        if (!q.weight) q.weight = 1
        if (!q.difficulty) q.difficulty = 'medium'
        if (!q.q_type) q.q_type = 'multiple_choice'
      }
      return copy
    }
    
    // Método para lidar com a criação do simulado pelo novo modal
    const handleCreateSimulado = async (simuladoData) => {
      creating.value = true
      createError.value = null
      try {
        const payload = normalizeSimuladoPayload(simuladoData)
        await adminSimuladoApi.create(payload)
        showCreate.value = false
        await loadSimulados()
      } catch (e) {
        createError.value = e?.response?.data?.message || e.message || 'Erro ao criar'
        if (import.meta.env.DEV) {
          // eslint-disable-next-line no-console
          console.debug('[ADMIN][CREATE][ERR]', e?.response?.status, e?.response?.data || e?.message)
        }
        throw e // Re-throw para que o modal possa lidar com o erro
      } finally {
        creating.value = false
      }
    }
    
    const createSimulado = async () => {
      creating.value = true
      createError.value = null
      try {
        // garantir opções padrão para true/false e coerência da resposta correta
        for (const q of formCreate.value.questions) {
          if (q.q_type === 'true_false') {
            q.options = ['true', 'false']
            if (q.correct_answer !== 'true' && q.correct_answer !== 'false') {
              q.correct_answer = 'true'
            }
          }
          if (q.q_type === 'multiple_choice') {
            // normalizar opções vazias
            q.options = (q.options || []).map(o => (o ?? '').toString()).filter(o => o.length > 0)
            if (!q.options.length) {
              q.options = ['Opção 1', 'Opção 2']
            }
            if (!q.options.includes(q.correct_answer)) {
              q.correct_answer = q.options[0]
            }
          }
          // defaults
          if (!q.weight) q.weight = 1
          if (!q.difficulty) q.difficulty = 'medium'
          if (!q.q_type) q.q_type = 'multiple_choice'
        }
        await adminSimuladoApi.create(formCreate.value)
        showCreate.value = false
        await loadSimulados()
      } catch (e) {
        createError.value = e?.response?.data?.message || e.message || 'Erro ao criar'
      } finally {
        creating.value = false
      }
    }

    // Questões - helpers
    const addQuestion = () => {
      formCreate.value.questions.push({
        statement: '',
        q_type: 'multiple_choice',
        weight: 1,
        difficulty: 'medium',
        options: ['', ''],
        correct_answer: ''
      })
    }
    const removeQuestion = (idx) => {
      formCreate.value.questions.splice(idx, 1)
    }
    const addOption = (q) => {
      if (!q.options) q.options = []
      q.options.push('')
    }
    const removeOption = (q, oi) => {
      if (!q.options) return
      q.options.splice(oi, 1)
    }

    // Edit modal state
    const showEdit = ref(false)
    const editing = ref(false)
    const editError = ref(null)
    const formEdit = ref({
      id: null,
      title: '',
      description: '',
      duration: 1800,
      min_score: 70,
      max_attempts: 3,
      type: 'obrigatorio',
      category_id: null,
      allow_navigation: true,
      allow_save_progress: true,
      show_feedback: true,
      questions: []
    })

    const normalizeQuestionDefaults = (q) => {
      if (!q.q_type) q.q_type = 'multiple_choice'
      if (!q.weight) q.weight = 1
      if (!q.difficulty) q.difficulty = 'medium'
      if (!Array.isArray(q.options)) q.options = []
      return q
    }

    const openEdit = async (s) => {
      editError.value = null
      try {
        const data = await adminSimuladoApi.get(s.id)
        const d = data || s
        formEdit.value = {
          id: d.id,
          title: d.title || '',
          description: d.description || '',
          duration: d.duration ?? 1800,
          min_score: d.min_score ?? d.minScore ?? 70,
          max_attempts: d.max_attempts ?? d.maxAttempts ?? 3,
          type: d.type || 'obrigatorio',
          category_id: d.category_id ?? d.categoryId ?? null,
          allow_navigation: d.allow_navigation ?? d.allowNavigation ?? true,
          allow_save_progress: d.allow_save_progress ?? d.allowSaveProgress ?? true,
          show_feedback: d.show_feedback ?? d.showFeedback ?? true,
          questions: (d.questions || []).map(q => normalizeQuestionDefaults({
            statement: q.statement || '',
            q_type: q.q_type || q.type || 'multiple_choice',
            weight: q.weight ?? 1,
            difficulty: q.difficulty || 'medium',
            options: Array.isArray(q.options) ? q.options.slice() : [],
            correct_answer: q.correct_answer ?? q.correctAnswer ?? ''
          }))
        }
        showEdit.value = true
      } catch (e) {
        editError.value = e?.response?.data?.message || e.message || 'Erro ao carregar simulado'
        showEdit.value = true
      }
    }

    const closeEdit = () => { showEdit.value = false }

    // Edit helpers
    const addQuestionEdit = () => {
      formEdit.value.questions.push({
        statement: '',
        q_type: 'multiple_choice',
        weight: 1,
        difficulty: 'medium',
        options: ['', ''],
        correct_answer: ''
      })
    }
    const removeQuestionEdit = (idx) => {
      formEdit.value.questions.splice(idx, 1)
    }
    const addOptionEdit = (q) => {
      if (!q.options) q.options = []
      q.options.push('')
    }
    const removeOptionEdit = (q, oi) => {
      if (!q.options) return
      q.options.splice(oi, 1)
    }

    const updateSimulado = async () => {
      if (!formEdit.value?.id) return
      editing.value = true
      editError.value = null
      try {
        // normalize questions similar to create flow
        for (const q of formEdit.value.questions) {
          if (q.q_type === 'true_false') {
            q.options = ['true', 'false']
            if (q.correct_answer !== 'true' && q.correct_answer !== 'false') {
              q.correct_answer = 'true'
            }
          }
          if (q.q_type === 'multiple_choice') {
            q.options = (q.options || []).map(o => (o ?? '').toString()).filter(o => o.length > 0)
            if (!q.options.length) {
              q.options = ['Opção 1', 'Opção 2']
            }
            if (!q.options.includes(q.correct_answer)) {
              q.correct_answer = q.options[0]
            }
          }
          if (!q.weight) q.weight = 1
          if (!q.difficulty) q.difficulty = 'medium'
          if (!q.q_type) q.q_type = 'multiple_choice'
        }
        await adminSimuladoApi.update(formEdit.value.id, formEdit.value)
        showEdit.value = false
        await loadSimulados()
      } catch (e) {
        editError.value = e?.response?.data?.message || e.message || 'Erro ao salvar'
      } finally {
        editing.value = false
      }
    }

    // Validations
    const validateQuestions = (qs) => {
      if (!Array.isArray(qs) || qs.length === 0) return false
      for (const q of qs) {
        if (!q.statement || !q.q_type || !q.weight) return false
        if (q.q_type === 'multiple_choice') {
          const opts = (q.options || []).filter(o => (o || '').length > 0)
          if (opts.length < 2) return false
          if (!opts.includes(q.correct_answer)) return false
        }
        if (q.q_type === 'true_false') {
          if (q.correct_answer !== 'true' && q.correct_answer !== 'false') return false
        }
        if (q.q_type === 'ordering') {
          const items = (q.options || []).filter(o => (o || '').length > 0)
          if (items.length < 2) return false
        }
      }
      return true
    }

    const isCreateValid = computed(() => {
      const f = formCreate.value
      if (!f.title || !f.duration || !f.min_score || !f.max_attempts) return false
      return validateQuestions(f.questions)
    })

    const isEditValid = computed(() => {
      const f = formEdit.value
      if (!f.title || !f.duration || !f.min_score || !f.max_attempts) return false
      return validateQuestions(f.questions)
    })

    const openAssign = (s) => {
      assignError.value = null
      assignTarget.value = s
      formAssign.value = {
        target_type: 'user', target_id: null, due_at: null, max_attempts_override: null, min_score_override: null,
      }
    }
    const createAssignment = async () => {
      if (!assignTarget.value) return
      assigning.value = true
      assignError.value = null
      try {
        await adminSimuladoApi.createAssignment(assignTarget.value.id, formAssign.value)
        assignTarget.value = null
      } catch (e) {
        assignError.value = e?.response?.data?.message || e?.message || 'Falha ao atribuir'
      } finally {
        assigning.value = false
      }
    }

    const formatDuration = (seconds) => {
      const h = Math.floor(seconds / 3600)
      const m = Math.floor((seconds % 3600) / 60)
      const s = seconds % 60
      return h > 0
        ? `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
        : `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
    }

    const formatDate = (iso) => {
      try {
        if (!iso) return '-'
        const d = new Date(iso)
        return d.toLocaleString()
      } catch {
        return iso || '-'
      }
    }

    onMounted(() => {
      loadSimulados()
      loadCategories()
    })

    return {
      // estado
      loading,
      error,
      simulados,
      expandedSimuladoId,
      attempts,
      attemptsLoading,
      attemptsError,
      detailSimulado,
      showCreate,
      formCreate,
      creating,
      createError,
      assignTarget,
      formAssign,
      assigning,
      assignError,
      categories,

      // métodos
      loadSimulados,
      toggleAttempts,
      loadAttempts,
      viewDetails,
      openCreate,
      closeCreate,
      createSimulado,
      handleCreateSimulado,
      addQuestion,
      removeQuestion,
      addOption,
      removeOption,
      // edit modal
      showEdit,
      formEdit,
      editing,
      editError,
      openEdit,
      closeEdit,
      updateSimulado,
      addQuestionEdit,
      removeQuestionEdit,
      addOptionEdit,
      removeOptionEdit,
      loadCategories,
      openAssign,
      createAssignment,
      formatDuration,
      formatDate,
      isCreateValid,
      isEditValid,
    }
  }
}
</script>

<style scoped>
.bg-muted\/50 { background-color: rgba(0,0,0,0.03); }
/* Fallbacks for environments without Tailwind arbitrary opacity utilities */
.bg-black\/40 { background-color: rgba(0,0,0,0.4); }
.bg-surface { background-color: #ffffff; }
/* Modal layout helpers to prevent overflow outside panel */
.modal-panel { max-height: 90vh; display: flex; flex-direction: column; }
.modal-body { overflow-y: auto; }
</style>
