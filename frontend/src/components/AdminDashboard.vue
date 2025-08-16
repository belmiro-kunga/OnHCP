<template>
  <div class="min-h-screen bg-background flex">
    <!-- Sidebar Component -->
    <AdminSidebar />

    <!-- Main Content Area -->
    <div class="flex-1 lg:ml-64 transition-all duration-300 ease-in-out">
      <!-- Topbar Component -->
      <AdminTopbar />

      <!-- Dashboard Content -->
      <main class="p-4 lg:p-6">
        <!-- Dynamic Module Loading -->
        <component :is="currentComponent" />
      </main>
    </div>
  </div>
</template>

<script>
import { computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useNavigation } from '../composables/useNavigation.js'
import AdminSidebar from './layout/AdminSidebar.vue'
import AdminTopbar from './layout/AdminTopbar.vue'

// Lazy loading dos módulos administrativos
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
  components: {
    AdminSidebar,
    AdminTopbar,
    AdminOverview,
    AdminUsers,
    AdminSimulado,
    AdminOnboarding,
    AdminCursos,
    AdminGamificacao,
    AdminCertificados,
    AdminReports
  },
  setup() {
    const { activeTab, selectMenuItem } = useNavigation()
    const router = useRouter()

    // Mapeamento dos componentes baseado na aba ativa
    const componentMap = {
      overview: 'AdminOverview',
      users: 'AdminUsers',
      simulado: 'AdminSimulado',
      onboarding: 'AdminOnboarding',
      cursos: 'AdminCursos',
      gamificacao: 'AdminGamificacao',
      certificados: 'AdminCertificados',
      reports: 'AdminReports'
    }

    const currentComponent = computed(() => {
      return componentMap[activeTab.value] || 'AdminOverview'
    })

    // Sincronizar aba ativa com a rota atual
    const syncActiveTabWithRoute = () => {
      const currentPath = router.currentRoute.value.path
      const pathSegments = currentPath.split('/')
      const section = pathSegments[pathSegments.length - 1]
      
      if (section && componentMap[section]) {
        activeTab.value = section
      } else {
        activeTab.value = 'overview'
      }
    }

    // Sincronizar na montagem do componente
    onMounted(() => {
      syncActiveTabWithRoute()
    })

    // Observar mudanças na rota
    watch(() => router.currentRoute.value.path, () => {
      syncActiveTabWithRoute()
    })

    return {
      currentComponent
    }
  }
}
</script>