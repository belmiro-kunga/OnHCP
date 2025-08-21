<template>
  <header class="bg-white h-14 lg:h-16 border-b border-neutral-light flex items-center justify-between px-4 lg:px-6">
    <div class="flex items-center space-x-2 lg:space-x-4">
      <!-- Menu Toggle (for mobile) -->
      <button 
        @click="toggleSidebar"
        class="lg:hidden p-2 rounded-md hover:bg-gray-100 transition-colors"
        aria-label="Toggle menu"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
        </svg>
      </button>

      <!-- Collapse Toggle (desktop) -->
      <button 
        @click="toggleCollapse"
        class="hidden lg:inline-flex p-2 rounded-md hover:bg-gray-100 transition-colors"
        aria-label="Alternar menu"
        title="Alternar menu"
      >
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
          <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
        </svg>
      </button>
      
      <!-- Search Input -->
      <div class="relative hidden md:block">
        <input 
          type="text" 
          placeholder="Pesquisar..."
          class="input-field w-48 lg:w-80 pl-10 text-sm"
          v-model="searchQuery"
        >
        <svg class="w-4 h-4 lg:w-5 lg:h-5 text-text-secondary absolute left-3 top-1/2 transform -translate-y-1/2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
        </svg>
      </div>
    </div>

    <div class="flex items-center space-x-2 lg:space-x-4">
      <!-- Mobile Search Button -->
      <button 
        class="md:hidden p-2 rounded-md hover:bg-gray-100 transition-colors"
        @click="toggleMobileSearch"
        aria-label="Search"
      >
        <svg class="w-5 h-5 text-text-secondary" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
        </svg>
      </button>

      <!-- Notifications -->
      <NotificationDropdown />

      <!-- User Profile -->
      <div class="flex items-center space-x-2 lg:space-x-3">
        <div class="text-right hidden sm:block">
          <p class="text-sm font-heading text-text-primary">{{ user.name }}</p>
          <p class="text-xs text-text-secondary">{{ user.role }}</p>
        </div>
        <div class="w-8 h-8 lg:w-10 lg:h-10 bg-primary rounded-full flex items-center justify-center">
          <span class="text-white font-heading text-xs lg:text-sm">{{ user.initials }}</span>
        </div>
        <button 
          @click="handleLogout" 
          class="text-text-secondary hover:text-error transition-colors"
          aria-label="Logout"
          title="Sair"
        >
          <svg class="w-4 h-4 lg:w-5 lg:h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"></path>
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>

<script>
import { ref } from 'vue'
import { useSidebar } from '../../composables/useSidebar.js'
import { useNavigation } from '../../composables/useNavigation.js'
import NotificationDropdown from '../notifications/NotificationDropdown.vue'

export default {
  name: 'AdminTopbar',
  components: {
    NotificationDropdown
  },
  setup() {
    const { toggleSidebar, toggleCollapse } = useSidebar()
    const { logout } = useNavigation()
    
    const searchQuery = ref('')
    const user = ref({
      name: 'Utilizador',
      role: 'Administrador',
      initials: 'U'
    })

    const toggleMobileSearch = () => {
      // Implementar busca mobile
      console.log('Toggle mobile search')
    }

    const handleLogout = () => {
      logout()
    }

    return {
      searchQuery,
      user,
      toggleSidebar,
      toggleCollapse,
      toggleMobileSearch,
      handleLogout
    }
  }
}
</script>