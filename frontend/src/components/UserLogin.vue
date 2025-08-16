<template>
  <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="text-center">
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          OnHCP - Login de Usuário
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Acesse sua conta de profissional de saúde
        </p>
      </div>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="card">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div>
            <label for="email" class="form-label">
              Email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="form-input"
              placeholder="seu@email.com"
            />
          </div>

          <div>
            <label for="password" class="form-label">
              Senha
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="form-input"
              placeholder="Sua senha"
            />
          </div>

          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded"
              />
              <label for="remember" class="ml-2 block text-sm text-gray-900">
                Lembrar de mim
              </label>
            </div>

            <div class="text-sm">
              <a href="#" class="font-medium text-primary-600 hover:text-primary-500">
                Esqueceu a senha?
              </a>
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary w-full"
            >
              {{ loading ? 'Entrando...' : 'Entrar' }}
            </button>
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
export default {
  name: 'UserLogin',
  data() {
    return {
      loading: false,
      loginStatus: null,
      form: {
        email: '',
        password: '',
        remember: false
      }
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true
      this.loginStatus = null
      
      try {
        // Simular login por enquanto
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        this.loginStatus = {
          success: true,
          message: 'Login realizado com sucesso!'
        }
        
        // Redirecionar para o painel do usuário após 1 segundo
        setTimeout(() => {
          this.$router.push('/dashboard')
        }, 1000)
        
      } catch (error) {
        this.loginStatus = {
          success: false,
          message: 'Erro no login. Verifique suas credenciais.'
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>