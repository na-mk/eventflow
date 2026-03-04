<script setup>
import { useEventsStore } from '../stores/events'
import EventCard from '../components/EventCard.vue'

const eventsStore = useEventsStore()
</script>

<template>

<h2>Dashboard Events</h2>

<div style="display:flex; gap:10px; flex-wrap:wrap; margin: 15px 0;">

<input
v-model="eventsStore.filters.q"
placeholder="Rechercher événement"
/>

<select v-model="eventsStore.filters.location">

<option
v-for="loc in eventsStore.locations"
:key="loc"
:value="loc"
>
{{ loc }}
</option>

</select>

<label>

<input
type="checkbox"
v-model="eventsStore.filters.onlyAvailable"
/>

Uniquement avec places disponibles

</label>

</div>

<p>
Résultats :
<strong>{{ eventsStore.filteredEvents.length }}</strong>
</p>

<div style="display:grid; gap:12px; margin-top: 12px;">

<EventCard
v-for="ev in eventsStore.filteredEvents"
:key="ev.id"
:event="ev"
@register="eventsStore.registerOneClick"
/>

</div>

</template>