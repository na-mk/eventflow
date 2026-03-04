<script setup>

import { onMounted } from "vue"
import { useEventsStore } from "../stores/events"

const eventsStore = useEventsStore()

onMounted(() => {
  eventsStore.fetchEvents()
})

</script>

<template>

<h2>Events Dashboard</h2>

<p v-if="eventsStore.loading">Chargement...</p>

<p v-if="eventsStore.error" style="color:red">
  {{ eventsStore.error }}
</p>

<div v-for="event in eventsStore.events" :key="event._id" style="border:1px solid #ccc; padding:10px; margin-bottom:10px">

<h3>{{ event.title }}</h3>

<p>{{ event.description }}</p>

<p><strong>Lieu:</strong> {{ event.location }}</p>

<p><strong>Date:</strong> {{ event.date }}</p>

</div>

</template>