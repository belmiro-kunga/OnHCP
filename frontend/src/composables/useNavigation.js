import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from './useAuth'

// Estado global para garantir que todos os componentes compartilhem a mesma instância
const globalActiveTab = ref('overview')

/**
 * Composable para gerenciar a navegação do dashboard
 */
export function useNavigation() {
  const router = useRouter()
  const activeTab = globalActiveTab

  const menuItems = ref([
    {
      id: 'overview',
      name: 'Menu',
      icon: 'IconDashboard'
    },
    {
      id: 'onboarding',
      name: 'Integração',
      icon: 'IconUserPlus'
    },
    { 
      id: 'users', 
      name: 'Utilizadores',
      icon: 'IconUsers'
    },
    {
      id: 'role-mappings',
      name: 'Mapeamentos',
      icon: 'IconShield'
    },
    { 
      id: 'simulado', 
      name: 'Simulados',
      icon: 'IconClipboard'
    },
    { 
      id: 'simulado-categories', 
      name: 'Categorias',
      icon: 'IconChart'
    },
    { 
      id: 'reports', 
      name: 'Relatórios',
      icon: 'IconChart'
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
      id: 'settings',
      name: 'Configurações',
      icon: 'IconDashboard'
    }
  ])

  const selectMenuItem = (tabId) => {
    // Só navegar se a aba não estiver já ativa
    if (activeTab.value !== tabId) {
      activeTab.value = tabId
      // Navegar para a rota correspondente
      router.push(`/admin/dashboard/${tabId}`)
    }
  }

  const logout = () => {
    const { logout: authLogout } = useAuth()
    // Limpar autenticação
    authLogout()
    // Redirecionar para página de login
    router.push('/admin/login')
  }

  return {
    activeTab,
    menuItems,
    selectMenuItem,
    logout
  }
}