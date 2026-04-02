import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './style.css'

function safeGetItem(key) {
  try {
    return localStorage.getItem(key)
  } catch {
    return null
  }
}

// Applique le thème AVANT le premier rendu (évite le flash)
const saved = safeGetItem('ef-theme')
if (saved === 'dark') document.documentElement.classList.add('dark')

createApp(App).use(createPinia()).use(router).mount('#app')
