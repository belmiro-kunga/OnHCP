import { ref } from 'vue'
import { useRouter } from 'vue-router'

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
    // Implementar lógica de logout aqui
    router.push('/admin/login')
  }

  return {
    activeTab,
    menuItems,
    selectMenuItem,
    logout
  }
}