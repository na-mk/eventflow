<script setup>

const props = defineProps({
  event: Object
})

const emit = defineEmits(["register"])

</script>

<template>

<div style="border:1px solid #ddd; border-radius:10px; padding:12px;">

<h3>{{ event.title }}</h3>

<p>{{ event.description }}</p>

<p>
<strong>Date:</strong> {{ event.date }}
</p>

<p>
<strong>Lieu:</strong> {{ event.location }}
</p>

<p>
<strong>Places restantes:</strong>
{{ event.capacity - event.registered }}
</p>

<button @click="emit('register', event.id)">
S'inscrire
</button>

</div>

</template>