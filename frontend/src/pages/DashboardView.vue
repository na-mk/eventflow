<script setup>
import { computed, onMounted } from 'vue'
import { ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useEventsStore } from '../stores/events'

const authStore = useAuthStore()
const eventsStore = useEventsStore()
const feedback = ref('')

onMounted(async () => {
  if (!authStore.user && authStore.token) {
    await authStore.fetchMe()
  }

  await eventsStore.fetchMyRegistrations()

  if (authStore.isOrganizer) {
    await eventsStore.fetchMyEvents()
  }
})

const nextRegistration = computed(() => {
  return [...eventsStore.myRegistrations]
    .filter((registration) => registration.status === 'confirmed')
    .sort((a, b) => new Date(a.event.eventDate || 0) - new Date(b.event.eventDate || 0))[0] || null
})

function formatDate(value) {
  if (!value) return 'Date a confirmer'
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

async function cancelRegistration(registrationId) {
  feedback.value = ''

  try {
    await eventsStore.cancelRegistration(registrationId)
    feedback.value = 'Inscription annulee avec succes.'
  } catch (err) {
    feedback.value = err?.response?.data?.message || 'Impossible d annuler cette inscription.'
  }
}
</script>

<template>
  <section class="space-y-8">
    <div class="glass p-8 md:p-10">
      <div class="section-label mb-3">DashboardView</div>
      <h1 class="text-3xl md:text-4xl font-extrabold text-main">Bonjour {{ authStore.user?.firstName || 'utilisateur' }}</h1>
      <p class="mt-3 max-w-2xl text-base leading-7 text-sub">
        Retrouvez votre resume personnel, vos inscriptions et vos outils de gestion selon votre role.
      </p>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.2em] text-muted">Role</div>
        <div class="mt-3 text-2xl font-black text-main">{{ authStore.role }}</div>
      </article>
      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.2em] text-muted">Mes inscriptions</div>
        <div class="mt-3 text-2xl font-black text-main">{{ eventsStore.myRegistrations.length }}</div>
      </article>
      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.2em] text-muted">Mes evenements</div>
        <div class="mt-3 text-2xl font-black text-main">{{ authStore.isOrganizer ? eventsStore.events.length : 0 }}</div>
      </article>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
      <section class="glass p-6">
        <div class="section-label mb-2">Prochaine etape</div>
        <template v-if="nextRegistration">
          <h2 class="text-2xl font-extrabold text-main">{{ nextRegistration.event.title }}</h2>
          <p class="mt-3 text-sm leading-6 text-sub">
            Prochaine participation confirmee le {{ formatDate(nextRegistration.event.eventDate) }} a {{ nextRegistration.event.location }}.
          </p>
          <router-link :to="`/events/${nextRegistration.event.id}`" class="btn-primary mt-5">Voir l evenement</router-link>
        </template>
        <template v-else>
          <h2 class="text-2xl font-extrabold text-main">Aucune inscription active</h2>
          <p class="mt-3 text-sm leading-6 text-sub">Explorez le catalogue public pour rejoindre un prochain evenement.</p>
          <router-link to="/events" class="btn-primary mt-5">Parcourir les evenements</router-link>
        </template>
      </section>

      <section class="glass p-6">
        <div class="section-label mb-2">Acces rapides</div>
        <div class="grid gap-3">
          <router-link to="/profile" class="rounded-2xl border p-4 transition hover:-translate-y-0.5" style="border-color:var(--border); background:var(--bg-card)">
            <div class="font-bold text-main">Mon profil RGPD</div>
            <div class="mt-1 text-sm text-sub">Consulter, modifier ou exporter mes donnees.</div>
          </router-link>
          <router-link v-if="authStore.isOrganizer" to="/events/create" class="rounded-2xl border p-4 transition hover:-translate-y-0.5" style="border-color:var(--border); background:var(--bg-card)">
            <div class="font-bold text-main">Creer un evenement</div>
            <div class="mt-1 text-sm text-sub">Publier une nouvelle fiche evenement.</div>
          </router-link>
          <router-link v-if="authStore.isAdmin" to="/admin" class="rounded-2xl border p-4 transition hover:-translate-y-0.5" style="border-color:var(--border); background:var(--bg-card)">
            <div class="font-bold text-main">Administration</div>
            <div class="mt-1 text-sm text-sub">Piloter l ensemble des evenements de la plateforme.</div>
          </router-link>
        </div>
      </section>
    </div>

    <section class="glass p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <div class="section-label mb-2">Inscriptions</div>
          <h2 class="text-2xl font-extrabold text-main">Mes evenements reserves</h2>
        </div>
        <button class="btn-ghost" @click="eventsStore.fetchMyRegistrations()">Actualiser</button>
      </div>

      <div v-if="feedback" class="mt-4 rounded-xl border px-4 py-3 text-sm"
        :style="feedback.includes('succes') ? 'background:rgba(16,185,129,0.08); border-color:rgba(16,185,129,0.2); color:#059669' : 'background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2); color:#dc2626'">
        {{ feedback }}
      </div>

      <div v-if="eventsStore.myRegistrations.length === 0" class="mt-5 rounded-2xl border p-6 text-sm text-sub"
        style="border-color:var(--border); background:var(--bg-card)">
        Aucune inscription a afficher pour le moment.
      </div>

      <div v-else class="mt-5 grid gap-4">
        <article v-for="registration in eventsStore.myRegistrations" :key="registration.id" class="rounded-2xl border p-5"
          style="border-color:var(--border); background:var(--bg-card)">
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <span class="badge-green" v-if="registration.status === 'confirmed'">confirmee</span>
                <span class="badge-orange" v-else-if="registration.status === 'cancelled'">annulee</span>
                <span class="badge-blue" v-else>en attente</span>
              </div>
              <h3 class="mt-3 text-xl font-extrabold text-main">{{ registration.event?.title }}</h3>
              <p class="mt-1 text-sm text-sub">
                {{ formatDate(registration.event?.eventDate) }} · {{ registration.event?.location }}
              </p>
              <p class="mt-2 text-xs font-semibold text-sub">
                {{ registration.event?.remainingPlaces }} place{{ registration.event?.remainingPlaces > 1 ? 's' : '' }} restante{{ registration.event?.remainingPlaces > 1 ? 's' : '' }}
              </p>
            </div>

            <div class="flex flex-wrap gap-3">
              <router-link :to="`/events/${registration.event?.id}`" class="btn-outline">Voir le detail</router-link>
              <button
                v-if="registration.status !== 'cancelled'"
                class="btn-danger"
                @click="cancelRegistration(registration.id)"
              >
                Annuler mon inscription
              </button>
            </div>
          </div>
        </article>
      </div>
    </section>
  </section>
</template>
