<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Admin Header -->
    <header class="bg-primary-800 shadow-lg">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-6-3a2 2 0 11-4 0 2 2 0 014 0zm-2 4a5 5 0 00-4.546 2.916A5.986 5.986 0 0010 16a5.986 5.986 0 004.546-2.084A5 5 0 0010 11z" clip-rule="evenodd"></path>
                </svg>
              </div>
            </div>
            <h1 class="ml-3 text-2xl font-bold text-white">Painel Administrativo</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-primary-100">Admin: Maria Santos</span>
            <button @click="logout" class="text-sm text-red-300 hover:text-red-100 font-medium">Sair</button>
          </div>
        </div>
      </div>
    </header>

    <!-- Navigation Tabs -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Dynamic Module Loading -->
      <AdminOverview v-if="activeTab === 'overview'" />
      <AdminUsers v-if="activeTab === 'users'" />
      <AdminReports v-if="activeTab === 'reports'" />
      <AdminSettings v-if="activeTab === 'settings'" />
    </main>
  </div>
</template>

<script>
import AdminOverview from './modules/AdminOverview.vue'
import AdminUsers from './modules/AdminUsers.vue'
import AdminReports from './modules/AdminReports.vue'
import AdminSettings from './modules/AdminSettings.vue'

export default {
  name: 'AdminDashboard',
  components: {
    AdminOverview,
    AdminUsers,
    AdminReports,
    AdminSettings
  },
  data() {
    return {
      activeTab: 'overview',
      tabs: [
        { id: 'overview', name: 'Visão Geral' },
        { id: 'users', name: 'Utilizadores' },
        { id: 'reports', name: 'Relatórios' },
        { id: 'settings', name: 'Configurações' }
      ]
    }
  },
  methods: {
    logout() {
      // Simular logout
      this.$router.push('/admin/login')
    }
  }
}
</script>