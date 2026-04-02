import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import { api } from '../services/api'

function safeGetItem(key) {
  try {
    return localStorage.getItem(key)
  } catch {
    return null
  }
}

function safeSetItem(key, value) {
  try {
    localStorage.setItem(key, value)
  } catch {
    // Ignore storage errors to keep the SPA usable.
  }
}

function safeRemoveItem(key) {
  try {
    localStorage.removeItem(key)
  } catch {
    // Ignore storage errors to keep the SPA usable.
  }
}

function safeParseUser() {
  const rawUser = safeGetItem('user')
  if (!rawUser) return null

  try {
    return JSON.parse(rawUser)
  } catch {
    safeRemoveItem('user')
    return null
  }
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref(safeParseUser())
  const token = ref(safeGetItem('token'))
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)
  const isOrganizer = computed(() => {
    const roles = user.value?.roles || []
    return roles.includes('ROLE_ORGANIZER') || roles.includes('ROLE_ADMIN')
  })
  const isAdmin = computed(() => user.value?.roles?.includes('ROLE_ADMIN') ?? false)
  const role = computed(() => {
    const roles = user.value?.roles || []
    if (roles.includes('ROLE_ADMIN')) return 'admin'
    if (roles.includes('ROLE_ORGANIZER')) return 'organizer'
    return 'participant'
  })
  const fullName = computed(() => user.value ? `${user.value.firstName} ${user.value.lastName}` : '')

  async function fetchMe() {
    try {
      const res = await api.get('/me')
      user.value = res.data?.user ?? res.data
      safeSetItem('user', JSON.stringify(user.value))
    } catch {
      logout()
    }
  }

  async function login(credentials) {
    loading.value = true
    error.value = null

    try {
      const res = await api.post('/auth/login', credentials)
      token.value = res.data.token
      safeSetItem('token', token.value)
      await fetchMe()
    } catch (err) {
      error.value = err?.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function register(payload) {
    loading.value = true
    error.value = null

    try {
      await api.post('/auth/register', payload)
      await login({ email: payload.email, password: payload.password })
    } catch (err) {
      const apiData = err?.response?.data
      error.value =
        apiData?.message ||
        (apiData?.errors ? JSON.stringify(apiData.errors) : null) ||
        (apiData ? JSON.stringify(apiData) : 'Registration failed')
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateProfile(payload) {
    loading.value = true

    try {
      const res = await api.put('/me', payload)
      user.value = res.data
      safeSetItem('user', JSON.stringify(user.value))
    } catch (err) {
      error.value = err?.response?.data?.message || 'Update failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  async function anonymize() {
    loading.value = true

    try {
      await api.delete('/me')
      logout()
    } catch (err) {
      error.value = err?.response?.data?.message || 'Anonymization failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  function logout() {
    token.value = null
    user.value = null
    error.value = null
    safeRemoveItem('token')
    safeRemoveItem('user')
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isOrganizer,
    isAdmin,
    role,
    fullName,
    fetchMe,
    login,
    register,
    updateProfile,
    anonymize,
    logout,
  }
})
