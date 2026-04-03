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
    nextErrors.message = 'Le message doit contenir au moins 10 caracteres.'
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
      ? 'Votre message a bien ete envoye. Nous vous repondrons rapidement.'
      : 'Votre message a bien ete pris en compte. Nous vous repondrons rapidement.'
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
        form: err?.response?.data?.message || 'Impossible d envoyer le message pour le moment.',
      }
    }
  } finally {
    sending.value = false
  }
}
</script>

<template>
  <section class="max-w-5xl mx-auto space-y-8">
    <div class="glass p-8 md:p-10">
      <div class="section-label mb-3">Contact</div>
      <h1 class="text-3xl md:text-4xl font-extrabold text-main">Nous contacter</h1>
      <p class="mt-3 max-w-3xl text-base leading-7 text-sub">
        Une question sur la plateforme, un besoin d'assistance ou une demande de demonstration ? Laissez-nous un
        message et nous reviendrons vers vous.
      </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
      <section class="glass p-6">
        <div class="section-label mb-2">Coordonnees</div>
        <h2 class="text-2xl font-extrabold text-main">Equipe EventFlow</h2>
        <div class="mt-5 space-y-4 text-sm text-sub">
          <p>
            <span class="text-muted">Email :</span>
            <strong class="text-main ml-2">contact@eventflow.local</strong>
          </p>
          <p>
            <span class="text-muted">Delai moyen :</span>
            <strong class="text-main ml-2">24 a 48 heures</strong>
          </p>
          <p>
            <span class="text-muted">Sujet :</span>
            <strong class="text-main ml-2">Support, partenariats, questions produit</strong>
          </p>
        </div>

        <div class="mt-6 rounded-2xl border p-4 text-sm text-sub"
          style="border-color:var(--border); background:var(--border-light)">
          Vous pouvez utiliser ce formulaire pour nous transmettre un retour utilisateur, signaler un probleme ou nous
          contacter a propos d'un evenement.
        </div>
      </section>

      <section class="glass p-6">
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
              <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Nom</label>
              <input v-model="form.name" class="ef-input" placeholder="Votre nom" />
              <p v-if="errors.name" class="mt-2 text-xs text-red-500">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Email</label>
              <input v-model="form.email" type="email" class="ef-input" placeholder="vous@exemple.com" />
              <p v-if="errors.email" class="mt-2 text-xs text-red-500">{{ errors.email }}</p>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Sujet</label>
            <input v-model="form.subject" class="ef-input" placeholder="Ex: Besoin d'assistance" />
            <p v-if="errors.subject" class="mt-2 text-xs text-red-500">{{ errors.subject }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Message</label>
            <textarea
              v-model="form.message"
              rows="7"
              class="ef-input resize-none"
              placeholder="Decrivez votre demande en quelques lignes."
            ></textarea>
            <p v-if="errors.message" class="mt-2 text-xs text-red-500">{{ errors.message }}</p>
          </div>

          <div class="flex justify-end">
            <button type="submit" class="btn-primary" :disabled="sending">
              {{ sending ? 'Envoi en cours...' : 'Envoyer le message' }}
            </button>
          </div>
        </form>
      </section>
    </div>
  </section>
</template>
