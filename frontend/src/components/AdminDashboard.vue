<template>
  <div class="min-h-screen bg-background flex">
    <!-- Mobile Overlay -->
    <div 
      v-if="sidebarOpen" 
      @click="sidebarOpen = false"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"
    ></div>

    <!-- Sidebar -->
    <aside :class="[
      'sidebar fixed left-0 top-0 h-full z-50 transition-transform duration-300 ease-in-out',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]">
      <!-- Logo Section -->
      <div class="flex items-center px-4 lg:px-6 py-4 lg:py-6 border-b border-primary-700">
        <div class="flex items-center space-x-3">
          <!-- Logo Symbol -->
          <div class="w-8 h-8 lg:w-10 lg:h-10 bg-accent rounded-md flex items-center justify-center">
            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
            </svg>
          </div>
          <!-- Company Name -->
          <div :class="shouldShowMenuText ? 'block' : 'hidden'">
            <h1 class="text-base lg:text-lg font-heading text-white">Hemera Capital</h1>
            <p class="text-xs text-primary-200">Partners</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="mt-4 lg:mt-6">
        <div class="px-2 lg:px-3">
          <button
            v-for="item in menuItems"
            :key="item.id"
            @click="selectMenuItem(item.id)"
            :class="[
              'sidebar-item w-full text-left mb-1 rounded-md flex items-center',
              activeTab === item.id ? 'active' : ''
            ]"
          >
            <component :is="item.icon" class="w-5 h-5 mr-3 flex-shrink-0" />
            <span class="truncate" :class="shouldShowMenuText ? 'inline' : 'hidden'">{{ item.name }}</span>
          </button>
        </div>
      </nav>


    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-64 transition-all duration-300 ease-in-out">
      <!-- Top Bar -->
      <header class="bg-white h-14 lg:h-16 border-b border-neutral-light flex items-center justify-between px-4 lg:px-6">
        <div class="flex items-center space-x-2 lg:space-x-4">
          <!-- Menu Toggle (for mobile) -->
          <button 
            @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden p-2 rounded-md hover:bg-gray-100 transition-colors"
          >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
            </svg>
          </button>
          
          <!-- Search Input -->
          <div class="relative hidden md:block">
            <input 
              type="text" 
              placeholder="Pesquisar..."
              class="input-field w-48 lg:w-80 pl-10 text-sm"
            >
            <svg class="w-4 h-4 lg:w-5 lg:h-5 text-text-secondary absolute left-3 top-1/2 transform -translate-y-1/2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
            </svg>
          </div>
        </div>

        <div class="flex items-center space-x-2 lg:space-x-4">
          <!-- Mobile Search Button -->
          <button class="md:hidden p-2 rounded-md hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5 text-text-secondary" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
            </svg>
          </button>

          <!-- Notifications -->
          <button class="p-2 rounded-md hover:bg-gray-100 relative transition-colors">
            <svg class="w-5 h-5 lg:w-6 lg:h-6 text-text-secondary" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path>
            </svg>
            <span class="absolute top-1 right-1 w-2 h-2 bg-error rounded-full"></span>
          </button>

          <!-- User Profile -->
          <div class="flex items-center space-x-2 lg:space-x-3">
            <div class="text-right hidden sm:block">
              <p class="text-sm font-heading text-text-primary">Maria Santos</p>
              <p class="text-xs text-text-secondary">Administradora</p>
            </div>
            <div class="w-8 h-8 lg:w-10 lg:h-10 bg-primary rounded-full flex items-center justify-center">
              <span class="text-white font-heading text-xs lg:text-sm">MS</span>
            </div>
            <button @click="logout" class="text-text-secondary hover:text-error transition-colors hidden sm:block">
              <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
              </svg>
            </button>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <main class="p-4 lg:p-6">
        <!-- Dynamic Module Loading -->
        <AdminOverview v-if="activeTab === 'overview'" />
        <AdminUsers v-if="activeTab === 'users'" />
        <AdminSimulado v-if="activeTab === 'simulado'" />
        <AdminOnboarding v-if="activeTab === 'onboarding'" />
        <AdminCursos v-if="activeTab === 'cursos'" />
        <AdminGamificacao v-if="activeTab === 'gamificacao'" />
        <AdminCertificados v-if="activeTab === 'certificados'" />
        <AdminReports v-if="activeTab === 'reports'" />

      </main>
    </div>
  </div>
</template>

<script>
import AdminOverview from './modules/AdminOverview.vue'
import AdminUsers from './modules/AdminUsers.vue'
import AdminSimulado from './modules/AdminSimulado.vue'
import AdminOnboarding from './modules/AdminOnboarding.vue'
import AdminCursos from './modules/AdminCursos.vue'
import AdminGamificacao from './modules/AdminGamificacao.vue'
import AdminCertificados from './modules/AdminCertificados.vue'
import AdminReports from './modules/AdminReports.vue'


export default {
  name: 'AdminDashboard',
  data() {
    return {
      activeTab: 'overview',
      sidebarOpen: false,
      isDesktop: window.innerWidth >= 1024,
      menuItems: [
        { 
          id: 'overview', 
          name: 'Visão Geral',
          icon: 'IconDashboard'
        },
        { 
          id: 'users', 
          name: 'Utilizadores',
          icon: 'IconUsers'
        },
        { 
          id: 'simulado', 
          name: 'Simulados',
          icon: 'IconClipboard'
        },
        { 
          id: 'onboarding', 
          name: 'Integração',
          icon: 'IconUserPlus'
        },
        { 
          id: 'cursos', 
          name: 'Cursos',
          icon: 'IconBook'
        },
        { 
          id: 'gamificacao', 
          name: 'Gamificação',
          icon: 'IconTrophy'
        },
        { 
          id: 'certificados', 
          name: 'Certificados',
          icon: 'IconAward'
        },
        { 
          id: 'reports', 
          name: 'Relatórios',
          icon: 'IconChart'
        },

      ]
    }
  },
  components: {
    // Icon Components
    IconDashboard: {
      template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"></path></svg>`
    },
    IconUsers: {
      template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path></svg>`
    },
    IconClipboard: {
      template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2v1a1 1 0 102 0V3a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>`
    },
    IconUserPlus: {
      template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"></path></svg>`
    },
    IconBook: {
      template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V4.804z"></path></svg>`
    },
    IconTrophy: {
      template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd"></path></svg>`
    },
    IconAward: {
      template: `<svg fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`
    },
    IconChart: {
      template: `<svg fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>`
    },

    // Existing components
    AdminOverview,
    AdminUsers,
    AdminSimulado,
    AdminOnboarding,
    AdminCursos,
    AdminGamificacao,
    AdminCertificados,
    AdminReports,

  },
  methods: {
    selectMenuItem(tabId) {
      this.activeTab = tabId
      // Fechar sidebar no mobile após seleção
      if (window.innerWidth < 1024) {
        this.sidebarOpen = false
      }
    },
    logout() {
      // Simular logout
      this.$router.push('/admin/login')
    }
  },
  computed: {
    shouldShowMenuText() {
      return this.sidebarOpen || this.isDesktop
    }
  },
  mounted() {
    // Fechar sidebar quando redimensionar para desktop
    window.addEventListener('resize', () => {
      this.isDesktop = window.innerWidth >= 1024
      if (this.isDesktop) {
        this.sidebarOpen = false
      }
    })
  }
}
</script>