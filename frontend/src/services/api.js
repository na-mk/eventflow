import axios from "axios"

const api = axios.create({
  baseURL: "/api"
})

// ajouter automatiquement le token JWT
api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token")

  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }

  return config
})

export { api }