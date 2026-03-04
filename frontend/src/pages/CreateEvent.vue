<script setup>
import { reactive } from "vue"
import { useRouter } from "vue-router"
import { useEventsStore } from "../stores/events"

const router = useRouter()
const eventsStore = useEventsStore()

const form = reactive({
  title: "",
  description: "",
  date: "",
  location: "",
  capacity: 10
})

async function submit() {
  await eventsStore.createEvent(form)
  if (!eventsStore.error) {
    router.push("/")
  }
}
</script>

<template>
  <div class="max-w-xl mx-auto">
    <div class="mb-6">
      <h2 class="text-xl font-bold">Créer un événement</h2>
      <p class="text-sm text-slate-500">Réservé aux organisateurs et admins.</p>
    </div>

    <div v-if="eventsStore.error" class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
      {{ eventsStore.error }}
    </div>

    <form
      @submit.prevent="submit"
      class="bg-white border rounded-2xl p-6 shadow-sm space-y-4"
    >
      <div>
        <label class="text-sm font-medium">Titre</label>
        <input
          v-model="form.title"
          class="mt-1 w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
          placeholder="Ex: Conférence Full Stack"
        />
      </div>

      <div>
        <label class="text-sm font-medium">Description</label>
        <textarea
          v-model="form.description"
          rows="3"
          class="mt-1 w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
          placeholder="Détail de l'événement..."
        />
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-medium">Date</label>
          <input
            v-model="form.date"
            type="date"
            class="mt-1 w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
          />
        </div>

        <div>
          <label class="text-sm font-medium">Places</label>
          <input
            v-model="form.capacity"
            type="number"
            min="1"
            class="mt-1 w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
          />
        </div>
      </div>

      <div>
        <label class="text-sm font-medium">Lieu</label>
        <input
          v-model="form.location"
          class="mt-1 w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-200"
          placeholder="Ex: Paris"
        />
      </div>

      <button
        type="submit"
        class="w-full px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-500 transition"
      >
        Créer
      </button>
    </form>
  </div>
</template>