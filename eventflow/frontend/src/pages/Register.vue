<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '../stores/user'
const userStore = useUserStore()
const router = useRouter()
const form = reactive({ firstName: '', lastName: '', email: '', password: '', phone: '', roles: ['ROLE_USER'], consentGiven: false })
const errors = ref({})
function validate() {
  errors.value = {}
  if (!form.firstName) errors.value.firstName = 'Prénom requis'
  if (!form.lastName) errors.value.lastName = 'Nom requis'
  if (!form.email) errors.value.email = 'Email requis'
  if (!form.password || form.password.length < 6) errors.value.password = '6 caractères minimum'
  if (!form.consentGiven) errors.value.consent = 'Consentement requis (RGPD)'
  return Object.keys(errors.value).length === 0
}
async function submit() {
  if (!validate()) return
  try {
    await userStore.register({ firstName: form.firstName, lastName: form.lastName, email: form.email, password: form.password, phone: form.phone || null, roles: form.roles, consentGiven: form.consentGiven })
    if (!userStore.error) router.push('/')
  } catch (err) {
    const e = err?.response?.data?.errors
    if (e) errors.value = e
  }
}
</script>

<template>
  <div class='min-h-[70vh] flex items-center justify-center py-12'>
    <div class='w-full max-w-lg'>
      <div class='text-center mb-10'>
        <div class='w-14 h-14 rounded-2xl bg-purple-500/15 border border-purple-500/20 grid place-items-center mx-auto mb-5'>
          <svg class='w-7 h-7 text-purple-400' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'/>
          </svg>
        </div>
        <h1 class='text-3xl font-extrabold text-main'>Créer un compte</h1>
        <p class='text-sub text-sm mt-2'>Rejoignez la plateforme EVENTFLOW</p>
      </div>
      <div v-if='userStore.error' class='mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm'>
        {{ typeof userStore.error === 'object' ? JSON.stringify(userStore.error) : userStore.error }}
      </div>
      <div class='glass p-8'>
        <form @submit.prevent='submit' class='space-y-5'>
          <div class='grid grid-cols-2 gap-4'>
            <div>
              <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Prénom *</label>
              <input v-model='form.firstName' class='ef-input' placeholder='Prénom' />
              <p v-if='errors.firstName' class='text-xs text-red-400 mt-1'>{{ errors.firstName }}</p>
            </div>
            <div>
              <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Nom *</label>
              <input v-model='form.lastName' class='ef-input' placeholder='Nom' />
              <p v-if='errors.lastName' class='text-xs text-red-400 mt-1'>{{ errors.lastName }}</p>
            </div>
          </div>
          <div>
            <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Email *</label>
            <input v-model='form.email' type='email' class='ef-input' placeholder='email@exemple.com' />
            <p v-if='errors.email' class='text-xs text-red-400 mt-1'>{{ errors.email }}</p>
          </div>
          <div>
            <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Mot de passe *</label>
            <input v-model='form.password' type='password' class='ef-input' placeholder='Minimum 6 caractères' />
            <p v-if='errors.password' class='text-xs text-red-400 mt-1'>{{ errors.password }}</p>
          </div>
          <div>
            <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Téléphone (optionnel)</label>
            <input v-model='form.phone' class='ef-input' placeholder='+33 6 00 00 00 00' />
          </div>
          <div>
            <label class='block text-xs font-semibold text-muted uppercase tracking-wider mb-2'>Rôle</label>
            <select v-model='form.roles[0]' class='ef-input'>
              <option value='ROLE_USER'>Participant</option>
              <option value='ROLE_ORGANIZER'>Organisateur</option>
            </select>
          </div>
          <div class='p-4 rounded-xl bg-orange-500/10 border border-orange-500/20'>
            <label class='flex items-start gap-3 cursor-pointer'>
              <input v-model='form.consentGiven' type='checkbox' class='mt-0.5 rounded' />
              <span class='text-xs text-sub leading-relaxed'>
                J&apos;accepte que mes données personnelles soient traitées par EVENTFLOW conformément à la
                <router-link to='/privacy' class='text-orange-400 hover:underline font-medium'>politique de confidentialité</router-link>
                (RGPD — Art. 6.1.a, version 1.0).
              </span>
            </label>
            <p v-if='errors.consent' class='text-xs text-red-400 mt-2 ml-6'>{{ errors.consent }}</p>
          </div>
          <button type='submit' :disabled='userStore.loading' class='btn-primary w-full py-3.5 rounded-xl'>
            {{ userStore.loading ? 'Création…' : 'Créer mon compte' }}
          </button>
        </form>
        <div class='mt-6 pt-6 border-t ef-border text-center'>
          <p class='text-sm text-muted'>
            Déjà un compte ?
            <router-link to='/login' class='text-orange-400 font-semibold hover:text-orange-300 transition ml-1'>Se connecter →</router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>