// frontend/src/services/api.js
import axios from "axios"

// ✅ Backend sur 4000
const BASE_URL = import.meta.env.VITE_API_URL || "http://localhost:4000/api"

export const api = axios.create({
  baseURL: BASE_URL,
  headers: { "Content-Type": "application/json" },
})

// ✅ Ajoute le token JWT si présent (utile pour routes protégées)
api.interceptors.request.use((config) => {
  const token =
    localStorage.getItem("token") ||
    localStorage.getItem("access_token") ||
    localStorage.getItem("jwt")

  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

// ✅ Log d'erreur clair en dev
api.interceptors.response.use(
  (res) => res,
  (error) => {
    console.error("API ERROR:", {
      url: error?.config?.url,
      method: error?.config?.method,
      status: error?.response?.status,
      data: error?.response?.data,
      message: error?.message,
    })
    return Promise.reject(error)
  }
)