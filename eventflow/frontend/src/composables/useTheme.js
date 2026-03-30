import { ref, watchEffect } from 'vue'

// Persistance : par défaut light (plus lisible)
const stored = typeof window !== 'undefined' ? localStorage.getItem('ef-theme') : null
const isDark = ref(stored ? stored === 'dark' : false)

// Applique immédiatement sur l'élément HTML
function applyTheme(dark) {
  if (dark) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
  localStorage.setItem('ef-theme', dark ? 'dark' : 'light')
}

// Synchronise à chaque changement
watchEffect(() => applyTheme(isDark.value))

export function useTheme() {
  return {
    isDark,
    toggle() { isDark.value = !isDark.value }
  }
}
