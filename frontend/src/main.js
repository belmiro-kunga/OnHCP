import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import UserLogin from './components/UserLogin.vue'
import AdminLogin from './components/AdminLogin.vue'
import UserDashboard from './components/UserDashboard.vue'
import AdminDashboard from './components/AdminDashboard.vue'
import './style.css'
import { useAuth } from './composables/useAuth'
import { usePermissions } from './composables/usePermissions'
import { api } from './composables/api'

const routes = [
  { path: '/', component: UserLogin },
  { path: '/admin/login', component: AdminLogin },
  { path: '/dashboard', component: UserDashboard },
  { path: '/admin/dashboard', redirect: '/admin/dashboard/overview' },
  {
    path: '/admin/dashboard/overview',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/users',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['users.view'] }
  },
  // Redirect legacy/removed route to Users to avoid 404 and warnings
  {
    path: '/admin/dashboard/users-report',
    redirect: '/admin/dashboard/users'
  },
  {
    path: '/admin/dashboard/simulado',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/reports',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/onboarding',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/cursos',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/gamificacao',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/certificados',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },

  {
    path: '/admin/dashboard/reports',
    redirect: '/admin/dashboard/users'
  },
  {
    path: '/admin/dashboard/audit',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['audit.view'] }
  },
  {
    path: '/admin/dashboard/security',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['security.view'] }
  },
  {
    path: '/admin/dashboard/role-mappings',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['users.manage'] }
  },
  {
    path: '/admin/dashboard/settings',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/settings/login',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/settings/security',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/settings/audit',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  {
    path: '/admin/dashboard/settings/email',
    component: AdminDashboard,
    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }
  },
  // Rotas do módulo de simulados
  {
    path: '/simulados',
    component: () => import('./components/modules/SimuladoList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/simulados/:id/rules',
    component: () => import('./components/modules/SimuladoRules.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/simulados/:id/exam',
    component: () => import('./components/modules/SimuladoExam.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/simulados/:id/result/:attemptId',
    component: () => import('./components/modules/SimuladoResult.vue'),
    meta: { requiresAuth: true }
  },
  {    path: '/simulados/history',    component: () => import('./components/modules/SimuladoHistory.vue'),    meta: { requiresAuth: true }  },  // Rota do dashboard de analytics  {    path: '/admin/dashboard/analytics',    component: () => import('./components/modules/AnalyticsDashboard.vue'),    meta: { requiresAuth: true, permissions: ['admin.dashboard.view'] }  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Global auth + RBAC/ABAC guard
router.beforeEach((to, from, next) => {
  const { isAuthenticated } = useAuth()
  const { can } = usePermissions()

  // Prevent infinite redirect loops
  if (to.path === '/admin/login' && isAuthenticated.value) {
    return next('/admin/dashboard/overview')
  }

  if (to.meta?.requiresAuth && !isAuthenticated.value) {
    // Prevent redirect loop if already going to login
    if (to.path === '/admin/login') {
      return next()
    }
    return next({ path: '/admin/login', query: { redirect: to.fullPath } })
  }

  const required = to.meta?.permissions
  if (required && Array.isArray(required) && required.length > 0) {
    const allowed = required.every((p) => can(p))
    if (!allowed) {
      // Prevent redirect loop if already going to overview
      if (to.path === '/admin/dashboard/overview') {
        return next()
      }
      return next({ path: '/admin/dashboard/overview', query: { forbidden: '1' } })
    }
  }

  return next()
})

const app = createApp(App)
app.use(router)
// Dev helpers for quick checks in console
if (import.meta.env.DEV) {
  const auth = useAuth()
  const perm = usePermissions()
  window.__api = api
  window.__auth = auth
  window.__perm = perm
  window.can = (p, ctx) => perm.can(p, ctx)
}
// Global 401 handler: redirect to login and clear state
window.addEventListener('auth:unauthorized', () => {
  const { logout } = useAuth()
  logout().finally(() => {
    router.replace({ path: '/admin/login', query: { unauthorized: '1' } })
  })
})
app.mount('#app')