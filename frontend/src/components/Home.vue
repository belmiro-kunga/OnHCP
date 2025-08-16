<template>
  <div class="home">
    <h2>Bem-vindo ao Sistema de Onboarding HCP</h2>
    <p>Este é o frontend Vue.js conectado ao backend Laravel.</p>
    
    <div class="status-check">
      <h3>Status da Conexão com Backend:</h3>
      <button @click="checkBackend" :disabled="loading">{{ loading ? 'Verificando...' : 'Testar Conexão' }}</button>
      <div v-if="backendStatus" class="status-result">
        <p :class="backendStatus.success ? 'success' : 'error'">
          {{ backendStatus.message }}
        </p>
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
      backendStatus: null
    }
  },
  methods: {
    async checkBackend() {
      this.loading = true
      try {
        const response = await axios.get('http://localhost:8080/api/health')
        this.backendStatus = {
          success: true,
          message: 'Backend conectado com sucesso!'
        }
      } catch (error) {
        this.backendStatus = {
          success: false,
          message: 'Erro ao conectar com o backend. Verifique se o Laravel está rodando.'
        }
      }
      this.loading = false
    }
  }
}
</script>

<style scoped>
.home {
  max-width: 800px;
  margin: 0 auto;
}

.status-check {
  margin-top: 2rem;
  padding: 1rem;
  border: 1px solid #ddd;
  border-radius: 8px;
}

button {
  background-color: #42b883;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.status-result {
  margin-top: 1rem;
}

.success {
  color: #28a745;
}

.error {
  color: #dc3545;
}
</style>