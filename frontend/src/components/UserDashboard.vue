<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <h1 class="text-2xl font-bold text-gray-900">Painel do Utilizador</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">Bem-vindo, Dr. João Silva</span>
            <button
              @click="logout"
              class="text-sm text-red-600 hover:text-red-800 font-medium"
            >
              Sair
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-primary-100 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Onboarding</p>
              <p class="text-2xl font-bold text-gray-900">85%</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-secondary-100 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-secondary-600" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Documentos</p>
              <p class="text-2xl font-bold text-gray-900">12/15</p>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600">Próximas Tarefas</p>
              <p class="text-2xl font-bold text-gray-900">3</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Dashboard Content -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Progresso do Onboarding -->
        <div class="card">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Progresso da Integração</h3>
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-700">Informações Pessoais</span>
              <span class="text-sm text-green-600 font-medium">✓ Completo</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-green-600 h-2 rounded-full w-full"></div>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-700">Documentação</span>
              <span class="text-sm text-yellow-600 font-medium">80% Completo</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-yellow-500 h-2 rounded-full w-4/5"></div>
            </div>

            <div class="flex items-center justify-between">
              <span class="text-sm font-medium text-gray-700">Treinamentos</span>
              <span class="text-sm text-blue-600 font-medium">60% Completo</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div class="bg-blue-500 h-2 rounded-full w-3/5"></div>
            </div>
          </div>
        </div>

        <!-- Próximas Tarefas -->
        <div class="card">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Próximas Tarefas</h3>
          <div class="space-y-3">
            <div class="flex items-center p-3 bg-yellow-50 border border-yellow-200 rounded-md">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-yellow-400 rounded-full"></div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">Enviar Diploma</p>
                <p class="text-xs text-gray-600">Prazo: 2 dias</p>
              </div>
            </div>

            <div class="flex items-center p-3 bg-blue-50 border border-blue-200 rounded-md">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-blue-400 rounded-full"></div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">Completar Formação de Segurança</p>
                <p class="text-xs text-gray-600">Prazo: 5 dias</p>
              </div>
            </div>

            <div class="flex items-center p-3 bg-green-50 border border-green-200 rounded-md">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">Agendar Exame Médico</p>
                <p class="text-xs text-gray-600">Prazo: 1 semana</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-8">
        <div class="card">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <button class="btn-primary">
              Enviar Documento
            </button>
            <button class="btn-secondary">
              Ver Formações
            </button>
            <button class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
              Agendar Reunião
            </button>
            <button class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
              Suporte
            </button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  name: 'UserDashboard',
  methods: {
    logout() {
      // Simular logout
      this.$router.push('/')
    }
  }
}
</script>