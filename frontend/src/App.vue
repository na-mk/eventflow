<script setup>
import { ref, computed, onMounted } from "vue"
import { useRoute } from "vue-router"
import { useAuthStore } from "./stores/auth"
import { useTheme } from "./composables/useTheme"
import CookieBanner from "./components/CookieBanner.vue"

const userStore = useAuthStore()
const route = useRoute()
const mobileOpen = ref(false)
const { isDark, toggle } = useTheme()

const isHome = computed(() => route.path === "/")

onMounted(async () => {
  if (userStore.token && !userStore.user) {
    await userStore.fetchMe()
  }
})
</script>

<template>
  <div class="min-h-screen" style="background:var(--bg-base);color:var(--text-1)">
    <header class="sticky top-0 z-30 backdrop-blur-xl border-b"
            style="background:var(--bg-nav);border-color:var(--border)">
      <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between gap-6">
        <router-link to="/" class="flex items-center gap-3 shrink-0">
          <div class="h-9 w-9 rounded-xl bg-orange-500 grid place-items-center font-black text-white text-sm shadow-sm">
            EF
          </div>
          <span class="font-bold tracking-wide text-base hidden sm:block text-main"
                style="font-family:'Space Grotesk',sans-serif">
            EVENT<span class="text-orange-500">FLOW</span>
          </span>
        </router-link>

        <nav class="hidden md:flex items-center gap-6">
          <router-link class="nav-link" to="/">Accueil</router-link>
          <router-link class="nav-link" to="/events">Événements</router-link>
          <router-link v-if="userStore.isAuthenticated" class="nav-link" to="/dashboard">Mon espace</router-link>
          <router-link v-if="userStore.isOrganizer" class="nav-link" to="/events/create">Créer un événement</router-link>
          <router-link v-if="userStore.isAdmin" class="nav-link" to="/admin">Administration</router-link>
          <router-link class="nav-link" to="/contact">Contact</router-link>
        </nav>

        <div class="hidden md:flex items-center gap-3">
          <button @click="toggle" class="theme-toggle" :title="isDark ? 'Mode clair' : 'Mode sombre'">
            <svg v-if="isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m8.66-13H21M3 12H2m15.54-6.46l-.7.7M7.16 16.84l-.7.7M18.36 18.36l-.7-.7M6.34 6.34l-.7-.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
          </button>

          <template v-if="!userStore.isAuthenticated">
            <router-link to="/login" class="btn-ghost">Connexion</router-link>
            <router-link to="/register" class="btn-primary text-xs px-4 py-2">S'inscrire</router-link>
          </template>
          <template v-else>
            <router-link to="/profile" class="btn-ghost text-xs">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ userStore.fullName || 'Mon profil' }}
            </router-link>
            <button @click="userStore.logout" class="btn-outline text-xs px-4 py-2">Déconnexion</button>
          </template>
        </div>

        <div class="md:hidden flex items-center gap-2">
          <button @click="toggle" class="theme-toggle">
            <svg v-if="isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m8.66-13H21M3 12H2m15.54-6.46l-.7.7M7.16 16.84l-.7.7M18.36 18.36l-.7-.7M6.34 6.34l-.7-.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
          </button>
          <button @click="mobileOpen = !mobileOpen" class="p-2 rounded-lg text-sub hover:text-main" style="transition:color 0.15s">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <Transition name="slide-up">
        <div v-if="mobileOpen" class="md:hidden border-t px-6 py-4 space-y-1"
             style="border-color:var(--border);background:var(--bg-card)">
          <router-link @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/">Accueil</router-link>
          <router-link @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/events">Événements</router-link>
          <router-link v-if="userStore.isAuthenticated" @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/dashboard">Mon espace</router-link>
          <router-link v-if="userStore.isOrganizer" @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/events/create">Créer un événement</router-link>
          <router-link v-if="userStore.isAdmin" @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/admin">Administration</router-link>
          <router-link @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/contact">Contact</router-link>
          <div class="pt-3 border-t flex flex-col gap-2" style="border-color:var(--border)">
            <template v-if="!userStore.isAuthenticated">
              <router-link @click="mobileOpen=false" to="/login" class="btn-ghost justify-start">Connexion</router-link>
              <router-link @click="mobileOpen=false" to="/register" class="btn-primary justify-center">S'inscrire</router-link>
            </template>
            <template v-else>
              <router-link @click="mobileOpen=false" to="/profile" class="btn-ghost justify-start">Mon profil</router-link>
              <button @click="userStore.logout(); mobileOpen=false" class="btn-outline justify-center">Déconnexion</button>
            </template>
          </div>
        </div>
      </Transition>
    </header>

    <section v-if="isHome" class="relative overflow-hidden">
      <div class="absolute inset-0 bg-grid opacity-100 pointer-events-none"></div>
      <div class="absolute -top-24 left-1/3 w-[680px] h-[460px] bg-orange-500/10 blur-[120px] rounded-full pointer-events-none"></div>
      <div class="absolute top-24 right-0 w-[380px] h-[380px] bg-purple-600/5 blur-[100px] rounded-full pointer-events-none"></div>

      <div class="relative max-w-7xl mx-auto px-6 pt-20 pb-16 lg:pt-28 lg:pb-24">
        <div class="grid items-center gap-14 lg:grid-cols-[1.15fr_0.85fr]">
          <div class="max-w-3xl">
            <div class="section-label mb-4">Découvrez. Participez. Organisez.</div>
            <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold leading-[1.03] tracking-tight text-main">
              Des événements qui<br>
              <span class="gradient-text">rassemblent.</span>
            </h1>
            <p class="mt-6 text-lg text-sub max-w-2xl leading-relaxed">
              Découvrez des conférences, formations et rencontres qui vous correspondent,
              ou créez votre propre événement en quelques minutes.
            </p>

            <div class="mt-9 flex flex-wrap gap-4">
              <router-link to="/events" class="btn-primary px-8 py-4 text-base rounded-2xl">
                Découvrir les événements
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
              </router-link>
              <router-link v-if="userStore.isOrganizer" to="/events/create" class="btn-outline px-8 py-4 text-base rounded-2xl">Créer un événement</router-link>
              <router-link v-else to="/register" class="btn-outline px-8 py-4 text-base rounded-2xl">Organiser un événement</router-link>
            </div>
          </div>

          <div class="grid gap-4 sm:grid-cols-3 lg:grid-cols-1">
            <article class="glass p-5 lg:p-6">
              <div class="flex items-center gap-4">
                <div class="h-11 w-11 rounded-2xl bg-orange-500/10 grid place-items-center text-orange-500 text-xl">⌕</div>
                <div>
                  <h2 class="font-extrabold text-main">Découvrez</h2>
                  <p class="mt-1 text-sm leading-6 text-sub">Trouvez facilement des événements qui correspondent à vos envies.</p>
                </div>
              </div>
            </article>
            <article class="glass p-5 lg:p-6">
              <div class="flex items-center gap-4">
                <div class="h-11 w-11 rounded-2xl bg-orange-500/10 grid place-items-center text-orange-500 text-xl">✓</div>
                <div>
                  <h2 class="font-extrabold text-main">Participez</h2>
                  <p class="mt-1 text-sm leading-6 text-sub">Réservez votre place et retrouvez toutes vos inscriptions au même endroit.</p>
                </div>
              </div>
            </article>
            <article class="glass p-5 lg:p-6">
              <div class="flex items-center gap-4">
                <div class="h-11 w-11 rounded-2xl bg-orange-500/10 grid place-items-center text-orange-500 text-xl">＋</div>
                <div>
                  <h2 class="font-extrabold text-main">Organisez</h2>
                  <p class="mt-1 text-sm leading-6 text-sub">Créez vos événements et suivez vos participants depuis un seul espace.</p>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <main class="max-w-7xl mx-auto px-6 pb-20" :class="isHome ? 'pt-2' : 'pt-10'">
      <router-view v-slot="{ Component }">
        <Transition name="fade" mode="out-in">
          <component :is="Component" />
        </Transition>
      </router-view>
    </main>

    <footer class="border-t" style="border-color:var(--border);background:var(--bg-card)">
      <div class="max-w-7xl mx-auto px-6 py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
        <div>
          <div class="flex items-center gap-3">
            <div class="h-8 w-8 rounded-xl bg-orange-500 grid place-items-center font-black text-white text-xs">EF</div>
            <span class="font-bold text-main">EventFlow</span>
          </div>
          <p class="mt-2 text-xs text-muted">Des événements plus simples à découvrir, créer et partager.</p>
        </div>
        <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-xs text-muted">
          <router-link to="/events" class="hover:text-sub transition">Événements</router-link>
          <router-link to="/contact" class="hover:text-sub transition">Contact</router-link>
          <router-link to="/privacy" class="hover:text-sub transition">Politique de confidentialité</router-link>
          <router-link v-if="userStore.isAuthenticated" to="/profile" class="hover:text-sub transition">Mes données</router-link>
          <span>© 2026 EventFlow</span>
        </div>
      </div>
    </footer>

    <CookieBanner />
  </div>
</template>