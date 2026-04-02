<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useEventsStore } from '../stores/events'
import { useAuthStore } from '../stores/auth'

const eventsStore = useEventsStore()
const userStore = useAuthStore()

const search = ref('')
const statusFilter = ref('all')
const organizerFilter = ref('all')
const timelineFilter = ref('all')
const sortBy = ref('dateAsc')
const localError = ref('')
const successMessage = ref('')
const editingId = ref(null)
const editOpen = ref(false)
const createOpen = ref(false)
const createErrors = ref({})

const editForm = reactive({
  title: '',
  description: '',
  eventDate: '',
  endDate: '',
  location: '',
  maxParticipants: 50,
  isPublished: false,
})

const createForm = reactive({
  title: '',
  description: '',
  eventDate: '',
  endDate: '',
  location: '',
  maxParticipants: 50,
  isPublished: true,
})

function resetMessages() {
  localError.value = ''
  successMessage.value = ''
}

async function loadEvents() {
  resetMessages()
  await eventsStore.fetchMyEvents()
}

onMounted(loadEvents)

const adminEvents = computed(() => [...eventsStore.events])

const organizerOptions = computed(() => {
  const map = new Map()

  adminEvents.value.forEach((event) => {
    const organizer = event.organizer
    if (!organizer?.id) return

    const fullName = [organizer.firstName, organizer.lastName].filter(Boolean).join(' ').trim() || `Organizer #${organizer.id}`
    map.set(String(organizer.id), fullName)
  })

  return [...map.entries()]
    .map(([id, name]) => ({ id, name }))
    .sort((a, b) => a.name.localeCompare(b.name))
})

const metrics = computed(() => {
  const now = new Date()
  const total = adminEvents.value.length
  const published = adminEvents.value.filter((event) => event.isPublished).length
  const drafts = total - published
  const full = adminEvents.value.filter((event) => event.isFull).length
  const upcoming = adminEvents.value.filter((event) => new Date(event.eventDate) >= now).length

  const seats = adminEvents.value.reduce((sum, event) => sum + Number(event.maxParticipants || 0), 0)
  const filledSeats = adminEvents.value.reduce((sum, event) => sum + Number(event.participantsCount || 0), 0)
  const fillRate = seats > 0 ? Math.round((filledSeats / seats) * 100) : 0

  return { total, published, drafts, full, upcoming, seats, filledSeats, fillRate }
})

const filteredEvents = computed(() => {
  const now = new Date()
  const query = search.value.trim().toLowerCase()
  let list = [...adminEvents.value]

  if (query) {
    list = list.filter((event) => {
      const organizerName = [event.organizer?.firstName, event.organizer?.lastName].filter(Boolean).join(' ')
      const haystack = [
        event.title,
        event.description,
        event.location,
        organizerName,
      ]
        .filter(Boolean)
        .join(' ')
        .toLowerCase()

      return haystack.includes(query)
    })
  }

  if (statusFilter.value === 'published') {
    list = list.filter((event) => event.isPublished)
  } else if (statusFilter.value === 'draft') {
    list = list.filter((event) => !event.isPublished)
  } else if (statusFilter.value === 'full') {
    list = list.filter((event) => event.isFull)
  }

  if (organizerFilter.value !== 'all') {
    list = list.filter((event) => String(event.organizer?.id) === organizerFilter.value)
  }

  if (timelineFilter.value === 'upcoming') {
    list = list.filter((event) => new Date(event.eventDate) >= now)
  } else if (timelineFilter.value === 'past') {
    list = list.filter((event) => new Date(event.eventDate) < now)
  }

  if (sortBy.value === 'dateAsc') {
    list.sort((a, b) => new Date(a.eventDate || 0) - new Date(b.eventDate || 0))
  } else if (sortBy.value === 'dateDesc') {
    list.sort((a, b) => new Date(b.eventDate || 0) - new Date(a.eventDate || 0))
  } else if (sortBy.value === 'capacityDesc') {
    list.sort((a, b) => (b.participantsCount || 0) - (a.participantsCount || 0))
  } else if (sortBy.value === 'title') {
    list.sort((a, b) => (a.title || '').localeCompare(b.title || ''))
  }

  return list
})

