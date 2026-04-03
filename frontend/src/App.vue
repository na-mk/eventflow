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

const isDashboard = computed(() => route.path === "/")

onMounted(async () => {
  if (userStore.token && !userStore.user) {
    await userStore.fetchMe()
  }
})
</script>

<template>
  <div class="min-h-screen" style="background:var(--bg-base);color:var(--text-1)">

    <!-- ── NAVBAR ──────────────────────────────────────────── -->
    <header class="sticky top-0 z-30 backdrop-blur-xl border-b"
            style="background:var(--bg-nav);border-color:var(--border)">
      <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between gap-6">

        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-3 shrink-0">
          <div class="h-8 w-8 rounded-lg bg-orange-500 grid place-items-center font-black text-white text-sm">
            EF
          </div>
          <span class="font-bold tracking-wide text-base hidden sm:block text-main"
                style="font-family:'Space Grotesk',sans-serif">
            EVENT<span class="text-orange-500">FLOW</span>
          </span>
        </router-link>

        <!-- Nav centre (desktop) -->
        <nav class="hidden md:flex items-center gap-6">
          <router-link class="nav-link" to="/">Accueil</router-link>
          <router-link class="nav-link" to="/events">Événements</router-link>
          <router-link v-if="userStore.isAuthenticated" class="nav-link" to="/dashboard">Dashboard</router-link>
          <router-link v-if="userStore.isOrganizer" class="nav-link" to="/events/create">Créer</router-link>
          <router-link v-if="userStore.isAdmin" class="nav-link" to="/admin">Admin</router-link>
          <router-link class="nav-link" to="/contact">Contact</router-link>
          <router-link class="nav-link" to="/privacy">Confidentialité</router-link>
        </nav>

        <!-- Actions (desktop) -->
        <div class="hidden md:flex items-center gap-3">
          <!-- Theme toggle -->
          <button @click="toggle" class="theme-toggle" :title="isDark ? 'Mode clair' : 'Mode sombre'">
            <!-- Sun icon (shown in dark mode) -->
            <svg v-if="isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 3v1m0 16v1m8.66-13H21M3 12H2m15.54-6.46l-.7.7M7.16 16.84l-.7.7M18.36 18.36l-.7-.7M6.34 6.34l-.7-.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <!-- Moon icon (shown in light mode) -->
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
            <button @click="userStore.logout" class="btn-outline text-xs px-4 py-2">
              Déconnexion
            </button>
          </template>
        </div>

        <!-- Burger (mobile) -->
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
          <button
            @click="mobileOpen = !mobileOpen"
            class="p-2 rounded-lg text-sub hover:text-main"
            style="transition:color 0.15s"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"/>
              <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M6 18L18 6M6 6l12 12"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Mobile menu -->
      <Transition name="slide-up">
        <div v-if="mobileOpen" class="md:hidden border-t px-6 py-4 space-y-1"
             style="border-color:var(--border);background:var(--bg-card)">
          <router-link @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/">Accueil</router-link>
          <router-link @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/events">Événements</router-link>
          <router-link v-if="userStore.isAuthenticated" @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/dashboard">Dashboard</router-link>
          <router-link v-if="userStore.isOrganizer" @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/events/create">Créer un événement</router-link>
          <router-link v-if="userStore.isAdmin" @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/admin">Administration</router-link>
          <router-link @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/contact">Contact</router-link>
          <router-link @click="mobileOpen=false" class="block py-2 text-sm text-sub hover:text-main transition-colors" to="/privacy">Confidentialité</router-link>
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

    <!-- ── HERO (dashboard uniquement) ────────────────────── -->
    <section v-if="isDashboard" class="relative overflow-hidden">
      <div class="absolute inset-0 bg-grid opacity-100 pointer-events-none"></div>
      <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px]
                  bg-orange-500/10 blur-[120px] rounded-full pointer-events-none"></div>
      <div class="absolute top-20 right-0 w-[400px] h-[400px]
                  bg-purple-600/5 blur-[100px] rounded-full pointer-events-none"></div>

      <div class="relative max-w-7xl mx-auto px-6 pt-24 pb-20">
        <div class="max-w-3xl">
          <div class="section-label mb-4">Plateforme d'événements professionnels</div>

          <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold leading-[1.05] tracking-tight text-main">
            Vos événements,<br>
            <span class="gradient-text">réinventés.</span>
          </h1>

          <p class="mt-6 text-lg text-sub max-w-xl leading-relaxed">
            Créez, gérez et découvrez des conférences, meetups et formations.
            Une plateforme moderne, sécurisée et conforme RGPD.
          </p>

          <div class="mt-10 flex flex-wrap gap-4">
            <template v-if="!userStore.isAuthenticated">
              <router-link to="/register" class="btn-primary px-8 py-4 text-base rounded-2xl">
                Commencer gratuitement
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
              </router-link>
              <router-link to="/login" class="btn-outline px-8 py-4 text-base rounded-2xl">
                Se connecter
              </router-link>
            </template>
            <template v-else>
              <router-link v-if="userStore.isOrganizer" to="/events/create" class="btn-primary px-8 py-4 text-base rounded-2xl">
                Créer un événement
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
              </router-link>
            </template>
          </div>

          <!-- Stats -->
          <div class="mt-14 flex flex-wrap gap-8">
            <div>
              <div class="text-3xl font-extrabold gradient-text">100%</div>
              <div class="text-xs text-muted mt-1 uppercase tracking-wider">Conforme RGPD</div>
            </div>
            <div class="border-l pl-8" style="border-color:var(--border)">
              <div class="text-3xl font-extrabold text-main">JWT</div>
              <div class="text-xs text-muted mt-1 uppercase tracking-wider">Sécurisé RS256</div>
            </div>
            <div class="border-l pl-8" style="border-color:var(--border)">
              <div class="text-3xl font-extrabold text-main">REST</div>
              <div class="text-xs text-muted mt-1 uppercase tracking-wider">API Symfony 6.4</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CONTENT ──────────────────────────────────────────── -->
    <main class="max-w-7xl mx-auto px-6 pb-20" :class="isDashboard ? 'pt-4' : 'pt-10'">
      <router-view v-slot="{ Component }">
        <Transition name="fade" mode="out-in">
          <component :is="Component" />
        </Transition>
      </router-view>
    </main>

    <!-- ── FOOTER ───────────────────────────────────────────── -->
    <footer class="border-t" style="border-color:var(--border);background:var(--bg-card)">
      <div class="max-w-7xl mx-auto px-6 py-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <div class="h-7 w-7 rounded-lg bg-orange-500 grid place-items-center font-black text-white text-xs">EF</div>
          <span class="text-sm text-muted">EVENTFLOW — Symfony 6.4 + Vue 3</span>
        </div>
        <div class="flex items-center gap-6 text-xs text-muted">
          <router-link to="/" class="hover:text-sub transition">Accueil</router-link>
          <router-link to="/contact" class="hover:text-sub transition">Contact</router-link>
          <router-link to="/privacy" class="hover:text-sub transition">Politique de confidentialité</router-link>
          <router-link v-if="userStore.isAuthenticated" to="/profile" class="hover:text-sub transition">Mes données (RGPD)</router-link>
          <span>© 2026</span>
        </div>
      </div>
    </footer>

    <CookieBanner />
  </div>
</template>
