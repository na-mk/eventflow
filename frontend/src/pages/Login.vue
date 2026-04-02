<script setup>
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
const userStore = useUserStore()
const router = useRouter()
const form = reactive({ email: '', password: '' })
async function submit() {
  await userStore.login(form)
  if (!userStore.error) router.push('/')
}
</script>

<template>
  <div class='min-h-[70vh] flex items-center justify-center py-12'>
    <div class='w-full max-w-md'>
      <div class='text-center mb-10'>
        <div class='w-14 h-14 rounded-2xl bg-orange-500/15 border border-orange-500/20 grid place-items-center mx-auto mb-5'>
          <svg class='w-7 h-7 text-orange-500' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'/>
          </svg>
        </div>
        <h1 class='text-3xl font-extrabold text-main'>Connexion</h1>
        <p class='text-sub text-sm mt-2'>Accédez à votre espace EVENTFLOW</p>
      </div>
      <div v-if='userStore.error' class='mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm'>
        {{ userStore.error }}
      </div>
      <div class='glass p-8'>
        <form @submit.prevent='submit' class='space-y-5'>
          <div>
            <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Email</label>
            <input v-model='form.email' type='email' class='ef-input' placeholder='email@exemple.com' autocomplete='email' required />
          </div>
          <div>
            <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Mot de passe</label>
            <input v-model='form.password' type='password' class='ef-input' placeholder='••••••••' autocomplete='current-password' required />
          </div>
          <button type='submit' :disabled='userStore.loading' class='btn-primary w-full py-3.5 rounded-xl mt-2'>
            {{ userStore.loading ? 'Connexion…' : 'Se connecter' }}
          </button>
        </form>
        <div class='mt-6 pt-6 border-t ef-border text-center'>
          <p class='text-sm text-muted'>
            Pas de compte ?
            <router-link to='/register' class='text-orange-400 font-semibold hover:text-orange-300 transition ml-1'>Créer un compte →</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>