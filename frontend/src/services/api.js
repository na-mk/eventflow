import axios from "axios"

const BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:8000/api"

function clearStoredAuth() {
  localStorage.removeItem("token")
  localStorage.removeItem("access_token")
  localStorage.removeItem("jwt")
  localStorage.removeItem("user")
}

function normalizeUrl(url = "") {
  if (url.startsWith("http://") || url.startsWith("https://")) {
    try {
      return new URL(url).pathname
    } catch {
      return url
    }
  }

  return url
}

function isPublicRequest(config) {
  const method = (config?.method || "get").toLowerCase()
  const url = normalizeUrl(config?.url || "")

  if (method === "get" && (url === "/events" || /^\/events\/[^/]+$/.test(url))) {
    return true
  }

  if (method === "post" && (url === "/auth/login" || url === "/auth/register")) {
    return true
  }

  return false
}

export const api = axios.create({
  baseURL: BASE_URL,
  headers: { "Content-Type": "application/json" },
})

api.interceptors.request.use((config) => {
  const token =
    localStorage.getItem("token") ||
    localStorage.getItem("access_token") ||
    localStorage.getItem("jwt")

  if (token && !isPublicRequest(config)) {
    config.headers.Authorization = `Bearer ${token}`
  } else if (config?.headers?.Authorization) {
    delete config.headers.Authorization
  }

  return config
})

api.interceptors.response.use(
  (res) => res,
  (error) => {
    const status = error?.response?.status
    const responseMessage = error?.response?.data?.message || ""

    if (status === 401 && /expired jwt token|invalid jwt token|jwt token not found/i.test(responseMessage)) {
      clearStoredAuth()
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
