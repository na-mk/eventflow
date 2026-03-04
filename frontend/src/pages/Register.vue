<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'

const userStore = useUserStore()
const router = useRouter()

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'participant'
})

async function submit() {
  try {
    await userStore.register(form)
    router.push('/')
  } catch (e) {
    // userStore.error affichera le message
  }
}
</script>

<template>
  <h2>Register</h2>

  <form @submit.prevent="submit" style="display:flex; flex-direction:column; gap:10px; max-width:360px;">
    <input v-model="form.name" placeholder="Name" />
    <input v-model="form.email" type="email" placeholder="Email" />
    <input v-model="form.password" type="password" placeholder="Password" />

    <select v-model="form.role">
      <option value="participant">participant</option>
      <option value="organizer">organizer</option>
      <option value="admin">admin</option>
    </select>

    <button type="submit" :disabled="userStore.loading">
      {{ userStore.loading ? 'Création...' : 'Créer le compte' }}
    </button>

    <p v-if="userStore.error" style="color:red; margin:0;">
      {{ userStore.error }}
    </p>
  </form>
</template>