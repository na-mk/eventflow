<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useEventsStore } from '../stores/events'
import { useAuthStore } from '../stores/auth'

const route = useRoute()
const eventsStore = useEventsStore()
const authStore = useAuthStore()

const event = ref(null)
const loading = ref(true)
const error = ref('')
const feedback = ref('')
const feedbackSuccess = ref(false)
const registering = ref(false)

onMounted(async () => {
  loading.value = true
  error.value = ''

  try {
    event.value = await eventsStore.fetchEventById(route.params.id)
    if (authStore.isAuthenticated) {
      await eventsStore.fetchMyRegistrations()
    }
  } catch (err) {
    error.value = err?.response?.data?.message || 'Impossible de charger cet evenement.'
  } finally {
    loading.value = false
  }
})

const isRegistered = computed(() => eventsStore.isRegisteredToEvent(route.params.id))

const capacityRatio = computed(() => {
  const max = Number(event.value?.maxParticipants || 0)
  if (!max) return 0
  return Math.min(Math.round((Number(event.value?.participantsCount || 0) / max) * 100), 100)
})

const mapEmbedUrl = computed(() => {
  const location = event.value?.location?.trim()
  if (!location) return ''

  return `https://maps.google.com/maps?q=${encodeURIComponent(location)}&z=15&output=embed`
})

