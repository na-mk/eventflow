<script setup>
import { computed, onMounted } from 'vue'
import { useEventsStore } from '../stores/events'
import { useAuthStore } from '../stores/auth'

const eventsStore = useEventsStore()
const authStore = useAuthStore()

onMounted(() => {
  if (!eventsStore.events.length) {
    eventsStore.fetchEvents()
  }
})

const featuredEvents = computed(() => eventsStore.events.slice(0, 3))

function formatDate(value) {
  if (!value) return 'Date a confirmer'
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}
</script>

<template>
  <section class="space-y-10">
    <div class="glass p-8 md:p-10">
      <div class="section-label mb-3">HomeView</div>
      <h2 class="text-3xl md:text-4xl font-extrabold text-main">Un portail evenementiel clair, securise et conforme RGPD</h2>
      <p class="mt-4 max-w-3xl text-base leading-7 text-sub">
        EventFlow permet aux participants de decouvrir des evenements, aux organisateurs de publier leurs sessions
        et aux utilisateurs connectes d'acceder a leur tableau de bord personnel.
      </p>

      <div class="mt-8 flex flex-wrap gap-3">
        <router-link to="/events" class="btn-primary">Explorer les evenements</router-link>
        <router-link v-if="authStore.isAuthenticated" to="/dashboard" class="btn-outline">Ouvrir mon dashboard</router-link>
        <router-link v-else to="/login" class="btn-outline">Se connecter</router-link>
      </div>
    </div>

    <section>
      <div class="mb-5 flex items-end justify-between gap-4">
        <div>
          <div class="section-label mb-2">Apercu public</div>
          <h3 class="text-2xl font-extrabold text-main">Evenements mis en avant</h3>
        </div>
        <router-link to="/events" class="btn-ghost">Voir tout</router-link>
      </div>

      <div v-if="eventsStore.loading" class="grid gap-5 md:grid-cols-3">
        <div v-for="i in 3" :key="i" class="ef-card animate-pulse p-6">
          <div class="h-5 w-28 rounded" style="background:var(--border)"></div>
          <div class="mt-4 h-4 rounded" style="background:var(--border-light)"></div>
          <div class="mt-2 h-4 w-2/3 rounded" style="background:var(--border-light)"></div>
        </div>
      </div>

      <div v-else class="grid gap-5 md:grid-cols-3">
        <article v-for="event in featuredEvents" :key="event.id" class="ef-card p-6">
          <div class="flex items-center justify-between gap-3">
            <span class="badge-green">public</span>
            <span class="text-xs text-muted">{{ formatDate(event.eventDate) }}</span>
          </div>
          <h4 class="mt-4 text-xl font-extrabold text-main">{{ event.title }}</h4>
          <p class="mt-3 line-clamp-3 text-sm leading-6 text-sub">{{ event.description }}</p>
          <div class="mt-5 text-sm text-muted">{{ event.location }}</div>
          <router-link :to="`/events/${event.id}`" class="btn-outline mt-6">Voir le detail</router-link>
        </article>
      </div>
    </section>
  </section>
</template>
