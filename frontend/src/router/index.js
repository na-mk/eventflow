import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from '../stores/user'

import Login from '../pages/Login.vue'
import Register from '../pages/Register.vue'
import Dashboard from '../pages/Dashboard.vue'
import CreateEvent from '../pages/CreateEvent.vue'
import Admin from '../pages/Admin.vue'

const routes = [
  { path: '/', name: 'dashboard', component: Dashboard },

  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },

  {
    path: '/create',
    name: 'create',
    component: CreateEvent,
    meta: { requiresAuth: true, roles: ['organizer', 'admin'] }
  },
  {
    path: '/admin',
    name: 'admin',
    component: Admin,
    meta: { requiresAuth: true, roles: ['admin'] }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Guard global
router.beforeEach((to) => {
  const userStore = useUserStore()

  // 1) Si la route demande auth
  if (to.meta.requiresAuth && !userStore.isAuthenticated) {
    return { name: 'login' }
  }

  // 2) Si la route demande un rôle
  if (to.meta.roles && to.meta.roles.length > 0) {
    const role = userStore.role
    if (!role || !to.meta.roles.includes(role)) {
      return { name: 'dashboard' } // ou une page "403"
    }
  }

  return true
})

export default router