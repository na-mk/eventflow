<script setup>
import { useUserStore } from './stores/user'
const userStore = useUserStore()
</script>

<template>
  <h1>EVENTFLOW</h1>

  <nav style="display:flex; gap:10px; flex-wrap: wrap; margin-bottom: 20px;">
    <router-link to="/">Dashboard</router-link>

    <router-link v-if="!userStore.isAuthenticated" to="/login">Login</router-link>
    <router-link v-if="!userStore.isAuthenticated" to="/register">Register</router-link>

    <router-link
      v-if="userStore.isAuthenticated && (userStore.role === 'organizer' || userStore.role === 'admin')"
      to="/create"
    >
      Create Event
    </router-link>

    <router-link v-if="userStore.isAuthenticated && userStore.role === 'admin'" to="/admin">
      Admin
    </router-link>

    <button v-if="userStore.isAuthenticated" @click="userStore.logout">
      Logout
    </button>
  </nav>

  <router-view />
</template>