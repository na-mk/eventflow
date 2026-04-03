import axios from "axios"

const BASE_URL = import.meta.env.VITE_API_URL || "/api"
let inMemoryToken = ""

function emitAuthEvent(name) {
  if (typeof window !== "undefined") {
    window.dispatchEvent(new CustomEvent(name))
  }
}

function getStoredToken() {
  return inMemoryToken || (
    localStorage.getItem("token") ||
    localStorage.getItem("access_token") ||
    localStorage.getItem("jwt") ||
    ""
  )
}

export function syncApiToken(token) {
  inMemoryToken = token || ""

  if (inMemoryToken) {
    api.defaults.headers.common.Authorization = `Bearer ${inMemoryToken}`
  } else {
    delete api.defaults.headers.common.Authorization
  }
}

function clearStoredAuth() {
  localStorage.removeItem("token")
  localStorage.removeItem("access_token")
  localStorage.removeItem("jwt")
  localStorage.removeItem("user")
  syncApiToken("")
  emitAuthEvent("eventflow:auth-cleared")
}

export const api = axios.create({
  baseURL: BASE_URL,
  headers: { "Content-Type": "application/json" },
})

api.interceptors.request.use((config) => {
  const token = getStoredToken()

  config.headers = config.headers || {}

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  } else {
    delete config.headers.Authorization
  }

  return config
})

api.interceptors.response.use(
  (res) => res,
  (error) => {
    const status = error?.response?.status
    const responseMessage = error?.response?.data?.message || ""
    const currentToken = getStoredToken()
    const requestAuthorization =
      error?.config?.headers?.Authorization ||
      error?.config?.headers?.authorization ||
      ""
    const failedToken = requestAuthorization.startsWith("Bearer ")
      ? requestAuthorization.slice("Bearer ".length)
      : ""
    const shouldResetSession =
      status === 401 &&
      /expired jwt token|invalid jwt token|jwt token not found/i.test(responseMessage) &&
      (!failedToken || failedToken === currentToken)

    if (shouldResetSession) {
      clearStoredAuth()

      if (typeof window !== "undefined" && window.location.pathname !== "/login") {
        window.location.assign("/login")
      }
    }

    console.error("API ERROR:", {
      url: error?.config?.url,
      method: error?.config?.method,
      status,
      data: error?.response?.data,
      message: error?.message,
    })

    return Promise.reject(error)
  }
)
