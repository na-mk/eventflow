import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import './style.css'

// Applique le thème AVANT le premier rendu (évite le flash)
const saved = localStorage.getItem('ef-theme')
if (saved === 'dark') document.documentElement.classList.add('dark')

createApp(App).use(createPinia()).use(router).mount('#app')