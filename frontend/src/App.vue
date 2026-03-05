<script setup>
import { computed } from "vue"
import { useRoute } from "vue-router"
import { useUserStore } from "./stores/user"

const userStore = useUserStore()
const route = useRoute()

const showHero = computed(() => route.path === "/")
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-600 via-violet-600 to-fuchsia-500 text-white">
    <!-- Top bar -->
    <header class="sticky top-0 z-20 backdrop-blur bg-white/10 border-b border-white/15">
      <div class="max-w-6xl mx-auto px-5 py-4 flex items-center justify-between">
        <!-- Brand -->
        <router-link to="/" class="flex items-center gap-3 group">
          <div
            class="h-9 w-9 rounded-2xl bg-white/20 border border-white/20 grid place-items-center font-black
                   group-hover:bg-white/25 transition"
          >
            EF
          </div>
          <div class="leading-tight">
            <h1 class="text-lg font-extrabold tracking-tight">EVENTFLOW</h1>
            <p class="text-xs text-white/70">Gestion professionnelle d’événements</p>
          </div>
        </router-link>

        <!-- Nav -->
        <nav class="flex items-center gap-2 flex-wrap justify-end">
          <router-link class="navlink" to="/">Dashboard</router-link>

          <router-link v-if="!userStore.isAuthenticated" class="navlink" to="/login">
            Login
          </router-link>
          <router-link v-if="!userStore.isAuthenticated" class="navlink" to="/register">
            Register
          </router-link>

          <router-link
            v-if="userStore.isAuthenticated && (userStore.role === 'organizer' || userStore.role === 'admin')"
            class="navlink"
            to="/create"
          >
            Create
          </router-link>

          <router-link
            v-if="userStore.isAuthenticated && userStore.role === 'admin'"
            class="navlink"
            to="/admin"
          >
            Admin
          </router-link>

          <button
            v-if="userStore.isAuthenticated"
            @click="userStore.logout"
            class="ml-2 px-4 py-2 rounded-full bg-white text-slate-900 text-sm font-semibold
                   hover:bg-white/90 transition"
          >
            Logout
          </button>
        </nav>
      </div>
    </header>

    <!-- HERO (uniquement sur /) -->
    <section v-if="showHero" class="max-w-6xl mx-auto px-5 pt-12 pb-10">
      <div class="grid lg:grid-cols-2 gap-10 items-center">
        <!-- Left -->
        <div>
          <h2 class="text-5xl sm:text-6xl font-extrabold tracking-tight leading-[1.05]">
            Manage your <span class="text-white/90">events</span>
            <span class="block text-white/80">like a pro</span>
          </h2>

          <p class="mt-5 text-white/80 text-lg max-w-xl">
            Créez des événements, gérez les inscriptions, sécurisez l’accès avec JWT + RBAC,
            et administrez tout depuis une interface moderne.
          </p>

          <div class="mt-7 flex flex-wrap gap-3">
            <router-link
              to="/"
              class="px-5 py-3 rounded-full bg-white text-slate-900 font-semibold hover:bg-white/90 transition"
            >
              Voir les événements
            </router-link>

            <router-link
              v-if="!userStore.isAuthenticated"
              to="/register"
              class="px-5 py-3 rounded-full bg-white/15 border border-white/25 font-semibold
                     hover:bg-white/20 transition"
            >
              Créer un compte
            </router-link>

            <router-link
              v-if="userStore.isAuthenticated && (userStore.role === 'organizer' || userStore.role === 'admin')"
              to="/create"
              class="px-5 py-3 rounded-full bg-white/15 border border-white/25 font-semibold
                     hover:bg-white/20 transition"
            >
              Créer un événement
            </router-link>
          </div>

          <div class="mt-7 flex flex-wrap gap-2 text-sm text-white/75">
            <span class="chip">Vue 3</span>
            <span class="chip">Node/Express</span>
            <span class="chip">MongoDB</span>
            <span class="chip">JWT + RBAC</span>
          </div>
        </div>

        <!-- Right (preview card) -->
        <div class="bg-white/95 text-slate-900 rounded-3xl shadow-2xl p-6 border border-white/40">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-extrabold tracking-tight">Aperçu</h3>
            <span class="text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">
              Dashboard
            </span>
          </div>

          <p class="mt-2 text-sm text-slate-600">
            Retrouvez ici la liste des événements et commencez à gérer vos inscriptions.
          </p>

          <div class="mt-4 space-y-3">
            <div class="rounded-2xl border p-4 bg-white shadow-sm">
              <div class="flex items-center justify-between">
                <p class="font-semibold">DevCode</p>
                <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700">Paris</span>
              </div>
              <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                Rencontre de développeurs, talks, networking.
              </p>
            </div>

            <div class="rounded-2xl border p-4 bg-white shadow-sm">
              <div class="flex items-center justify-between">
                <p class="font-semibold">Business Event</p>
                <span class="text-xs px-2 py-1 rounded-full bg-slate-100 text-slate-700">Dakar</span>
              </div>
              <p class="text-sm text-slate-600 mt-1 line-clamp-2">
                Conférences et opportunités professionnelles.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CONTENT AREA (toutes les pages s'affichent ici) -->
    <main class="max-w-6xl mx-auto px-5 pb-12">
      <div class="bg-white/95 text-slate-900 rounded-3xl shadow-2xl p-6 border border-white/40">
        <router-view />
      </div>
    </main>

    <footer class="pb-10">
      <div class="max-w-6xl mx-auto px-5 text-xs text-white/60">
        EVENTFLOW • Built with Vue 3 + Tailwind
      </div>
    </footer>
  </div>
</template>

<style scoped>
.navlink {
  @apply px-4 py-2 rounded-full text-sm font-semibold bg-white/10 border border-white/15
         hover:bg-white/15 transition;
}
.router-link-active {
  @apply bg-white text-slate-900 border-white;
}
.chip {
  @apply px-3 py-1 rounded-full bg-white/15 border border-white/15;
}
</style>