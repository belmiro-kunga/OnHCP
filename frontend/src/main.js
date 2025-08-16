import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import UserLogin from './components/UserLogin.vue'
import AdminLogin from './components/AdminLogin.vue'
import UserDashboard from './components/UserDashboard.vue'
import AdminDashboard from './components/AdminDashboard.vue'
import './style.css'

const routes = [
  { path: '/', component: UserLogin },
  { path: '/admin/login', component: AdminLogin },
  { path: '/dashboard', component: UserDashboard },
  { path: '/admin/dashboard', redirect: '/admin/dashboard/overview' },
  { path: '/admin/dashboard/overview', component: AdminDashboard },
  { path: '/admin/dashboard/users', component: AdminDashboard },
  { path: '/admin/dashboard/simulado', component: AdminDashboard },
  { path: '/admin/dashboard/onboarding', component: AdminDashboard },
  { path: '/admin/dashboard/cursos', component: AdminDashboard },
  { path: '/admin/dashboard/gamificacao', component: AdminDashboard },
  { path: '/admin/dashboard/certificados', component: AdminDashboard },
  { path: '/admin/dashboard/reports', component: AdminDashboard }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

const app = createApp(App)
app.use(router)
app.mount('#app')