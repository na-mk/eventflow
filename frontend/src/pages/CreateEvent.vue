<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useEventsStore } from '../stores/events'
const router = useRouter()
const eventsStore = useEventsStore()
const form = reactive({ title: '', description: '', eventDate: '', location: '', maxParticipants: 50, isPublished: false })
const errors = ref({})
async function submit() {
  errors.value = {}
  try {
    await eventsStore.createEvent({ ...form, maxParticipants: Number(form.maxParticipants) })
    router.push('/')
  } catch (err) {
    const e = err?.response?.data?.errors
    if (e) errors.value = e
  }
}
</script>

<template>
  <div class='max-w-2xl mx-auto py-8'>
    <div class='mb-10'>
      <div class='section-label mb-3'>Organisateur</div>
      <h1 class='text-3xl font-extrabold text-main'>Créer un événement</h1>
      <p class='text-sub text-sm mt-2'>Remplissez les informations de votre événement professionnel.</p>
    </div>
    <div v-if='eventsStore.error' class='mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm'>
      {{ typeof eventsStore.error === 'object' ? JSON.stringify(eventsStore.error) : eventsStore.error }}
    </div>
    <div class='glass p-8 space-y-6'>
      <div>
        <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Titre de l&apos;événement *</label>
        <input v-model='form.title' class='ef-input' placeholder='Ex: Conférence Full Stack 2026' />
        <p v-if='errors.title' class='text-xs text-red-400 mt-1'>{{ errors.title }}</p>
      </div>
      <div>
        <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Description *</label>
        <textarea v-model='form.description' rows='4' class='ef-input resize-none' placeholder='Décrivez votre événement en détail…'></textarea>
        <p v-if='errors.description' class='text-xs text-red-400 mt-1'>{{ errors.description }}</p>
      </div>
      <div class='grid sm:grid-cols-2 gap-4'>
        <div>
          <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Date et heure *</label>
          <input v-model='form.eventDate' type='datetime-local' class='ef-input' />
          <p v-if='errors.eventDate' class='text-xs text-red-400 mt-1'>{{ errors.eventDate }}</p>
        </div>
        <div>
          <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Capacité *</label>
          <input v-model='form.maxParticipants' type='number' min='1' class='ef-input' placeholder='50' />
          <p v-if='errors.maxParticipants' class='text-xs text-red-400 mt-1'>{{ errors.maxParticipants }}</p>
        </div>
      </div>
      <div>
        <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Lieu *</label>
        <input v-model='form.location' class='ef-input' placeholder='Ex: Paris — Palais des Congrès' />
        <p v-if='errors.location' class='text-xs text-red-400 mt-1'>{{ errors.location }}</p>
      </div>
      <div class='flex items-center gap-3 p-4 rounded-xl border' style='background:var(--border-light);border-color:var(--border)'>
        <input v-model='form.isPublished' type='checkbox' id='published' class='rounded' />
        <label for='published' class='text-sm text-sub cursor-pointer'>
          <span class='font-semibold text-main'>Publier immédiatement</span>
          <span class='text-muted ml-1'>— visible sur le dashboard</span>
        </label>
      </div>
      <div class='flex gap-4 pt-2'>
        <button type='button' @click="router.push('/')" class='btn-outline flex-1 py-3.5 rounded-xl'>
          Annuler
        </button>
        <button @click='submit' :disabled='eventsStore.loading' class='btn-primary flex-1 py-3.5 rounded-xl'>
          {{ eventsStore.loading ? 'Création…' : "Créer l'événement" }}
        </button>
      </div>
    </div>
  </div>
</template>