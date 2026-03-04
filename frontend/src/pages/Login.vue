<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'

const userStore = useUserStore()
const router = useRouter()

const form = reactive({
  email: '',
  password: ''
})

async function submit() {
  try {
    await userStore.login(form)
    router.push('/')
  } catch (e) {
    // userStore.error affichera le message
  }
}
</script>

<template>
  <h2>Login</h2>

  <form @submit.prevent="submit" style="display:flex; flex-direction:column; gap:10px; max-width:360px;">
    <input v-model="form.email" type="email" placeholder="Email" />
    <input v-model="form.password" type="password" placeholder="Password" />

    <button type="submit" :disabled="userStore.loading">
      {{ userStore.loading ? 'Connexion...' : 'Se connecter' }}
    </button>

    <p v-if="userStore.error" style="color:red; margin:0;">
      {{ userStore.error }}
    </p>
  </form>
</template>