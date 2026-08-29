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
const pageTitle = computed(() => isEditMode.value ? 'Modifier votre événement' : 'Créer un événement')

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
    errors.value.form = err?.response?.data?.message || 'Impossible de charger cet événement.'
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
  <div class="max-w-4xl mx-auto py-8">
    <div class="mb-9">
      <div class="section-label mb-3">Organisation</div>
      <h1 class="text-3xl md:text-4xl font-extrabold text-main">{{ pageTitle }}</h1>
      <p class="text-sub text-base leading-7 mt-3 max-w-2xl">
        {{ isEditMode
          ? 'Mettez à jour les informations utiles puis enregistrez vos modifications.'
          : 'Renseignez les informations essentielles. Vous pourrez publier votre événement immédiatement ou le conserver en brouillon.' }}
      </p>
    </div>

    <div v-if="errors.form" class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 text-sm">
      {{ errors.form }}
    </div>

    <div v-if="loadingInitial" class="glass p-8 animate-pulse">
      <div class="h-5 w-48 rounded" style="background:var(--border)"></div>
      <div class="mt-4 h-10 rounded-xl" style="background:var(--border-light)"></div>
      <div class="mt-4 h-28 rounded-xl" style="background:var(--border-light)"></div>
    </div>

    <div v-else class="grid gap-6 lg:grid-cols-[1fr_260px]">
      <div class="glass p-7 md:p-8 space-y-7">
        <div>
          <label class="block text-sm font-semibold text-main mb-2">Titre de l'événement *</label>
          <input v-model="form.title" class="ef-input" placeholder="Ex. Conférence Full Stack 2026" />
          <p v-if="errors.title" class="text-xs text-red-500 mt-1">{{ errors.title }}</p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-main mb-2">Description *</label>
          <textarea v-model="form.description" rows="6" class="ef-input resize-none" placeholder="Présentez le programme, les objectifs et ce que les participants peuvent attendre de l'événement."></textarea>
          <p v-if="errors.description" class="text-xs text-red-500 mt-1">{{ errors.description }}</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-main mb-2">Début *</label>
            <input v-model="form.eventDate" type="datetime-local" class="ef-input" />
            <p v-if="errors.eventDate" class="text-xs text-red-500 mt-1">{{ errors.eventDate }}</p>
          </div>
          <div>
            <label class="block text-sm font-semibold text-main mb-2">Fin</label>
            <input v-model="form.endDate" type="datetime-local" class="ef-input" />
            <p v-if="errors.endDate" class="text-xs text-red-500 mt-1">{{ errors.endDate }}</p>
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-main mb-2">Lieu *</label>
          <input v-model="form.location" class="ef-input" placeholder="Ex. Paris — Palais des Congrès" />
          <p v-if="errors.location" class="text-xs text-red-500 mt-1">{{ errors.location }}</p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-main mb-2">Nombre de places *</label>
          <input v-model="form.maxParticipants" type="number" min="1" class="ef-input" />
          <p v-if="errors.maxParticipants" class="text-xs text-red-500 mt-1">{{ errors.maxParticipants }}</p>
        </div>

        <div class="flex gap-4 pt-2">
          <button type="button" @click="router.back()" class="btn-outline flex-1 py-3.5 rounded-xl">Annuler</button>
          <button @click="submit" :disabled="eventsStore.loading" class="btn-primary flex-1 py-3.5 rounded-xl">
            {{ eventsStore.loading ? 'Enregistrement...' : (isEditMode ? 'Enregistrer les modifications' : "Créer l'événement") }}
          </button>
        </div>
      </div>

      <aside class="space-y-4">
        <div class="glass p-5">
          <div class="section-label mb-2">Publication</div>
          <h2 class="font-extrabold text-main">Quand souhaitez-vous le rendre visible ?</h2>
          <label class="mt-4 flex items-start gap-3 rounded-xl border p-4 cursor-pointer"
            style="background:var(--border-light);border-color:var(--border)">
            <input v-model="form.isPublished" type="checkbox" class="mt-1 rounded" />
            <span class="text-sm text-sub">
              <span class="font-semibold text-main block">Publier immédiatement</span>
              <span class="text-muted">L'événement apparaîtra dans la liste publique dès sa création.</span>
            </span>
          </label>
          <p v-if="!form.isPublished" class="mt-3 text-xs leading-5 text-muted">
            Sans publication immédiate, votre événement restera en brouillon jusqu'à ce que vous décidiez de le publier.
          </p>
        </div>

        <div class="rounded-2xl border p-5 text-sm leading-6 text-sub"
          style="border-color:rgba(249,115,22,0.2);background:rgba(249,115,22,0.06)">
          <div class="font-bold text-main">Conseil</div>
          <p class="mt-1">Un titre clair, une description précise et un lieu complet donnent davantage envie de s'inscrire.</p>
        </div>
      </aside>
    </div>
  </div>
</template>