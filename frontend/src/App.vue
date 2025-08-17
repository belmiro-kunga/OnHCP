<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Global auth banner -->
    <div v-if="sessionExpired" class="bg-red-50 text-red-800 border border-red-200 px-4 py-3">
      <div class="container mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-3">
        <div>
          <strong>Sessão expirada.</strong>
          Por favor, inicie sessão novamente.
        </div>
        <div class="space-x-2">
          <button class="btn-secondary" @click="dismissBanner">Fechar</button>
          <button class="btn-primary" @click="goToLogin">Ir para Login</button>
        </div>
      </div>
    </div>
    <main class="container mx-auto px-4 py-8">
      <router-view></router-view>
    </main>
  </div>
</template>

<script>
import { useAuth } from './composables/useAuth'

export default {
  name: 'App',
  data() {
    return {
      sessionExpired: false,
      _unauthHandler: null,
    }
  },
  mounted() {
    this._unauthHandler = () => {
      // mark session expired and clear token
      this.sessionExpired = true
      try { useAuth().logout() } catch (e) {}
    }
    window.addEventListener('auth:unauthorized', this._unauthHandler)
  },
  beforeUnmount() {
    if (this._unauthHandler) window.removeEventListener('auth:unauthorized', this._unauthHandler)
  },
  methods: {
    goToLogin() {
      this.sessionExpired = false
      // Try to navigate to login route if available
      if (this.$router) {
        this.$router.push({ path: '/login' }).catch(() => {})
      }
    },
    dismissBanner() {
      this.sessionExpired = false
    }
  }
}
</script>