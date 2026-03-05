<script setup>
import { computed, onMounted, ref } from "vue"
import { useEventsStore } from "../stores/events"
import { useUserStore } from "../stores/user"

const eventsStore = useEventsStore()
const userStore = useUserStore()

const q = ref("")
const onlyFuture = ref(false)
const sortBy = ref("dateAsc") // dateAsc | dateDesc | title

onMounted(() => {
  eventsStore.fetchEvents()
})

function formatDate(iso) {
  if (!iso) return ""
  const d = new Date(iso)
  return d.toLocaleDateString("fr-FR", { year: "numeric", month: "long", day: "numeric" })
}

const isOrganizerOrAdmin = computed(() => {
  return userStore.isAuthenticated && (userStore.role === "organizer" || userStore.role === "admin")
})

const filteredEvents = computed(() => {
  const s = q.value.trim().toLowerCase()
  const now = new Date()

  let list = [...eventsStore.events]

  if (onlyFuture.value) {
    list = list.filter((e) => (e?.date ? new Date(e.date) >= now : true))
  }

  if (s) {
    list = list.filter((e) => {
      const hay = `${e?.title ?? ""} ${e?.description ?? ""} ${e?.location ?? ""}`.toLowerCase()
      return hay.includes(s)
    })
  }

  if (sortBy.value === "dateAsc") {
    list.sort((a, b) => new Date(a?.date || 0) - new Date(b?.date || 0))
  } else if (sortBy.value === "dateDesc") {
    list.sort((a, b) => new Date(b?.date || 0) - new Date(a?.date || 0))
  } else if (sortBy.value === "title") {
    list.sort((a, b) => (a?.title || "").localeCompare(b?.title || ""))
  }

  return list
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
      <div>
        <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Événements</h3>
        <p class="text-slate-500 mt-1">Découvrez et gérez vos événements.</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="eventsStore.fetchEvents()"
          class="px-4 py-2 rounded-full bg-slate-900 text-white text-sm font-semibold
                 hover:bg-slate-800 transition disabled:opacity-60"
          :disabled="eventsStore.loading"
        >
          {{ eventsStore.loading ? "Actualisation..." : "Rafraîchir" }}
        </button>

        <router-link
          v-if="isOrganizerOrAdmin"
          to="/create"
          class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold
                 hover:bg-indigo-500 transition"
        >
          + Créer
        </router-link>
      </div>
    </div>

    <!-- Controls -->
    <div class="grid gap-3 lg:grid-cols-3">
      <!-- Search -->
      <div class="relative">
        <input
          v-model="q"
          placeholder="Rechercher : titre, lieu, description..."
          class="w-full rounded-2xl border bg-white px-4 py-3 pr-11
                 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        />
        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400">⌕</span>
      </div>

      <!-- Filters -->
      <label class="flex items-center gap-3 rounded-2xl border bg-white px-4 py-3">
        <input v-model="onlyFuture" type="checkbox" class="h-4 w-4 rounded border-slate-300" />
        <span class="text-sm font-medium text-slate-700">Afficher seulement les événements à venir</span>
      </label>

      <!-- Sort -->
      <div class="rounded-2xl border bg-white px-4 py-3 flex items-center gap-3">
        <span class="text-sm font-medium text-slate-700 whitespace-nowrap">Trier par</span>
        <select
          v-model="sortBy"
          class="w-full rounded-xl border px-3 py-2 bg-white
                 focus:outline-none focus:ring-2 focus:ring-indigo-200"
        >
          <option value="dateAsc">Date (croissante)</option>
          <option value="dateDesc">Date (décroissante)</option>
          <option value="title">Titre (A → Z)</option>
        </select>
      </div>
    </div>

    <!-- Loading skeleton -->
    <div v-if="eventsStore.loading" class="grid gap-4 sm:grid-cols-2">
      <div v-for="i in 4" :key="i" class="rounded-3xl border bg-white p-5 shadow-sm">
        <div class="h-4 w-2/3 rounded bg-slate-100 animate-pulse"></div>
        <div class="mt-3 h-3 w-1/3 rounded bg-slate-100 animate-pulse"></div>
        <div class="mt-4 space-y-2">
          <div class="h-3 w-full rounded bg-slate-100 animate-pulse"></div>
          <div class="h-3 w-5/6 rounded bg-slate-100 animate-pulse"></div>
        </div>
        <div class="mt-5 flex justify-between">
          <div class="h-3 w-1/3 rounded bg-slate-100 animate-pulse"></div>
          <div class="h-3 w-1/4 rounded bg-slate-100 animate-pulse"></div>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div
      v-else-if="eventsStore.error"
      class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 flex items-start justify-between gap-4"
    >
      <div>
        <p class="font-semibold">Erreur</p>
        <p class="text-sm mt-1">{{ eventsStore.error }}</p>
      </div>
      <button
        @click="eventsStore.fetchEvents()"
        class="px-4 py-2 rounded-full bg-red-600 text-white text-sm font-semibold hover:bg-red-500 transition"
      >
        Réessayer
      </button>
    </div>

    <!-- Empty -->
    <div
      v-else-if="filteredEvents.length === 0"
      class="p-6 rounded-3xl bg-white border shadow-sm"
    >
      <p class="text-lg font-bold text-slate-900">Aucun événement trouvé</p>
      <p class="text-sm text-slate-600 mt-1">
        Essayez de modifier la recherche ou les filtres.
      </p>

      <div class="mt-4 flex flex-wrap gap-3">
        <button
          @click="q = ''; onlyFuture = false; sortBy = 'dateAsc'"
          class="px-4 py-2 rounded-full bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition"
        >
          Réinitialiser
        </button>

        <router-link
          v-if="isOrganizerOrAdmin"
          to="/create"
          class="px-4 py-2 rounded-full bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500 transition"
        >
          Créer un événement
        </router-link>
      </div>
    </div>

    <!-- Cards -->
    <div v-else class="grid gap-4 sm:grid-cols-2">
      <article
        v-for="event in filteredEvents"
        :key="event._id"
        class="group rounded-3xl border bg-white p-5 shadow-sm hover:shadow-md transition
               hover:-translate-y-0.5"
      >
        <header class="flex items-start justify-between gap-3">
          <h4 class="text-lg font-extrabold leading-tight text-slate-900">
            {{ event.title }}
          </h4>

          <span
            class="text-xs px-2 py-1 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100
                   whitespace-nowrap"
          >
            {{ event.location || "—" }}
          </span>
        </header>

        <p class="mt-2 text-sm text-slate-600 line-clamp-3">
          {{ event.description || "Aucune description." }}
        </p>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm text-slate-600">
          <span class="inline-flex items-center gap-2">
            <span class="h-8 w-8 rounded-2xl bg-slate-100 grid place-items-center">📅</span>
            {{ formatDate(event.date) }}
          </span>

          <span v-if="event.capacity" class="inline-flex items-center gap-2">
            <span class="h-8 w-8 rounded-2xl bg-slate-100 grid place-items-center">👥</span>
            {{ event.capacity }} places
          </span>
        </div>

        <div class="mt-4 pt-4 border-t flex items-center justify-between">
          <span class="text-xs text-slate-400">
            ID: {{ event._id?.slice?.(0, 8) }}…
          </span>

          <button
            class="text-sm font-semibold text-indigo-600 hover:text-indigo-500 transition"
          >
            Voir →
          </button>
          <button
  @click="eventsStore.deleteEvent(event._id)"
  class="text-red-600 text-sm font-semibold hover:underline"
>
  Supprimer
</button>
        </div>
      </article>
    </div>
  </div>
</template>