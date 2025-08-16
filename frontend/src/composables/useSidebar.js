import { ref, computed, onMounted, onUnmounted } from 'vue'

/**
 * Composable para gerenciar o estado e comportamento da sidebar
 */
export function useSidebar() {
  const sidebarOpen = ref(false)
  const isDesktop = ref(window.innerWidth >= 1024)

  const shouldShowMenuText = computed(() => {
    return sidebarOpen.value || isDesktop.value
  })

  const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value
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

  onMounted(() => {
    window.addEventListener('resize', handleResize)
  })

  onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
  })

  return {
    sidebarOpen,
    isDesktop,
    shouldShowMenuText,
    toggleSidebar,
    closeSidebar
  }
}