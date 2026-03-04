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
    role: (state) => state.user?.role || null
  },

  actions: {
    async register(payload) {
      this.loading = true
      this.error = null
      try {
        const res = await api.post('/auth/register', payload)

        this.token = res.data.token
        this.user = res.data.user

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch (err) {
        this.error = err?.response?.data?.message || 'Register failed'
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
        this.user = res.data.user

        localStorage.setItem('token', this.token)
        localStorage.setItem('user', JSON.stringify(this.user))
      } catch (err) {
        this.error = err?.response?.data?.message || 'Login failed'
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