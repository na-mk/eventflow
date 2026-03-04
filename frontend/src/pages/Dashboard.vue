<script setup>
import { onMounted } from "vue"
import { useEventsStore } from "../stores/events"

const eventsStore = useEventsStore()

onMounted(() => {
  eventsStore.fetchEvents()
})

function formatDate(iso) {
  if (!iso) return ""
  const d = new Date(iso)
  return d.toLocaleDateString("fr-FR", { year: "numeric", month: "long", day: "numeric" })
}
</script>

<template>
  <div class="flex items-start justify-between gap-4 mb-6">
    <div>
      <h2 class="text-xl font-bold">Events Dashboard</h2>
      <p class="text-sm text-slate-500">Liste des événements disponibles.</p>
    </div>

    <button
      @click="eventsStore.fetchEvents()"
      class="px-3 py-2 rounded-lg bg-white border hover:bg-slate-50 text-sm"
    >
      Rafraîchir
    </button>
  </div>

  <div v-if="eventsStore.loading" class="p-4 rounded-xl bg-white border">
    Chargement...
  </div>

  <div
    v-if="eventsStore.error"
    class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700"
  >
    {{ eventsStore.error }}
  </div>

  <div
    v-if="!eventsStore.loading && !eventsStore.error && eventsStore.events.length === 0"
    class="p-4 rounded-xl bg-white border"
  >
    Aucun événement pour le moment.
  </div>

  <div class="grid gap-4 sm:grid-cols-2" v-if="eventsStore.events.length">
    <div
      v-for="event in eventsStore.events"
      :key="event._id"
      class="bg-white border rounded-2xl p-5 shadow-sm hover:shadow-md transition"
    >
      <div class="flex items-start justify-between gap-3">
        <h3 class="text-lg font-semibold leading-tight">{{ event.title }}</h3>

        <span
          class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-100"
        >
          {{ event.location }}
        </span>
      </div>

      <p class="text-slate-600 text-sm mt-2">
        {{ event.description || "—" }}
      </p>

      <div class="mt-4 flex items-center justify-between text-sm text-slate-600">
        <span>📅 {{ formatDate(event.date) }}</span>
        <span v-if="event.capacity">👥 {{ event.capacity }} places</span>
      </div>
    </div>
  </div>
</template>