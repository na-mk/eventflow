import { ref, watchEffect } from 'vue'

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
    // Ignore storage errors to keep the UI usable.
  }
}

// Persistance : par défaut light (plus lisible)
const stored = typeof window !== 'undefined' ? safeGetItem('ef-theme') : null
const isDark = ref(stored ? stored === 'dark' : false)

// Applique immédiatement sur l'élément HTML
function applyTheme(dark) {
  if (dark) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
  safeSetItem('ef-theme', dark ? 'dark' : 'light')
}

// Synchronise à chaque changement
watchEffect(() => applyTheme(isDark.value))

export function useTheme() {
  return {
    isDark,
    toggle() { isDark.value = !isDark.value }
  }
}
