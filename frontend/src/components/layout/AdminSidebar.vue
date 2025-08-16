<template>
  <!-- Mobile Overlay -->
  <div 
    v-if="sidebarOpen" 
    @click="closeSidebar"
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
          @click="handleMenuItemClick(item.id)"
          :class="[
            'sidebar-item w-full text-left mb-1 rounded-md flex items-center',
            activeTab === item.id ? 'active' : ''
          ]"
        >
          <component :is="item.icon" class="w-5 h-5 mr-3 flex-shrink-0" />
          <span class="truncate" :class="shouldShowMenuText ? 'inline' : 'hidden'">
            {{ item.name }}
          </span>
        </button>
      </div>
    </nav>
  </aside>
</template>

<script>
import { useSidebar } from '../../composables/useSidebar.js'
import { useNavigation } from '../../composables/useNavigation.js'
import {
  IconDashboard,
  IconUsers,
  IconClipboard,
  IconUserPlus,
  IconBook,
  IconTrophy,
  IconAward,
  IconChart
} from '../icons/AdminIcons.vue'

export default {
  name: 'AdminSidebar',
  components: {
    IconDashboard,
    IconUsers,
    IconClipboard,
    IconUserPlus,
    IconBook,
    IconTrophy,
    IconAward,
    IconChart
  },
  setup() {
    const { sidebarOpen, shouldShowMenuText, closeSidebar } = useSidebar()
    const { activeTab, menuItems, selectMenuItem } = useNavigation()

    const handleMenuItemClick = (tabId) => {
      selectMenuItem(tabId)
      // Fechar sidebar no mobile após seleção
      if (window.innerWidth < 1024) {
        closeSidebar()
      }
    }

    return {
      sidebarOpen,
      shouldShowMenuText,
      activeTab,
      menuItems,
      closeSidebar,
      handleMenuItemClick
    }
  }
}
</script>