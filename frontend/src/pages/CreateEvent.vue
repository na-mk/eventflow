<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useEventsStore } from '../stores/events'

const route = useRoute()
const router = useRouter()
const eventsStore = useEventsStore()

const form = reactive({
  title: '',
  description: '',
  eventDate: '',
  endDate: '',
  location: '',
  maxParticipants: 50,
  isPublished: false,
})
const errors = ref({})
const loadingInitial = ref(false)

const isEditMode = computed(() => !!route.params.id)
const pageTitle = computed(() => isEditMode.value ? 'Modifier un evenement' : 'Creer un evenement')

function toDatetimeLocal(value) {
  if (!value) return ''
  const date = new Date(value)
  const pad = (part) => String(part).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`
}

onMounted(async () => {
  if (!isEditMode.value) return

  loadingInitial.value = true

  try {
    const existing = eventsStore.events.find((event) => String(event.id) === String(route.params.id))
    const source = existing || (await eventsStore.fetchEventById(route.params.id))

    form.title = source.title || ''
    form.description = source.description || ''
    form.eventDate = toDatetimeLocal(source.eventDate)
    form.endDate = toDatetimeLocal(source.endDate)
    form.location = source.location || ''
    form.maxParticipants = Number(source.maxParticipants || 50)
    form.isPublished = !!source.isPublished
  } catch (err) {
    errors.value.form = err?.response?.data?.message || 'Impossible de charger cet evenement.'
  } finally {
    loadingInitial.value = false
  }
})

async function submit() {
  errors.value = {}

  try {
    const payload = {
      ...form,
      maxParticipants: Number(form.maxParticipants),
      eventDate: form.eventDate ? new Date(form.eventDate).toISOString() : '',
      endDate: form.endDate ? new Date(form.endDate).toISOString() : '',
    }

    const saved = isEditMode.value
      ? await eventsStore.updateEvent(route.params.id, payload)
      : await eventsStore.createEvent(payload)

    router.push(isEditMode.value ? `/events/${saved.id}` : '/dashboard')
  } catch (err) {
    const e = err?.response?.data?.errors
    if (e) {
      errors.value = e
    } else {
      errors.value.form = err?.response?.data?.message || 'Une erreur est survenue.'
    }
  }
}
</script>

<template>
  <div class='max-w-3xl mx-auto py-8'>
    <div class='mb-10'>
      <div class='section-label mb-3'>EventFormView</div>
      <h1 class='text-3xl font-extrabold text-main'>{{ pageTitle }}</h1>
      <p class='text-sub text-sm mt-2'>
        {{ isEditMode ? 'Mettez a jour les informations de votre evenement.' : 'Publiez une nouvelle fiche evenement conforme au cahier des charges.' }}
      </p>
    </div>

    <div v-if='errors.form' class='mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 text-sm'>
      {{ errors.form }}
    </div>

    <div v-if='loadingInitial' class='glass p-8 animate-pulse'>
      <div class='h-5 w-48 rounded' style='background:var(--border)'></div>
      <div class='mt-4 h-10 rounded-xl' style='background:var(--border-light)'></div>
      <div class='mt-4 h-28 rounded-xl' style='background:var(--border-light)'></div>
    </div>

    <div v-else class='glass p-8 space-y-6'>
      <div>
        <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Titre *</label>
        <input v-model='form.title' class='ef-input' placeholder='Ex: Conference Full Stack 2026' />
        <p v-if='errors.title' class='text-xs text-red-500 mt-1'>{{ errors.title }}</p>
      </div>

      <div>
        <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Description *</label>
        <textarea v-model='form.description' rows='5' class='ef-input resize-none' placeholder='Detaillez le programme, les objectifs et le public vise.'></textarea>
        <p v-if='errors.description' class='text-xs text-red-500 mt-1'>{{ errors.description }}</p>
      </div>

      <div class='grid sm:grid-cols-2 gap-4'>
        <div>
          <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Debut *</label>
          <input v-model='form.eventDate' type='datetime-local' class='ef-input' />
          <p v-if='errors.eventDate' class='text-xs text-red-500 mt-1'>{{ errors.eventDate }}</p>
        </div>
        <div>
          <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Fin</label>
          <input v-model='form.endDate' type='datetime-local' class='ef-input' />
          <p v-if='errors.endDate' class='text-xs text-red-500 mt-1'>{{ errors.endDate }}</p>
        </div>
      </div>

      <div class='grid sm:grid-cols-2 gap-4'>
        <div>
          <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Capacite *</label>
          <input v-model='form.maxParticipants' type='number' min='1' class='ef-input' />
          <p v-if='errors.maxParticipants' class='text-xs text-red-500 mt-1'>{{ errors.maxParticipants }}</p>
        </div>
      </div>

      <div>
        <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Lieu *</label>
        <input v-model='form.location' class='ef-input' placeholder='Ex: Paris — Palais des Congres' />
        <p v-if='errors.location' class='text-xs text-red-500 mt-1'>{{ errors.location }}</p>
      </div>

      <label class='flex items-center gap-3 p-4 rounded-xl border' style='background:var(--border-light);border-color:var(--border)'>
        <input v-model='form.isPublished' type='checkbox' class='rounded' />
        <span class='text-sm text-sub'>
          <span class='font-semibold text-main'>Publier immediatement</span>
          <span class='text-muted ml-1'>- visible dans la liste publique.</span>
        </span>
      </label>

      <div class='flex gap-4 pt-2'>
        <button type='button' @click="router.back()" class='btn-outline flex-1 py-3.5 rounded-xl'>
          Annuler
        </button>
        <button @click='submit' :disabled='eventsStore.loading' class='btn-primary flex-1 py-3.5 rounded-xl'>
          {{ eventsStore.loading ? 'Enregistrement...' : (isEditMode ? 'Mettre a jour' : "Creer l'evenement") }}
        </button>
      </div>
    </div>
  </div>
</template>
