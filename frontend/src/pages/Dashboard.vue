<script setup>
import { computed, onMounted, ref } from 'vue'
import { useEventsStore } from '../stores/events'
import { useAuthStore } from '../stores/auth'

const eventsStore = useEventsStore()
const userStore = useAuthStore()
const q = ref('')
const sortBy = ref('dateAsc')
const registrationFeedback = ref('')
const registrationError = ref('')
const registeringEventId = ref(null)

const cardAccents = [
  { from: '#f97316', to: '#fb923c' },
  { from: '#8b5cf6', to: '#a78bfa' },
  { from: '#06b6d4', to: '#22d3ee' },
  { from: '#10b981', to: '#34d399' },
  { from: '#f43f5e', to: '#fb7185' },
  { from: '#3b82f6', to: '#60a5fa' },
]

onMounted(async () => {
  await eventsStore.fetchEvents()
  if (userStore.isAuthenticated) {
    await eventsStore.fetchMyRegistrations()
  }
})

function formatDate(iso) {
  if (!iso) return { day: '—', month: '—', year: '' }
  const d = new Date(iso)
  return {
    day: d.toLocaleDateString('fr-FR', { day: '2-digit' }),
    month: d.toLocaleDateString('fr-FR', { month: 'short' }).toUpperCase(),
    year: d.getFullYear(),
  }
}

function formatTimeRange(start, end) {
  if (!start) return 'Horaire à confirmer'

  const startDate = new Date(start)
  const startTime = new Intl.DateTimeFormat('fr-FR', { timeStyle: 'short' }).format(startDate)

  if (!end) return startTime

  const endDate = new Date(end)
  const endTime = new Intl.DateTimeFormat('fr-FR', { timeStyle: 'short' }).format(endDate)

  return `${startTime} - ${endTime}`
}

function organizerLabel(organizer) {
  if (!organizer) return 'Organisateur'

  const fullName = [organizer.firstName, organizer.lastName].filter(Boolean).join(' ').trim()
  return fullName || organizer.email || 'Organisateur'
}

function accent(i) { return cardAccents[i % cardAccents.length] }

const filteredEvents = computed(() => {
  const s = q.value.trim().toLowerCase()
  let list = [...eventsStore.events]
  if (s) list = list.filter(e => (e.title + ' ' + e.description + ' ' + e.location).toLowerCase().includes(s))
  if (sortBy.value === 'dateAsc') list.sort((a, b) => new Date(a.eventDate || 0) - new Date(b.eventDate || 0))
  if (sortBy.value === 'dateDesc') list.sort((a, b) => new Date(b.eventDate || 0) - new Date(a.eventDate || 0))
  if (sortBy.value === 'title') list.sort((a, b) => (a.title || '').localeCompare(b.title || ''))
  return list
})

async function handleDelete(id) {
  if (!confirm('Supprimer cet événement ?')) return
  await eventsStore.deleteEvent(id)
}

async function handleRegister(eventId) {
  if (eventsStore.isRegisteredToEvent(eventId) || registeringEventId.value !== null) return

  registrationFeedback.value = ''
  registrationError.value = ''
  registeringEventId.value = eventId

  try {
    await eventsStore.registerToEvent(eventId)
    registrationFeedback.value = 'Inscription confirmée avec succès.'
  } catch (err) {
    registrationError.value = err?.response?.data?.message || 'Impossible de vous inscrire à cet événement.'
  } finally {
    registeringEventId.value = null
  }
}
</script>

