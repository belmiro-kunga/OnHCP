import { ref, computed, onMounted, onUnmounted } from 'vue'

/**
 * Composable para gerenciar o estado e comportamento da sidebar
 */
// Singleton state (module-scoped)
const sidebarOpen = ref(false)
const isDesktop = ref(typeof window !== 'undefined' ? window.innerWidth >= 1024 : true)
const sidebarCollapsed = ref(false)

const shouldShowMenuText = computed(() => {
  return sidebarOpen.value || (isDesktop.value && !sidebarCollapsed.value)
})

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value
}

const toggleCollapse = () => {
  if (isDesktop.value) {
    sidebarCollapsed.value = !sidebarCollapsed.value
  }
}

const closeSidebar = () => {
  sidebarOpen.value = false
}

const handleResize = () => {
  isDesktop.value = window.innerWidth >= 1024
  if (isDesktop.value) {
    sidebarOpen.value = false
  }
}

let listenerBound = false

export function useSidebar() {
  onMounted(() => {
    if (!listenerBound) {
      window.addEventListener('resize', handleResize)
      listenerBound = true
    }
  })

  onUnmounted(() => {
    // Keep listener to maintain singleton behavior across components
    // Optionally could remove when no consumers exist
  })

  return {
    sidebarOpen,
    isDesktop,
    sidebarCollapsed,
    shouldShowMenuText,
    toggleSidebar,
    toggleCollapse,
    closeSidebar
  }
}