<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-900 to-primary-700 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <div class="mx-auto h-12 w-12 bg-white rounded-full flex items-center justify-center">
          <svg class="h-8 w-8 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-3xl font-bold text-white">
          Painel Administrativo
        </h2>
        <p class="mt-2 text-sm text-primary-100">
          Acesso restrito para administradores
        </p>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-xl rounded-lg sm:px-10">
        <form @submit.prevent="handleAdminLogin" class="space-y-6">
          <div>
            <label for="admin-email" class="form-label">
              E-mail do Administrador
            </label>
            <input
              id="admin-email"
              v-model="form.email"
              type="email"
              required
              class="form-input"
              placeholder="seu.email@empresa.com"
            />
          </div>

          <div>
            <label for="admin-password" class="form-label">
              Palavra-passe
            </label>
            <input
              id="admin-password"
              v-model="form.password"
              type="password"
              required
              class="form-input"
              placeholder="Palavra-passe do administrador"
            />
          </div>

          <!-- Campo opcional removido: validação agora é feita no backend -->

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ loading ? 'A verificar...' : 'Aceder ao Painel' }}
            </button>
          </div>

          <div class="text-center">
            <router-link
              to="/"
              class="text-sm text-primary-600 hover:text-primary-500"
            >
              ← Voltar ao Início de Sessão de Utilizador
            </router-link>
          </div>
        </form>

        <!-- Status de login -->
        <div v-if="loginStatus" class="mt-4">
          <div
            :class="loginStatus.success ? 'alert-success' : 'alert-error'"
          >
            {{ loginStatus.message }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useAuth } from '@/composables/useAuth'

export default {
  name: 'AdminLogin',
  data() {
    return {
      loading: false,
      loginStatus: null,
      form: {
        email: '',
        password: ''
      }
    }
  },
  methods: {
    async handleAdminLogin() {
      this.loading = true
      this.loginStatus = null
      try {
        const { login } = useAuth()
        await login(this.form.email, this.form.password)
        this.loginStatus = { success: true, message: 'Acesso autorizado! A redirecionar...' }
        const redirect = this.$route.query.redirect || '/admin/dashboard/overview'
        this.$router.replace(redirect)
      } catch (error) {
        this.loginStatus = {
          success: false,
          message: error?.message || 'Credenciais inválidas'
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>