<template>
  <section>
    <div class="mb-9 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <div class="section-label mb-2">Explorez</div>
        <h1 class="text-3xl md:text-4xl font-extrabold text-main">Trouvez votre prochain événement</h1>
        <p class="mt-3 max-w-2xl text-sm md:text-base leading-7 text-sub">
          Découvrez les conférences, formations et rencontres disponibles, puis réservez votre place en quelques clics.
        </p>
      </div>
      <router-link v-if="userStore.isOrganizer" to="/events/create" class="btn-primary h-12 px-6 rounded-xl whitespace-nowrap">Créer un événement</router-link>
    </div>

    <div v-if="registrationFeedback" class="mb-6 rounded-2xl border px-4 py-3 text-sm text-emerald-700"
      style="background:rgba(16,185,129,0.09); border-color:rgba(16,185,129,0.22)">
      {{ registrationFeedback }}
    </div>
    <div v-if="registrationError" class="mb-6 rounded-2xl border px-4 py-3 text-sm text-red-600"
      style="background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2)">
      {{ registrationError }}
    </div>

    <div class="glass p-4 mb-10">
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input v-model="q" placeholder="Rechercher un événement, une ville..." class="ef-input pl-11 h-12" />
        </div>
        <select v-model="sortBy" class="ef-input w-full sm:w-52 h-12">
          <option value="dateAsc">Les plus proches</option>
          <option value="dateDesc">Les plus lointains</option>
          <option value="title">Titre A → Z</option>
        </select>
      </div>
    </div>

    <div v-if="eventsStore.loading" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <div v-for="i in 6" :key="i" class="ef-card overflow-hidden animate-pulse">
        <div class="h-28" style="background:var(--border-light)"></div>
        <div class="p-5 space-y-3">
          <div class="h-4 w-2/3 rounded" style="background:var(--border)"></div>
          <div class="h-3 w-full rounded" style="background:var(--border-light)"></div>
          <div class="h-3 w-4/5 rounded" style="background:var(--border-light)"></div>
        </div>
      </div>
    </div>

    <div v-else-if="eventsStore.error" class="glass p-8 text-center">
      <p class="text-red-400 font-semibold mb-4">{{ eventsStore.error }}</p>
      <button @click="eventsStore.fetchEvents()" class="btn-outline text-xs">Réessayer</button>
    </div>

    <div v-else-if="filteredEvents.length === 0" class="text-center py-24">
      <div class="w-16 h-16 rounded-2xl border ef-border grid place-items-center mx-auto mb-6 text-3xl" style="background:var(--border-light)">🎟️</div>
      <h3 class="text-xl font-bold text-main mb-2">Aucun événement trouvé</h3>
      <p class="text-muted text-sm mb-6">{{ q ? 'Essayez une autre recherche.' : 'Aucun événement publié pour le moment.' }}</p>
      <div class="flex justify-center gap-3">
        <button v-if="q" @click="q = ''" class="btn-ghost text-xs">Effacer la recherche</button>
        <router-link v-if="userStore.isOrganizer" to="/events/create" class="btn-primary text-xs px-5 py-2.5">Créer le premier événement</router-link>
      </div>
    </div>

    <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      <article v-for="(event, i) in filteredEvents" :key="event.id" class="ef-card overflow-hidden group flex flex-col">
        <div class="relative h-28 flex items-end p-4"
          :style="`background: linear-gradient(135deg, ${accent(i).from}20, ${accent(i).to}08); border-bottom: 1px solid ${accent(i).from}18`">
          <div class="absolute top-0 left-0 right-0 h-0.5 rounded-t-2xl" :style="`background: linear-gradient(90deg, ${accent(i).from}, ${accent(i).to})`"></div>
          <div class="absolute top-4 right-4 text-right">
            <div class="text-4xl font-black leading-none" :style="`color: ${accent(i).from}`">{{ formatDate(event.eventDate).day }}</div>
            <div class="text-xs font-bold text-slate-400 tracking-widest">{{ formatDate(event.eventDate).month }}</div>
            <div class="text-xs text-slate-600">{{ formatDate(event.eventDate).year }}</div>
          </div>
          <span v-if="!event.isPublished" class="badge-orange text-xs">Brouillon</span>
        </div>

        <div class="p-5 flex flex-col flex-1">
          <router-link :to="`/events/${event.id}`" class="font-bold text-main text-lg leading-snug line-clamp-2 group-hover:text-orange-400 transition-colors mb-2">
            {{ event.title }}
          </router-link>
          <p class="text-sm text-sub line-clamp-2">{{ event.description || 'Aucune description.' }}</p>

          <div class="mt-5 space-y-2.5">
            <div class="flex items-center gap-2 text-xs text-muted">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              {{ formatTimeRange(event.eventDate, event.endDate) }}
            </div>
            <div class="flex items-center gap-2 text-xs text-muted">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              {{ event.location || 'Lieu à confirmer' }}
            </div>
            <div v-if="userStore.isAuthenticated" class="flex items-center gap-2 text-xs text-muted">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span>Organisé par <strong class="text-main">{{ organizerLabel(event.organizer) }}</strong></span>
            </div>
            <div class="flex items-center gap-2 text-xs text-muted">
              <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              <span><strong class="text-main">{{ event.participantsCount }}</strong> / {{ event.maxParticipants }} participants</span>
              <span v-if="event.isFull" class="badge-orange ml-auto">Complet</span>
            </div>
            <div class="text-xs font-semibold" :class="event.remainingPlaces === 0 ? 'text-red-500' : 'text-muted'">
              {{ event.remainingPlaces }} place{{ event.remainingPlaces > 1 ? 's' : '' }} restante{{ event.remainingPlaces > 1 ? 's' : '' }}
            </div>
          </div>

          <div class="mt-4 h-1 rounded-full overflow-hidden" style="background:var(--border)">
            <div class="h-full rounded-full transition-all duration-700"
              :style="`width: ${Math.min((event.participantsCount / event.maxParticipants) * 100, 100)}%; background: linear-gradient(90deg, ${accent(i).from}, ${accent(i).to})`"></div>
          </div>

          <div class="mt-5 pt-4 border-t ef-border flex items-center gap-2 mt-auto">
            <button v-if="userStore.isAuthenticated && eventsStore.isRegisteredToEvent(event.id)" class="btn-outline text-xs px-4 py-2 rounded-lg cursor-default" disabled>
              Inscrit ✓
            </button>
            <button v-else-if="userStore.isAuthenticated && !event.isFull"
              class="btn-primary text-xs px-4 py-2 rounded-lg"
              :style="`background: ${accent(i).from}`"
              :disabled="registeringEventId !== null"
              @click="handleRegister(event.id)">
              {{ registeringEventId === event.id ? 'Inscription...' : "S'inscrire" }}
            </button>
            <span v-else-if="event.isFull" class="text-xs text-muted italic">Complet</span>
            <router-link v-else to="/login" class="btn-ghost text-xs px-3 py-1.5">Se connecter</router-link>

            <div v-if="userStore.isOrganizer" class="flex items-center gap-1.5 ml-auto">
              <button @click="eventsStore.togglePublish(event.id)" class="text-xs px-2.5 py-1.5 rounded-lg border ef-border text-sub hover:text-main transition" style="border-color:var(--border)">
                {{ event.isPublished ? 'Dépublier' : 'Publier' }}
              </button>
              <button @click="handleDelete(event.id)" class="btn-danger text-xs px-2.5 py-1.5" title="Supprimer l'événement">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </div>
        </div>
      </article>
    </div>

    <div v-if="!eventsStore.loading && filteredEvents.length > 0" class="mt-10 text-center">
      <button @click="eventsStore.fetchEvents()" class="btn-ghost text-xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Actualiser les événements
      </button>
    </div>
  </section>
</template>