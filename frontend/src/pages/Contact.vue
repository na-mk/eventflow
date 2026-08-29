<script setup>
import { reactive, ref } from 'vue'
import { api } from '../services/api'

const form = reactive({
  name: '',
  email: '',
  subject: '',
  message: '',
})

const errors = ref({})
const success = ref('')
const sending = ref(false)

function resetMessages() {
  errors.value = {}
  success.value = ''
}

function validate() {
  const nextErrors = {}

  if (!form.name.trim()) nextErrors.name = 'Nom requis.'
  if (!form.email.trim()) {
    nextErrors.email = 'Email requis.'
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    nextErrors.email = 'Email invalide.'
  }
  if (!form.subject.trim()) nextErrors.subject = 'Sujet requis.'
  if (!form.message.trim()) {
    nextErrors.message = 'Message requis.'
  } else if (form.message.trim().length < 10) {
    nextErrors.message = 'Le message doit contenir au moins 10 caractères.'
  }

  errors.value = nextErrors
  return Object.keys(nextErrors).length === 0
}

async function submit() {
  resetMessages()

  if (!validate()) return

  sending.value = true

  try {
    const res = await api.post('/contact', { ...form })
    success.value = res.data?.message
      ? 'Votre message a bien été envoyé. Nous vous répondrons rapidement.'
      : 'Votre message a bien été pris en compte. Nous vous répondrons rapidement.'
    form.name = ''
    form.email = ''
    form.subject = ''
    form.message = ''
  } catch (err) {
    const apiErrors = err?.response?.data?.errors
    if (apiErrors) {
      errors.value = apiErrors
    } else {
      errors.value = {
        form: err?.response?.data?.message || "Impossible d'envoyer le message pour le moment.",
      }
    }
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <section class="max-w-6xl mx-auto space-y-8">
    <div class="grid gap-8 lg:grid-cols-[0.9fr_1.1fr] lg:items-start">
      <div class="pt-5 lg:pt-10">
        <div class="section-label mb-3">Restons en contact</div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-main leading-tight">Une question ?<br>Parlons-en.</h1>
        <p class="mt-5 max-w-xl text-base leading-7 text-sub">
          Besoin d'aide pour utiliser EventFlow, organiser un événement ou nous faire part d'une suggestion ?
          Envoyez-nous un message directement depuis ce formulaire.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
          <div class="rounded-2xl border p-5" style="border-color:var(--border);background:var(--bg-card)">
            <div class="font-extrabold text-main">Assistance</div>
            <p class="mt-2 text-sm leading-6 text-sub">Une difficulté avec votre compte, une inscription ou un événement ? Décrivez-nous simplement la situation.</p>
          </div>
          <div class="rounded-2xl border p-5" style="border-color:var(--border);background:var(--bg-card)">
            <div class="font-extrabold text-main">Suggestions & partenariats</div>
            <p class="mt-2 text-sm leading-6 text-sub">Vos retours nous aident à améliorer l'expérience et à imaginer de nouveaux usages.</p>
          </div>
        </div>

        <p class="mt-6 text-sm text-muted">Nous faisons au mieux pour répondre sous 24 à 48 heures.</p>
      </div>

      <section class="glass p-6 md:p-8">
        <div class="mb-6">
          <h2 class="text-2xl font-extrabold text-main">Envoyer un message</h2>
          <p class="mt-2 text-sm text-sub">Tous les champs nous permettent de mieux comprendre votre demande.</p>
        </div>

        <div v-if="success" class="mb-5 rounded-xl border px-4 py-3 text-sm text-emerald-600"
          style="background:rgba(16,185,129,0.08); border-color:rgba(16,185,129,0.2)">
          {{ success }}
        </div>
        <div v-if="errors.form" class="mb-5 rounded-xl border px-4 py-3 text-sm text-red-600"
          style="background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2)">
          {{ errors.form }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
          <div class="grid gap-4 md:grid-cols-2">
            <div>
              <label class="block text-sm font-semibold text-main mb-2">Votre nom</label>
              <input v-model="form.name" class="ef-input" placeholder="Nom et prénom" />
              <p v-if="errors.name" class="mt-2 text-xs text-red-500">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-semibold text-main mb-2">Votre email</label>
              <input v-model="form.email" type="email" class="ef-input" placeholder="vous@exemple.com" />
              <p v-if="errors.email" class="mt-2 text-xs text-red-500">{{ errors.email }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-main mb-2">Sujet</label>
            <input v-model="form.subject" class="ef-input" placeholder="Ex. Question sur mon inscription" />
            <p v-if="errors.subject" class="mt-2 text-xs text-red-500">{{ errors.subject }}</p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-main mb-2">Votre message</label>
            <textarea
              v-model="form.message"
              rows="7"
              class="ef-input resize-none"
              placeholder="Décrivez votre demande en quelques lignes."
            ></textarea>
            <p v-if="errors.message" class="mt-2 text-xs text-red-500">{{ errors.message }}</p>
          </div>

          <div class="flex justify-end pt-2">
            <button type="submit" class="btn-primary px-7 py-3" :disabled="sending">
              {{ sending ? 'Envoi en cours...' : 'Envoyer le message' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </section>
</template>