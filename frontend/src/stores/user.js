import { defineStore } from 'pinia'
import { api } from '../services/api'

export const useUserStore = defineStore('user', {
  state: () => ({
    token: localStorage.getItem('token') || null,
    user: JSON.parse(localStorage.getItem('user') || 'null'),
    loading: false,
    error: null
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    role: (state) => {
      const roles = state.user?.roles || []
      if (roles.includes('ROLE_ADMIN')) return 'admin'
      if (roles.includes('ROLE_ORGANIZER')) return 'organizer'
      return 'participant'
    },
    isAdmin: (state) => state.user?.roles?.includes('ROLE_ADMIN') ?? false,
    isOrganizer: (state) => {
      const roles = state.user?.roles || []
      return roles.includes('ROLE_ORGANIZER') || roles.includes('ROLE_ADMIN')
    },
    fullName: (state) => state.user ? `${state.user.firstName} ${state.user.lastName}` : ''
  },

  actions: {
    async register(payload) {
      this.loading = true
      this.error = null
      try {
        await api.post('/auth/register', payload)
        await this.login({ email: payload.email, password: payload.password })
      } catch (err) {
        const apiData = err?.response?.data
        this.error =
          apiData?.message ||
          (apiData?.errors ? JSON.stringify(apiData.errors) : null) ||
          (apiData ? JSON.stringify(apiData) : 'Registration failed')
        throw err
      } finally {
        this.loading = false
      }
    },

    async login(payload) {
      this.loading = true
      this.error = null
      try {
        const res = await api.post('/auth/login', payload)
        this.token = res.data.token
        localStorage.setItem('token', this.token)
        await this.fetchMe()
      } catch (err) {
        this.error = err?.response?.data?.message || 'Login failed'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchMe() {
      try {
        const res = await api.get('/me')
        this.user = res.data.user
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch {
        this.logout()
      }
    },

    async updateProfile(payload) {
      this.loading = true
      try {
        const res = await api.put('/me', payload)
        this.user = res.data
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch (err) {
        this.error = err?.response?.data?.message || 'Update failed'
        throw err
      } finally {
        this.loading = false
      }
    },

    async anonymize() {
      this.loading = true
      try {
        await api.delete('/me')
        this.logout()
      } catch (err) {
        this.error = err?.response?.data?.message || 'Anonymization failed'
        throw err
      } finally {
        this.loading = false
      }
    },

    logout() {
      this.token = null
      this.user = null
      this.error = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }
})
