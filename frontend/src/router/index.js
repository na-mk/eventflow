import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

import HomeView from '../pages/HomeView.vue'
import EventListView from '../pages/Dashboard.vue'
import EventDetailView from '../pages/EventDetailView.vue'
import LoginView from '../pages/Login.vue'
import RegisterView from '../pages/Register.vue'
import DashboardView from '../pages/DashboardView.vue'
import EventFormView from '../pages/CreateEvent.vue'
import ProfileView from '../pages/MyData.vue'
import PrivacyView from '../pages/Privacy.vue'
import AdminView from '../pages/Admin.vue'
import ContactView from '../pages/Contact.vue'

const routes = [
  { path: '/', name: 'home', component: HomeView },
  { path: '/events', name: 'events', component: EventListView },
  { path: '/events/:id', name: 'event-detail', component: EventDetailView, props: true },
  { path: '/login', name: 'login', component: LoginView },
  { path: '/register', name: 'register', component: RegisterView },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: DashboardView,
    meta: { requiresAuth: true },
  },
  {
    path: '/events/create',
    name: 'event-create',
    component: EventFormView,
    meta: { requiresAuth: true, roles: ['organizer', 'admin'] },
  },
  {
    path: '/events/:id/edit',
    name: 'event-edit',
    component: EventFormView,
    meta: { requiresAuth: true, roles: ['organizer', 'admin'] },
    props: true,
  },
  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,
    meta: { requiresAuth: true },
  },
  { path: '/contact', name: 'contact', component: ContactView },
  { path: '/privacy', name: 'privacy', component: PrivacyView },

  // Legacy aliases kept for compatibility with the existing project.
  { path: '/my-data', redirect: '/profile' },
  { path: '/create', redirect: '/events/create' },
  {
    path: '/admin',
    name: 'admin',
    component: AdminView,
    meta: { requiresAuth: true, roles: ['admin'] },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()

  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchMe()
    } catch (err) {
      if (err?.response?.status === 401 && to.name !== 'login') {
        return { name: 'login', query: { redirect: to.fullPath } }
      }
    }
  }

  if ((to.name === 'login' || to.name === 'register') && authStore.isAuthenticated) {
    return typeof to.query.redirect === 'string' ? to.query.redirect : { name: 'home' }
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.roles && to.meta.roles.length > 0) {
    const role = authStore.role
    if (!role || !to.meta.roles.includes(role)) {
      return { name: 'events' }
    }
  }

  return true
})

export default router