const mapSearchUrl = computed(() => {
  const location = event.value?.location?.trim()
  if (!location) return ''

  return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(location)}`
})

function formatDate(value) {
  if (!value) return 'Date a confirmer'
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'full',
    timeStyle: 'short',
  }).format(new Date(value))
}

function formatRange(start, end) {
  if (!start) return 'Date a confirmer'
  if (!end) return formatDate(start)

  const startDate = new Date(start)
  const endDate = new Date(end)
  const sameDay = startDate.toDateString() === endDate.toDateString()

  if (sameDay) {
    return `${new Intl.DateTimeFormat('fr-FR', { dateStyle: 'full', timeStyle: 'short' }).format(startDate)} - ${new Intl.DateTimeFormat('fr-FR', { timeStyle: 'short' }).format(endDate)}`
  }

  return `${formatDate(start)} - ${formatDate(end)}`
}

async function register() {
  if (isRegistered.value || registering.value) return

  feedback.value = ''
  feedbackSuccess.value = false
  registering.value = true

  try {
    await eventsStore.registerToEvent(route.params.id)
    feedback.value = 'Inscription confirmée avec succès.'
    feedbackSuccess.value = true
    if (event.value) {
      event.value.remainingPlaces = Math.max(0, Number(event.value.remainingPlaces) - 1)
      event.value.participantsCount = Math.max(0, Number(event.value.maxParticipants) - event.value.remainingPlaces)
      event.value.isFull = event.value.remainingPlaces === 0
    }
  } catch (err) {
    feedback.value = err?.response?.data?.message || 'Impossible de vous inscrire à cet événement.'
  } finally {
    registering.value = false
  }
}
</script>

<template>
  <section class="space-y-8">
    <div v-if="loading" class="glass p-10">
      <div class="h-6 w-48 animate-pulse rounded" style="background:var(--border)"></div>
      <div class="mt-4 h-4 w-2/3 animate-pulse rounded" style="background:var(--border-light)"></div>
    </div>

    <div v-else-if="error" class="glass p-10 text-center">
      <h1 class="text-2xl font-extrabold text-main">Evenement introuvable</h1>
      <p class="mt-3 text-sm text-red-500">{{ error }}</p>
      <router-link to="/events" class="btn-primary mt-6">Retour a la liste</router-link>
    </div>

    <template v-else-if="event">
      <div class="glass p-8 md:p-10">
        <div class="flex flex-wrap items-center gap-3">
          <span class="badge-green" v-if="event.isPublished">public</span>
          <span class="badge-orange" v-else>brouillon</span>
          <span class="badge-purple" v-if="event.isFull">complet</span>
        </div>

        <h1 class="mt-4 text-3xl md:text-4xl font-extrabold text-main">{{ event.title }}</h1>
        <p class="mt-4 max-w-3xl text-base leading-7 text-sub">{{ event.description }}</p>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
          <div class="rounded-2xl border p-4" style="border-color:var(--border); background:var(--bg-card)">
            <div class="text-xs uppercase tracking-[0.18em] text-muted">Horaires</div>
            <div class="mt-2 text-sm font-semibold text-main">{{ formatRange(event.eventDate, event.endDate) }}</div>
          </div>
          <div class="rounded-2xl border p-4" style="border-color:var(--border); background:var(--bg-card)">
            <div class="text-xs uppercase tracking-[0.18em] text-muted">Lieu</div>
            <div class="mt-2 text-sm font-semibold text-main">{{ event.location }}</div>
          </div>
          <div class="rounded-2xl border p-4" style="border-color:var(--border); background:var(--bg-card)">
            <div class="text-xs uppercase tracking-[0.18em] text-muted">Organisateur</div>
            <div class="mt-2 text-sm font-semibold text-main">{{ event.organizer?.firstName }} {{ event.organizer?.lastName }}</div>
          </div>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <section class="glass p-6">
          <div class="section-label mb-2">Capacite</div>
          <h2 class="text-2xl font-extrabold text-main">{{ event.participantsCount }} / {{ event.maxParticipants }} participants</h2>
          <div class="mt-5 h-3 overflow-hidden rounded-full" style="background:var(--border)">
            <div class="h-full rounded-full" :style="`width:${capacityRatio}%; background:linear-gradient(90deg, #f97316, #fb923c)`"></div>
          </div>
          <p class="mt-3 text-sm text-sub">{{ capacityRatio }}% des places reservees.</p>
          <p class="mt-2 text-sm font-semibold" :class="event.remainingPlaces === 0 ? 'text-red-500' : 'text-main'">
            {{ event.remainingPlaces }} place{{ event.remainingPlaces > 1 ? 's' : '' }} restante{{ event.remainingPlaces > 1 ? 's' : '' }}
          </p>
        </section>

        <section class="glass p-6">
          <div class="section-label mb-2">Action</div>
          <h2 class="text-2xl font-extrabold text-main">Participer a cet evenement</h2>
          <p class="mt-3 text-sm leading-6 text-sub">
            Connectez-vous pour vous inscrire. Les evenements complets restent visibles mais ne peuvent plus accepter de nouvelles inscriptions.
          </p>
          <div v-if="feedback" class="mt-4 rounded-xl border px-4 py-3 text-sm"
            :style="feedbackSuccess ? 'background:rgba(16,185,129,0.08); border-color:rgba(16,185,129,0.2); color:#059669' : 'background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2); color:#dc2626'">
            {{ feedback }}
          </div>
          <button v-if="authStore.isAuthenticated && isRegistered" class="btn-outline mt-5 cursor-default" disabled>Inscrit ✓</button>
          <button v-else-if="authStore.isAuthenticated && !event.isFull" class="btn-primary mt-5" :disabled="registering" @click="register">
            {{ registering ? 'Inscription...' : "M'inscrire" }}
          </button>
          <router-link v-else-if="!authStore.isAuthenticated" to="/login" class="btn-primary mt-5">Se connecter</router-link>
          <div v-else class="badge-orange mt-5">Aucune place disponible</div>
        </section>
      </div>

      <section class="glass p-6 md:p-8">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <div class="section-label mb-2">Localisation</div>
            <h2 class="text-2xl font-extrabold text-main">Voir le lieu sur la carte</h2>
          </div>
          <a
            v-if="mapSearchUrl"
            :href="mapSearchUrl"
            target="_blank"
            rel="noreferrer"
            class="btn-ghost text-sm"
          >
            Ouvrir dans Google Maps
          </a>
        </div>

        <p class="mt-3 text-sm text-sub">
          {{ event.location || 'Lieu a confirmer' }}
        </p>

        <div class="mt-6 overflow-hidden rounded-3xl border" style="border-color:var(--border); background:var(--bg-card)">
          <iframe
            v-if="mapEmbedUrl"
            :src="mapEmbedUrl"
            title="Carte de localisation de l evenement"
            class="h-[360px] w-full border-0"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          />
          <div v-else class="grid h-[220px] place-items-center px-6 text-center text-sm text-sub">
            La carte sera disponible dès qu'une localisation précise sera renseignée.
          </div>
        </div>
      </section>
    </template>
  </section>
</template>
