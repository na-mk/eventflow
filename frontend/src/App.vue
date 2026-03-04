<script setup>
import { useUserStore } from './stores/user'
const userStore = useUserStore()
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <!-- Header -->
    <header class="bg-white border-b">
      <div class="max-w-5xl mx-auto px-4 py-5 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-extrabold tracking-tight">EVENTFLOW</h1>
          <p class="text-sm text-slate-500">Plateforme de gestion d’événements</p>
        </div>

        <div class="flex items-center gap-3">
          <div v-if="userStore.isAuthenticated" class="text-right">
            <p class="text-sm font-semibold leading-none">{{ userStore.user?.name }}</p>
            <p class="text-xs text-slate-500">{{ userStore.user?.email }}</p>
          </div>

          <span
            v-if="userStore.isAuthenticated"
            class="text-xs px-2 py-1 rounded-full border bg-slate-100"
          >
            {{ userStore.role }}
          </span>

          <button
            v-if="userStore.isAuthenticated"
            @click="userStore.logout"
            class="px-3 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800"
          >
            Logout
          </button>
        </div>
      </div>

      <!-- Nav -->
      <nav class="bg-white">
        <div class="max-w-5xl mx-auto px-4 pb-4 flex flex-wrap gap-2">
          <router-link class="navlink" to="/">Dashboard</router-link>

          <router-link v-if="!userStore.isAuthenticated" class="navlink" to="/login">Login</router-link>
          <router-link v-if="!userStore.isAuthenticated" class="navlink" to="/register">Register</router-link>

          <router-link
            v-if="userStore.isAuthenticated && (userStore.role === 'organizer' || userStore.role === 'admin')"
            class="navlink"
            to="/create"
          >
            Create Event
          </router-link>

          <router-link
            v-if="userStore.isAuthenticated && userStore.role === 'admin'"
            class="navlink"
            to="/admin"
          >
            Admin
          </router-link>
        </div>
      </nav>
    </header>

    <!-- Main -->
    <main class="max-w-5xl mx-auto px-4 py-6">
      <router-view />
    </main>

    <footer class="py-8">
      <div class="max-w-5xl mx-auto px-4 text-xs text-slate-400">
        EVENTFLOW • Vue 3 • Node/Express • MongoDB
      </div>
    </footer>
  </div>
</template>

<style scoped>
.navlink {
  @apply text-sm font-medium px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 transition;
}
.router-link-active {
  @apply bg-blue-600 text-white hover:bg-blue-600;
}
</style>