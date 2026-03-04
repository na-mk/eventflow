<script setup>
import { reactive } from 'vue'
import { useEventsStore } from '../stores/events'
import { useRouter } from 'vue-router'

const eventsStore = useEventsStore()
const router = useRouter()

const form = reactive({
  title: '',
  description: '',
  date: '',
  location: '',
  capacity: ''
})

function submitForm() {

  if (!form.title || !form.date || !form.location || !form.capacity) {
    alert("Tous les champs obligatoires doivent être remplis")
    return
  }

  if (form.capacity <= 0) {
    alert("Le nombre de places doit être supérieur à 0")
    return
  }

  eventsStore.createEvent({
    title: form.title,
    description: form.description,
    date: form.date,
    location: form.location,
    capacity: Number(form.capacity)
  })

  router.push("/")
}
</script>

<template>

<h2>Create Event</h2>

<form @submit.prevent="submitForm" style="display:flex; flex-direction:column; gap:10px; max-width:400px">

<input v-model="form.title" placeholder="Titre événement" />

<textarea v-model="form.description" placeholder="Description"></textarea>

<input v-model="form.date" type="date" />

<input v-model="form.location" placeholder="Lieu" />

<input v-model="form.capacity" type="number" placeholder="Nombre de places" />

<button type="submit">
Créer événement
</button>

</form>

</template>