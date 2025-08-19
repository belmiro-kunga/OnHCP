<template>
  <div class="min-h-screen bg-background flex">
    <!-- Sidebar Component -->
    <AdminSidebar />

    <!-- Main Content Area -->
    <div :class="['flex-1 min-w-0 transition-all duration-300 ease-in-out', sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64']">
      <!-- Topbar Component -->
      <AdminTopbar />

      <!-- Dashboard Content -->
      <main class="p-4 lg:p-6 overflow-x-hidden">
        <!-- Dynamic Module Loading with Permission Gate -->
        <div v-if="isAllowed">
          <component :is="currentComponent" />
        </div>
        <div v-else class="p-8 bg-red-50 border border-red-200 rounded-lg text-red-800">
          <h3 class="text-lg font-semibold mb-2">Acesso negado</h3>
          <p>Você não possui permissões para acessar esta secção.</p>
        </div>
      </main>
    </div>
  </div>
</template>

<script>
import { computed, onMounted, watch } from 'vue'
import { useSidebar } from '../composables/useSidebar.js'
import { useRouter } from 'vue-router'
import { useNavigation } from '../composables/useNavigation.js'
import { usePermissions } from '../composables/usePermissions'
import AdminSidebar from './layout/AdminSidebar.vue'
import AdminTopbar from './layout/AdminTopbar.vue'

// Lazy loading dos módulos administrativos
import AdminOverview from './modules/AdminOverview.vue'
import AdminUsers from './modules/AdminUsers.vue'
import AdminOnboarding from './modules/AdminOnboarding.vue'
import AdminSimulado from './modules/AdminSimulado.vue'
import AdminCursos from './modules/AdminCursos.vue'
import AdminGamificacao from './modules/AdminGamificacao.vue'
import AdminCertificados from './modules/AdminCertificados.vue'
import AdminAudit from './modules/AdminAudit.vue'
import AdminSecurity from './modules/AdminSecurity.vue'
import AdminRoleMappings from './modules/AdminRoleMappings.vue'
import AdminSettings from './modules/AdminSettings.vue'

export default {
  name: 'AdminDashboard',
  components: {
    AdminSidebar,
    AdminTopbar,
    AdminOverview,
    AdminUsers,
    AdminOnboarding,
    AdminSimulado,
    AdminCursos,
    AdminGamificacao,
    AdminCertificados,
    AdminAudit,
    AdminSecurity,
    AdminRoleMappings,
    AdminSettings
  },
  setup() {
    const { activeTab, selectMenuItem } = useNavigation()
    const { sidebarCollapsed } = useSidebar()
    const router = useRouter()
    const { can } = usePermissions()

    // Mapeamento dos componentes baseado na aba ativa
    const componentMap = {
      overview: 'AdminOverview',
      users: 'AdminUsers',
      simulado: 'AdminSimulado',
      onboarding: 'AdminOnboarding',
      cursos: 'AdminCursos',
      gamificacao: 'AdminGamificacao',
      certificados: 'AdminCertificados',
      audit: 'AdminAudit',
      security: 'AdminSecurity',
      'role-mappings': 'AdminRoleMappings',
      settings: 'AdminSettings'
    }

    // Mapeamento de permissões por aba
    const permissionMap = {
      overview: ['admin.dashboard.view'],
      users: ['users.view'],
      simulado: ['admin.dashboard.view'],
      onboarding: ['admin.dashboard.view'],
      cursos: ['admin.dashboard.view'],
      gamificacao: ['admin.dashboard.view'],
      certificados: ['admin.dashboard.view'],
      audit: ['audit.view'],
      security: ['security.view'],
      'role-mappings': ['users.manage'],
      settings: ['admin.dashboard.view']
    }

    const currentComponent = computed(() => {
      return componentMap[activeTab.value] || 'AdminOverview'
    })

    const isAllowed = computed(() => {
      const perms = permissionMap[activeTab.value] || []
      return perms.every((p) => can(p))
    })

    // Sincronizar aba ativa com a rota atual
    const syncActiveTabWithRoute = () => {
      const currentPath = router.currentRoute.value.path
      // Handle nested settings routes
      if (currentPath.startsWith('/admin/dashboard/settings')) {
        activeTab.value = 'settings'
        return
      }

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
      currentComponent,
      isAllowed,
      sidebarCollapsed
    }
  }
}
</script>