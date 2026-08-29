<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useEventsStore } from '../stores/events'

const authStore = useAuthStore()
const eventsStore = useEventsStore()
const feedback = ref('')
const registrationSuccess = ref('')
const selectedParticipantsEventId = ref(null)
const participantsLoading = ref(false)
const participantsError = ref('')

function consumeRegistrationFlash() {
  try {
    const message = sessionStorage.getItem('eventflow-registration-success')
    if (!message) return
    registrationSuccess.value = message
    sessionStorage.removeItem('eventflow-registration-success')
  } catch {
    // Ignore storage issues and simply skip the flash message.
  }
}

onMounted(async () => {
  consumeRegistrationFlash()

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

const roleLabel = computed(() => {
  if (authStore.isAdmin) return 'Administrateur'
  if (authStore.isOrganizer) return 'Organisateur'
  return 'Participant'
})

function formatDate(value) {
  if (!value) return 'Date à confirmer'
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

async function cancelRegistration(registrationId) {
  feedback.value = ''

  try {
    await eventsStore.cancelRegistration(registrationId)
    feedback.value = 'Inscription annulée avec succès.'
  } catch (err) {
    feedback.value = err?.response?.data?.message || "Impossible d'annuler cette inscription."
  }
}

async function toggleParticipants(eventId) {
  participantsError.value = ''

  if (selectedParticipantsEventId.value === eventId) {
    selectedParticipantsEventId.value = null
    return
  }

  selectedParticipantsEventId.value = eventId
  participantsLoading.value = true

  try {
    await eventsStore.fetchEventParticipants(eventId)
  } catch (err) {
    participantsError.value = err?.response?.data?.message || 'Impossible de charger les participants.'
  } finally {
    participantsLoading.value = false
  }
}

function participantsFor(eventId) {
  return eventsStore.participantsByEvent[eventId] || []
}
</script>

<template>
  <section class="space-y-8">
    <div class="relative overflow-hidden rounded-[2rem] border p-8 md:p-10"
      style="border-color:var(--border);background:linear-gradient(135deg, rgba(249,115,22,0.08), var(--bg-card))">
      <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-orange-500/10 blur-3xl"></div>
      <div class="relative flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
        <div>
          <div class="section-label mb-3">Mon espace</div>
          <h1 class="text-3xl md:text-4xl font-extrabold text-main">Bonjour {{ authStore.user?.firstName || 'utilisateur' }}</h1>
          <p class="mt-3 max-w-2xl text-base leading-7 text-sub">
            {{ authStore.isOrganizer
              ? 'Gérez vos événements, suivez vos participants et retrouvez aussi vos propres inscriptions.'
              : 'Retrouvez vos inscriptions, votre prochain événement et les informations de votre compte.' }}
          </p>
        </div>
        <router-link v-if="authStore.isOrganizer" to="/events/create" class="btn-primary shrink-0">Créer un événement</router-link>
      </div>
    </div>

    <div v-if="registrationSuccess" class="rounded-2xl border px-4 py-3 text-sm text-emerald-700"
      style="background:rgba(16,185,129,0.09); border-color:rgba(16,185,129,0.22)">
      {{ registrationSuccess }}
    </div>

    <div class="grid gap-4 md:grid-cols-3">
      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.2em] text-muted">Mon profil</div>
        <div class="mt-3 text-2xl font-black text-main">{{ roleLabel }}</div>
      </article>
      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.2em] text-muted">Mes inscriptions</div>
        <div class="mt-3 text-2xl font-black text-main">{{ eventsStore.myRegistrations.length }}</div>
      </article>
      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.2em] text-muted">Mes événements créés</div>
        <div class="mt-3 text-2xl font-black text-main">{{ authStore.isOrganizer ? eventsStore.events.length : 0 }}</div>
      </article>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
      <section class="glass p-6">
        <div class="section-label mb-2">À venir</div>
        <template v-if="nextRegistration">
          <h2 class="text-2xl font-extrabold text-main">{{ nextRegistration.event.title }}</h2>
          <p class="mt-3 text-sm leading-6 text-sub">
            Votre prochaine participation est prévue le {{ formatDate(nextRegistration.event.eventDate) }} à {{ nextRegistration.event.location }}.
          </p>
          <router-link :to="`/events/${nextRegistration.event.id}`" class="btn-primary mt-5">Voir l'événement</router-link>
        </template>
        <template v-else>
          <h2 class="text-2xl font-extrabold text-main">Aucun événement réservé pour le moment</h2>
          <p class="mt-3 text-sm leading-6 text-sub">Explorez les événements disponibles et trouvez votre prochain rendez-vous.</p>
          <router-link to="/events" class="btn-primary mt-5">Découvrir les événements</router-link>
        </template>
      </section>

      <section class="glass p-6">
        <div class="section-label mb-2">Accès rapides</div>
        <div class="grid gap-3">
          <router-link to="/profile" class="rounded-2xl border p-4 transition hover:-translate-y-0.5" style="border-color:var(--border); background:var(--bg-card)">
            <div class="font-bold text-main">Mes données</div>
            <div class="mt-1 text-sm text-sub">Consulter, modifier ou exporter les informations de mon compte.</div>
          </router-link>
          <router-link v-if="authStore.isOrganizer" to="/events/create" class="rounded-2xl border p-4 transition hover:-translate-y-0.5" style="border-color:var(--border); background:var(--bg-card)">
            <div class="font-bold text-main">Créer un événement</div>
            <div class="mt-1 text-sm text-sub">Préparer un nouvel événement et choisir quand le publier.</div>
          </router-link>
          <router-link v-if="authStore.isAdmin" to="/admin" class="rounded-2xl border p-4 transition hover:-translate-y-0.5" style="border-color:var(--border); background:var(--bg-card)">
            <div class="font-bold text-main">Administration</div>
            <div class="mt-1 text-sm text-sub">Gérer l'ensemble des événements de la plateforme.</div>
          </router-link>
        </div>
      </section>
    </div>

    <section v-if="authStore.isOrganizer" class="glass p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <div class="section-label mb-2">Organisation</div>
          <h2 class="text-2xl font-extrabold text-main">Mes événements organisés</h2>
          <p class="mt-2 text-sm text-sub">Suivez les inscriptions et consultez les participants de chaque événement.</p>
        </div>
        <router-link to="/events/create" class="btn-primary">Créer un événement</router-link>
      </div>

      <div v-if="eventsStore.events.length === 0" class="mt-5 rounded-2xl border p-6 text-sm text-sub"
        style="border-color:var(--border); background:var(--bg-card)">
        Aucun événement organisé pour le moment.
      </div>

      <div v-else class="mt-5 grid gap-4">
        <article v-for="event in eventsStore.events" :key="event.id" class="rounded-2xl border p-5"
          style="border-color:var(--border); background:var(--bg-card)">
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <h3 class="text-xl font-extrabold text-main">{{ event.title }}</h3>
              <p class="mt-1 text-sm text-sub">{{ formatDate(event.eventDate) }}</p>
              <p class="mt-2 text-xs font-semibold text-sub">
                {{ event.participantsCount }} participant{{ event.participantsCount > 1 ? 's' : '' }} / {{ event.maxParticipants }} places
              </p>
            </div>
            <button class="btn-outline" @click="toggleParticipants(event.id)">
              {{ selectedParticipantsEventId === event.id ? 'Masquer les participants' : `Voir les participants (${event.participantsCount})` }}
            </button>
          </div>

          <div v-if="selectedParticipantsEventId === event.id" class="mt-5 border-t pt-5 ef-border">
            <h4 class="font-bold text-main">Participants inscrits — {{ event.title }}</h4>

            <p v-if="participantsLoading" class="mt-4 text-sm text-sub">Chargement des participants...</p>
            <p v-else-if="participantsError" class="mt-4 text-sm text-red-600">{{ participantsError }}</p>
            <p v-else-if="participantsFor(event.id).length === 0" class="mt-4 text-sm text-sub">Aucun participant inscrit pour le moment.</p>
            <div v-else class="mt-4 grid gap-3 sm:grid-cols-2">
              <div v-for="participant in participantsFor(event.id)" :key="participant.registrationId"
                class="rounded-xl border p-4" style="border-color:var(--border); background:var(--bg-base)">
                <div class="font-bold text-main">{{ participant.firstName }} {{ participant.lastName }}</div>
                <div class="mt-1 text-sm text-sub">{{ participant.email }}</div>
                <div class="mt-2 text-xs text-muted">Inscription le {{ formatDate(participant.registeredAt) }}</div>
                <span class="badge-green mt-3">Confirmée</span>
              </div>
            </div>
          </div>
        </article>
      </div>
    </section>

    <section class="glass p-6">
      <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
          <div class="section-label mb-2">Mes réservations</div>
          <h2 class="text-2xl font-extrabold text-main">Mes inscriptions</h2>
        </div>
        <button class="btn-ghost" @click="eventsStore.fetchMyRegistrations()">Actualiser</button>
      </div>

      <div v-if="feedback" class="mt-4 rounded-xl border px-4 py-3 text-sm"
        :style="feedback.includes('succès') ? 'background:rgba(16,185,129,0.08); border-color:rgba(16,185,129,0.2); color:#059669' : 'background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2); color:#dc2626'">
        {{ feedback }}
      </div>

      <div v-if="eventsStore.myRegistrations.length === 0" class="mt-5 rounded-2xl border p-6 text-sm text-sub"
        style="border-color:var(--border); background:var(--bg-card)">
        Aucune inscription à afficher pour le moment.
      </div>

      <div v-else class="mt-5 grid gap-4">
        <article v-for="registration in eventsStore.myRegistrations" :key="registration.id" class="rounded-2xl border p-5"
          style="border-color:var(--border); background:var(--bg-card)">
          <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <span class="badge-green" v-if="registration.status === 'confirmed'">Confirmée</span>
                <span class="badge-orange" v-else-if="registration.status === 'cancelled'">Annulée</span>
                <span class="badge-blue" v-else>En attente</span>
              </div>
              <h3 class="mt-3 text-xl font-extrabold text-main">{{ registration.event?.title }}</h3>
              <p class="mt-1 text-sm text-sub">{{ formatDate(registration.event?.eventDate) }} · {{ registration.event?.location }}</p>
              <p class="mt-2 text-xs font-semibold text-sub">
                {{ registration.event?.remainingPlaces }} place{{ registration.event?.remainingPlaces > 1 ? 's' : '' }} restante{{ registration.event?.remainingPlaces > 1 ? 's' : '' }}
              </p>
            </div>

            <div class="flex flex-wrap gap-3">
              <router-link :to="`/events/${registration.event?.id}`" class="btn-outline">Voir le détail</router-link>
              <button v-if="registration.status !== 'cancelled'" class="btn-danger" @click="cancelRegistration(registration.id)">Annuler mon inscription</button>
            </div>
          </div>
        </article>
      </div>
    </section>
  </section>
</template>