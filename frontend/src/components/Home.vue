<template>
  <div class="home">
    <div class="max-w-4xl mx-auto">
      <!-- Hero Section -->
      <div class="text-center mb-12">
        <h2 class="text-4xl font-bold text-gray-800 mb-4">Bem-vindo ao OnHCP</h2>
        <p class="text-xl text-gray-600 mb-8">Sistema de Onboarding para Profissionais de Saúde</p>
        
        <div class="grid md:grid-cols-3 gap-6 mb-8">
          <div class="card">
            <div class="text-primary-600 mb-4">
              <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Processo Simplificado</h3>
            <p class="text-gray-600">Onboarding rápido e eficiente para novos profissionais</p>
          </div>
          
          <div class="card">
            <div class="text-secondary-600 mb-4">
              <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Gestão de Equipes</h3>
            <p class="text-gray-600">Organize e acompanhe sua equipe médica</p>
          </div>
          
          <div class="card">
            <div class="text-primary-600 mb-4">
              <svg class="w-12 h-12 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path>
              </svg>
            </div>
            <h3 class="text-lg font-semibold mb-2">Documentação</h3>
            <p class="text-gray-600">Mantenha todos os documentos organizados</p>
          </div>
        </div>
      </div>
      
      <!-- Connection Test Section -->
      <div class="card max-w-md mx-auto">
        <h3 class="text-xl font-semibold mb-4 text-center">Status do Sistema</h3>
        <div class="text-center">
          <button 
            @click="testConnection" 
            :disabled="loading"
            class="btn-primary mb-4"
          >
            {{ loading ? 'Testando...' : 'Testar Conexão com Backend' }}
          </button>
          
          <div v-if="connectionStatus" class="p-4 rounded-lg" :class="connectionStatus.success ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'">
            <div class="flex items-center justify-center">
              <svg v-if="connectionStatus.success" class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
              </svg>
              <svg v-else class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
              </svg>
              <span :class="connectionStatus.success ? 'text-green-800' : 'text-red-800'">
                {{ connectionStatus.message }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'Home',
  data() {
    return {
      loading: false,
      connectionStatus: null
    }
  },
  methods: {
    async testConnection() {
      this.loading = true
      this.connectionStatus = null
      
      try {
        const response = await axios.get('http://localhost:8080/api/health')
        this.connectionStatus = {
          success: true,
          message: `Conexão bem-sucedida! Status: ${response.data.status}`
        }
      } catch (error) {
        this.connectionStatus = {
          success: false,
          message: `Erro na conexão: ${error.message}`
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>