const spotlightEvent = computed(() => {
  return filteredEvents.value
    .filter((event) => event.isPublished)
    .sort((a, b) => {
      const ratioA = capacityRatio(a)
      const ratioB = capacityRatio(b)
      return ratioB - ratioA
    })[0] || null
})

function capacityRatio(event) {
  const max = Number(event.maxParticipants || 0)
  if (!max) return 0
  return Math.min(Math.round((Number(event.participantsCount || 0) / max) * 100), 100)
}

function formatDate(value, options = { dateStyle: 'medium', timeStyle: 'short' }) {
  if (!value) return 'Date inconnue'
  return new Intl.DateTimeFormat('fr-FR', options).format(new Date(value))
}

function formatDateRange(start, end) {
  if (!start) return 'Date inconnue'
  if (!end) return formatDate(start)

  const startDate = new Date(start)
  const endDate = new Date(end)
  const sameDay = startDate.toDateString() === endDate.toDateString()

  if (sameDay) {
    return `${formatDate(start)} - ${new Intl.DateTimeFormat('fr-FR', { timeStyle: 'short' }).format(endDate)}`
  }

  return `${formatDate(start)} - ${formatDate(end)}`
}

function toDatetimeLocal(value) {
  if (!value) return ''
  const date = new Date(value)
  const pad = (part) => String(part).padStart(2, '0')
  return [
    date.getFullYear(),
    pad(date.getMonth() + 1),
    pad(date.getDate()),
  ].join('-') + `T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

function organizerName(event) {
  return [event.organizer?.firstName, event.organizer?.lastName].filter(Boolean).join(' ').trim() || 'Organizer inconnu'
}

function isPast(event) {
  return new Date(event.eventDate) < new Date()
}

function openEditor(event) {
  resetMessages()
  editingId.value = event.id
  editForm.title = event.title || ''
  editForm.description = event.description || ''
  editForm.eventDate = toDatetimeLocal(event.eventDate)
  editForm.endDate = toDatetimeLocal(event.endDate)
  editForm.location = event.location || ''
  editForm.maxParticipants = Number(event.maxParticipants || 50)
  editForm.isPublished = !!event.isPublished
  editOpen.value = true
}

function closeEditor() {
  editOpen.value = false
  editingId.value = null
}

function resetCreateForm() {
  createForm.title = ''
  createForm.description = ''
  createForm.eventDate = ''
  createForm.endDate = ''
  createForm.location = ''
  createForm.maxParticipants = 50
  createForm.isPublished = true
  createErrors.value = {}
}

async function submitCreate() {
  resetMessages()
  createErrors.value = {}

  try {
    await eventsStore.createEvent({
      ...createForm,
      maxParticipants: Number(createForm.maxParticipants),
      eventDate: createForm.eventDate ? new Date(createForm.eventDate).toISOString() : '',
      endDate: createForm.endDate ? new Date(createForm.endDate).toISOString() : '',
    })

    successMessage.value = 'Nouvel evenement cree avec succes.'
    createOpen.value = false
    resetCreateForm()
    await loadEvents()
  } catch (error) {
    const payload = error?.response?.data
    if (payload?.errors) {
      createErrors.value = payload.errors
    }
    localError.value = payload?.message || (payload?.errors ? 'Veuillez corriger le formulaire.' : 'Impossible de creer cet evenement.')
  }
}

async function submitEdition() {
  if (!editingId.value) return

  resetMessages()

  try {
    await eventsStore.updateEvent(editingId.value, {
      ...editForm,
      maxParticipants: Number(editForm.maxParticipants),
      eventDate: editForm.eventDate ? new Date(editForm.eventDate).toISOString() : '',
      endDate: editForm.endDate ? new Date(editForm.endDate).toISOString() : '',
    })

    successMessage.value = 'Evenement mis a jour avec succes.'
    closeEditor()
  } catch (error) {
    const payload = error?.response?.data
    localError.value = payload?.message || (payload?.errors ? JSON.stringify(payload.errors) : 'Impossible de mettre a jour cet evenement.')
  }
}

async function togglePublish(event) {
  resetMessages()
  const wasPublished = !!event.isPublished

  try {
    await eventsStore.togglePublish(event.id)
    successMessage.value = wasPublished ? 'Evenement depublie.' : 'Evenement publie.'
  } catch (error) {
    localError.value = error?.response?.data?.message || 'Impossible de changer le statut de publication.'
  }
}

async function deleteEvent(event) {
  const ok = window.confirm(`Supprimer "${event.title}" ? Cette action est irreversible.`)
  if (!ok) return

  resetMessages()

  try {
    await eventsStore.deleteEvent(event.id)
    successMessage.value = 'Evenement supprime.'
  } catch (error) {
    localError.value = error?.response?.data?.message || 'Impossible de supprimer cet evenement.'
  }
}
</script>

<template>
  <section class="space-y-8">
    <div class="relative overflow-hidden rounded-[2rem] border p-8 md:p-10"
      style="background:linear-gradient(135deg, rgba(15,23,42,0.96), rgba(15,23,42,0.84) 45%, rgba(249,115,22,0.18)); border-color:rgba(249,115,22,0.18)">
      <div class="absolute -top-24 right-0 h-64 w-64 rounded-full blur-3xl" style="background:rgba(249,115,22,0.18)"></div>
      <div class="absolute bottom-0 left-0 h-48 w-48 rounded-full blur-3xl" style="background:rgba(59,130,246,0.12)"></div>

      <div class="relative grid gap-8 xl:grid-cols-[1.15fr_0.85fr] xl:items-end">
        <div class="space-y-5">
          <div class="section-label">Administration</div>
          <div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-white leading-tight">
              Centre de pilotage EventFlow
            </h1>
            <p class="mt-4 max-w-2xl text-sm md:text-base leading-7 text-slate-200/85">
              Supervisez les evenements, priorisez les publications, corrigez les fiches organisateur et gardez un
              oeil sur l'occupation globale de la plateforme dans un espace unique reserve aux administrateurs.
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-3 text-xs">
            <span class="badge-orange">Role actif: administrateur</span>
            <span class="badge-blue">Utilisateur: {{ userStore.fullName || 'Admin' }}</span>
            <span class="badge-green">Controle global des evenements</span>
          </div>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-3xl border p-5 text-white" style="background:rgba(255,255,255,0.07); border-color:rgba(255,255,255,0.1)">
            <div class="text-xs uppercase tracking-[0.22em] text-slate-300">Evenements a venir</div>
            <div class="mt-3 text-4xl font-black">{{ metrics.upcoming }}</div>
            <p class="mt-2 text-sm text-slate-300">Sur {{ metrics.total }} evenements administres.</p>
          </div>

          <div class="rounded-3xl border p-5 text-white" style="background:rgba(255,255,255,0.07); border-color:rgba(255,255,255,0.1)">
            <div class="text-xs uppercase tracking-[0.22em] text-slate-300">Taux de remplissage</div>
            <div class="mt-3 text-4xl font-black">{{ metrics.fillRate }}%</div>
            <p class="mt-2 text-sm text-slate-300">{{ metrics.filledSeats }} places occupees sur {{ metrics.seats || 0 }}.</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="localError || eventsStore.error || successMessage" class="grid gap-3">
      <div v-if="successMessage" class="rounded-2xl border px-4 py-3 text-sm text-emerald-600"
        style="background:rgba(16,185,129,0.09); border-color:rgba(16,185,129,0.22)">
        {{ successMessage }}
      </div>
      <div v-if="localError || eventsStore.error" class="rounded-2xl border px-4 py-3 text-sm text-red-500"
        style="background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2)">
        {{ localError || eventsStore.error }}
      </div>
    </div>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.22em] text-muted">Total</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <strong class="text-4xl font-black text-main">{{ metrics.total }}</strong>
          <span class="badge-blue">catalogue</span>
        </div>
      </article>

      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.22em] text-muted">Publies</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <strong class="text-4xl font-black text-main">{{ metrics.published }}</strong>
          <span class="badge-green">visibles</span>
        </div>
      </article>

      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.22em] text-muted">Brouillons</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <strong class="text-4xl font-black text-main">{{ metrics.drafts }}</strong>
          <span class="badge-orange">a arbitrer</span>
        </div>
      </article>

      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.22em] text-muted">Complets</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <strong class="text-4xl font-black text-main">{{ metrics.full }}</strong>
          <span class="badge-purple">satures</span>
        </div>
      </article>

      <article class="ef-card p-5">
        <div class="text-xs uppercase tracking-[0.22em] text-muted">Places</div>
        <div class="mt-3 flex items-end justify-between gap-3">
          <strong class="text-4xl font-black text-main">{{ metrics.filledSeats }}</strong>
          <span class="text-xs font-semibold text-sub">/{{ metrics.seats || 0 }}</span>
        </div>
      </article>
    </div>

    <section class="glass p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-2xl">
          <div class="section-label mb-2">Creation</div>
          <h2 class="text-2xl font-extrabold text-main">Creer un evenement depuis l'administration</h2>
          <p class="mt-3 text-sm leading-7 text-sub">
            L'administrateur peut publier directement un nouvel evenement, preparer un brouillon pour un organisateur
            ou lancer rapidement une nouvelle session depuis ce tableau de bord.
          </p>
        </div>

        <div class="flex flex-wrap gap-3">
          <button class="btn-primary" @click="createOpen = !createOpen">
            {{ createOpen ? 'Masquer le formulaire' : 'Creer un evenement' }}
          </button>
          <router-link to="/events/create" class="btn-outline">Version plein ecran</router-link>
        </div>
      </div>

      <div v-if="createOpen" class="mt-6 grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
          <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Titre</label>
          <input v-model="createForm.title" class="ef-input" placeholder="Ex: Sommet IA & Product 2026" />
          <p v-if="createErrors.title" class="mt-2 text-xs text-red-500">{{ createErrors.title }}</p>
        </div>

        <div class="md:col-span-2">
          <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Description</label>
          <textarea v-model="createForm.description" rows="4" class="ef-input resize-none"
            placeholder="Objectifs, public vise, programme, intervenants..."></textarea>
          <p v-if="createErrors.description" class="mt-2 text-xs text-red-500">{{ createErrors.description }}</p>
        </div>

        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Debut</label>
          <input v-model="createForm.eventDate" type="datetime-local" class="ef-input" />
          <p v-if="createErrors.eventDate" class="mt-2 text-xs text-red-500">{{ createErrors.eventDate }}</p>
        </div>

        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Fin</label>
          <input v-model="createForm.endDate" type="datetime-local" class="ef-input" />
          <p v-if="createErrors.endDate" class="mt-2 text-xs text-red-500">{{ createErrors.endDate }}</p>
        </div>

        <div>
          <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Capacite</label>
          <input v-model="createForm.maxParticipants" type="number" min="1" class="ef-input" />
          <p v-if="createErrors.maxParticipants" class="mt-2 text-xs text-red-500">{{ createErrors.maxParticipants }}</p>
        </div>

        <div class="md:col-span-2">
          <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Lieu</label>
          <input v-model="createForm.location" class="ef-input" placeholder="Paris, Station F - Main Stage" />
          <p v-if="createErrors.location" class="mt-2 text-xs text-red-500">{{ createErrors.location }}</p>
        </div>

        <label class="md:col-span-2 flex items-center gap-3 rounded-2xl border p-4 text-sm text-sub"
          style="border-color:var(--border); background:var(--border-light)">
          <input v-model="createForm.isPublished" type="checkbox" />
          Publier l'evenement des sa creation pour le rendre visible sur la plateforme.
        </label>

        <div class="md:col-span-2 flex flex-wrap justify-end gap-3">
          <button class="btn-ghost" @click="resetCreateForm()">Reinitialiser</button>
          <button class="btn-outline" @click="createOpen = false">Fermer</button>
          <button class="btn-primary" @click="submitCreate()">Enregistrer l'evenement</button>
        </div>
      </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
      <section class="glass p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <div class="section-label mb-2">Filtrage</div>
            <h2 class="text-2xl font-extrabold text-main">Moderation des fiches evenement</h2>
          </div>

          <div class="flex flex-wrap gap-3">
            <button class="btn-ghost" @click="loadEvents()">Actualiser</button>
            <router-link to="/events/create" class="btn-primary">Creer un evenement</router-link>
          </div>
        </div>

        <div class="mt-6 grid gap-3 lg:grid-cols-2 xl:grid-cols-5">
          <input v-model="search" class="ef-input xl:col-span-2" placeholder="Titre, lieu, organisateur..." />

          <select v-model="statusFilter" class="ef-input">
            <option value="all">Tous les statuts</option>
            <option value="published">Publies</option>
            <option value="draft">Brouillons</option>
            <option value="full">Complets</option>
          </select>

          <select v-model="timelineFilter" class="ef-input">
            <option value="all">Toutes les dates</option>
            <option value="upcoming">A venir</option>
            <option value="past">Passes</option>
          </select>

          <select v-model="organizerFilter" class="ef-input">
            <option value="all">Tous les organisateurs</option>
            <option v-for="option in organizerOptions" :key="option.id" :value="option.id">
              {{ option.name }}
            </option>
          </select>
        </div>

        <div class="mt-3 grid gap-3 lg:grid-cols-[1fr_220px]">
          <div class="rounded-2xl border px-4 py-3 text-sm text-sub"
            style="background:var(--border-light); border-color:var(--border)">
            {{ filteredEvents.length }} resultat(s) affiches sur {{ metrics.total }} evenement(s). Les administrateurs peuvent publier,
            modifier et supprimer n'importe quelle fiche.
          </div>

          <select v-model="sortBy" class="ef-input">
            <option value="dateAsc">Date croissante</option>
            <option value="dateDesc">Date decroissante</option>
            <option value="capacityDesc">Occupation</option>
            <option value="title">Titre A-Z</option>
          </select>
        </div>
      </section>

      <aside class="glass p-6">
        <div class="section-label mb-2">Priorite</div>
        <template v-if="spotlightEvent">
          <h2 class="text-2xl font-extrabold text-main">{{ spotlightEvent.title }}</h2>
          <p class="mt-2 text-sm leading-6 text-sub">
            Cet evenement est actuellement la fiche publiee la plus proche de la saturation. C'est le bon candidat a surveiller
            pour anticiper la capacite ou ouvrir une nouvelle session.
          </p>

          <div class="mt-5 flex flex-wrap gap-2">
            <span class="badge-green" v-if="spotlightEvent.isPublished">publie</span>
            <span class="badge-orange" v-else>brouillon</span>
            <span class="badge-purple" v-if="spotlightEvent.isFull">complet</span>
            <span class="badge-blue">{{ organizerName(spotlightEvent) }}</span>
          </div>

          <div class="mt-5 space-y-3 rounded-3xl border p-5" style="border-color:var(--border); background:var(--bg-card)">
            <div class="flex items-center justify-between text-sm">
              <span class="text-muted">Horaires</span>
              <strong class="text-main text-right">{{ formatDateRange(spotlightEvent.eventDate, spotlightEvent.endDate) }}</strong>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-muted">Lieu</span>
              <strong class="text-main text-right">{{ spotlightEvent.location }}</strong>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="text-muted">Remplissage</span>
              <strong class="text-main">{{ spotlightEvent.participantsCount }}/{{ spotlightEvent.maxParticipants }}</strong>
            </div>
            <div class="h-2 overflow-hidden rounded-full" style="background:var(--border)">
              <div class="h-full rounded-full"
                :style="`width:${capacityRatio(spotlightEvent)}%; background:linear-gradient(90deg, #f97316, #fbbf24)`"></div>
            </div>
          </div>

          <div class="mt-5 flex flex-wrap gap-3">
            <button class="btn-outline" @click="openEditor(spotlightEvent)">Editer</button>
            <button class="btn-primary" @click="togglePublish(spotlightEvent)">
              {{ spotlightEvent.isPublished ? 'Depublier' : 'Publier' }}
            </button>
          </div>
        </template>

        <template v-else>
          <h2 class="text-2xl font-extrabold text-main">Aucune alerte prioritaire</h2>
          <p class="mt-2 text-sm leading-6 text-sub">
            Publiez davantage d'evenements ou assouplissez vos filtres pour faire remonter une fiche a surveiller.
          </p>
        </template>
      </aside>
    </div>

    <div v-if="eventsStore.loading" class="grid gap-4 lg:grid-cols-2">
      <div v-for="index in 4" :key="index" class="ef-card animate-pulse p-6">
        <div class="h-5 w-32 rounded" style="background:var(--border)"></div>
        <div class="mt-4 h-4 w-2/3 rounded" style="background:var(--border-light)"></div>
        <div class="mt-2 h-4 w-full rounded" style="background:var(--border-light)"></div>
        <div class="mt-6 h-10 rounded-2xl" style="background:var(--border-light)"></div>
      </div>
    </div>

    <div v-else-if="filteredEvents.length === 0" class="glass p-12 text-center">
      <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl border text-2xl"
        style="border-color:var(--border); background:var(--border-light)">
        A
      </div>
      <h2 class="mt-5 text-2xl font-extrabold text-main">Aucun evenement a afficher</h2>
      <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-sub">
        Ajustez les filtres, rechargez la liste ou creez un nouvel evenement pour alimenter le tableau d'administration.
      </p>
      <div class="mt-6 flex flex-wrap justify-center gap-3">
        <button class="btn-ghost" @click="search = ''; statusFilter = 'all'; organizerFilter = 'all'; timelineFilter = 'all'">Reinitialiser</button>
        <router-link to="/events/create" class="btn-primary">Creer un evenement</router-link>
      </div>
    </div>

    <div v-else class="grid gap-5 lg:grid-cols-2">
      <article v-for="event in filteredEvents" :key="event.id" class="ef-card overflow-hidden">
        <div class="border-b p-5" style="border-color:var(--border); background:linear-gradient(180deg, rgba(249,115,22,0.09), transparent)">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="space-y-3">
              <div class="flex flex-wrap gap-2">
                <span class="badge-green" v-if="event.isPublished">publie</span>
                <span class="badge-orange" v-else>brouillon</span>
                <span class="badge-purple" v-if="event.isFull">complet</span>
                <span class="badge-blue" v-if="isPast(event)">archive</span>
              </div>

              <div>
                <h3 class="text-xl font-extrabold text-main">{{ event.title }}</h3>
                <p class="mt-2 text-sm text-sub">
                  {{ organizerName(event) }} · {{ formatDateRange(event.eventDate, event.endDate) }}
                </p>
              </div>
            </div>

            <div class="rounded-2xl border px-3 py-2 text-right" style="border-color:var(--border); background:var(--bg-card)">
              <div class="text-xs uppercase tracking-[0.18em] text-muted">Occupation</div>
              <div class="mt-1 text-2xl font-black text-main">{{ capacityRatio(event) }}%</div>
            </div>
          </div>
        </div>

        <div class="p-5">
          <p class="line-clamp-3 text-sm leading-6 text-sub">{{ event.description || 'Aucune description fournie.' }}</p>

          <div class="mt-5 grid gap-3 md:grid-cols-2">
            <div class="rounded-2xl border p-4" style="border-color:var(--border); background:var(--border-light)">
              <div class="text-xs uppercase tracking-[0.18em] text-muted">Lieu</div>
              <div class="mt-2 text-sm font-semibold text-main">{{ event.location }}</div>
            </div>

            <div class="rounded-2xl border p-4" style="border-color:var(--border); background:var(--border-light)">
              <div class="text-xs uppercase tracking-[0.18em] text-muted">Fin</div>
              <div class="mt-2 text-sm font-semibold text-main">{{ event.endDate ? formatDate(event.endDate) : 'Non renseignee' }}</div>
            </div>

            <div class="rounded-2xl border p-4 md:col-span-2" style="border-color:var(--border); background:var(--border-light)">
              <div class="text-xs uppercase tracking-[0.18em] text-muted">Capacite</div>
              <div class="mt-2 text-sm font-semibold text-main">{{ event.participantsCount }} / {{ event.maxParticipants }} inscrits</div>
              <div class="mt-1 text-xs font-semibold" :class="event.remainingPlaces === 0 ? 'text-red-500' : 'text-sub'">
                {{ event.remainingPlaces }} place{{ event.remainingPlaces > 1 ? 's' : '' }} restante{{ event.remainingPlaces > 1 ? 's' : '' }}
              </div>
            </div>
          </div>

          <div class="mt-5">
            <div class="mb-2 flex items-center justify-between text-xs text-muted">
              <span>Progression d'occupation</span>
              <span>{{ capacityRatio(event) }}%</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full" style="background:var(--border)">
              <div class="h-full rounded-full"
                :style="`width:${capacityRatio(event)}%; background:linear-gradient(90deg, #0ea5e9, #f97316)`"></div>
            </div>
          </div>

          <div class="mt-6 flex flex-wrap gap-3">
            <button class="btn-outline" @click="openEditor(event)">Editer</button>
            <button class="btn-primary" @click="togglePublish(event)">
              {{ event.isPublished ? 'Depublier' : 'Publier' }}
            </button>
            <button class="btn-danger" @click="deleteEvent(event)">Supprimer</button>
          </div>
        </div>
      </article>
    </div>

    <section class="glass p-6">
      <div class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
        <div>
          <div class="section-label mb-2">Conformite</div>
          <h2 class="text-2xl font-extrabold text-main">Rappels gouvernance et RGPD</h2>
          <p class="mt-3 text-sm leading-7 text-sub">
            Le compte administrateur garde une vision transverse de la publication des evenements, mais la plateforme reste
            conforme a l'approche RGPD du projet: chaque utilisateur dispose toujours de son espace "Mes donnees" pour consulter,
            rectifier, exporter ou anonymiser son compte.
          </p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
        <router-link to="/profile" class="rounded-3xl border p-4 transition hover:-translate-y-0.5"
            style="border-color:var(--border); background:var(--bg-card)">
            <div class="text-xs uppercase tracking-[0.18em] text-muted">Donnees perso</div>
            <div class="mt-2 text-base font-bold text-main">Verifier mon profil admin</div>
          </router-link>

          <router-link to="/privacy" class="rounded-3xl border p-4 transition hover:-translate-y-0.5"
            style="border-color:var(--border); background:var(--bg-card)">
            <div class="text-xs uppercase tracking-[0.18em] text-muted">Politique</div>
            <div class="mt-2 text-base font-bold text-main">Relire la politique de confidentialite</div>
          </router-link>
        </div>
      </div>
    </section>

    <div v-if="editOpen" class="fixed inset-0 z-40 flex items-center justify-center bg-slate-950/50 px-4 py-8 backdrop-blur-sm">
      <div class="w-full max-w-3xl rounded-[1.75rem] border p-6 md:p-8"
        style="background:var(--bg-card); border-color:var(--border); box-shadow:var(--shadow-card-hover)">
        <div class="flex items-start justify-between gap-4">
          <div>
            <div class="section-label mb-2">Edition</div>
            <h2 class="text-2xl font-extrabold text-main">Mettre a jour la fiche evenement</h2>
            <p class="mt-2 text-sm text-sub">Les modifications s'appliquent directement a la fiche selectionnee.</p>
          </div>

          <button class="btn-ghost" @click="closeEditor()">Fermer</button>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2">
          <div class="md:col-span-2">
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Titre</label>
            <input v-model="editForm.title" class="ef-input" />
          </div>

          <div class="md:col-span-2">
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Description</label>
            <textarea v-model="editForm.description" rows="5" class="ef-input resize-none"></textarea>
          </div>

          <div>
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Debut</label>
            <input v-model="editForm.eventDate" type="datetime-local" class="ef-input" />
          </div>

          <div>
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Fin</label>
            <input v-model="editForm.endDate" type="datetime-local" class="ef-input" />
          </div>

          <div>
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Capacite</label>
            <input v-model="editForm.maxParticipants" type="number" min="1" class="ef-input" />
          </div>

          <div class="md:col-span-2">
            <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-muted">Lieu</label>
            <input v-model="editForm.location" class="ef-input" />
          </div>
        </div>

        <label class="mt-5 flex items-center gap-3 rounded-2xl border p-4 text-sm text-sub"
          style="border-color:var(--border); background:var(--border-light)">
          <input v-model="editForm.isPublished" type="checkbox" />
          Publier la fiche une fois les modifications enregistrees.
        </label>

        <div class="mt-6 flex flex-wrap justify-end gap-3">
          <button class="btn-outline" @click="closeEditor()">Annuler</button>
          <button class="btn-primary" @click="submitEdition()">Enregistrer</button>
        </div>
      </div>
    </div>
  </section>
</template>
