<script setup>
import { computed, onMounted, ref } from "vue"
import { useRouter } from "vue-router"
import { useAuthStore } from "../stores/auth"
import { api } from "../services/api"

const userStore = useAuthStore()
const router = useRouter()

const form = ref({ firstName: "", lastName: "", phone: "" })
const success = ref("")
const error = ref("")
const confirmAnon = ref(false)
const loading = ref(false)
const consentLoading = ref(false)
const consentGiven = ref(true)
const consentVersion = ref("1.0")

const consentStatusLabel = computed(() => {
  return consentGiven.value ? "Consentement actif" : "Consentement retire"
})

const roleLabel = computed(() => {
  const roles = userStore.user?.roles || []
  if (roles.includes('ROLE_ADMIN')) return 'Administrateur'
  if (roles.includes('ROLE_ORGANIZER')) return 'Organisateur'
  return 'Participant'
})

onMounted(async () => {
  if (!userStore.user && userStore.token) {
    await userStore.fetchMe()
  }

  hydrateForm()
})

function hydrateForm() {
  if (!userStore.user) return

  form.value.firstName = userStore.user.firstName || ""
  form.value.lastName = userStore.user.lastName || ""
  form.value.phone = userStore.user.phone || ""
  consentGiven.value = !!userStore.user.consentDate
  consentVersion.value = userStore.user.consentVersion || "1.0"
}

async function saveProfile() {
  error.value = ""
  success.value = ""
  loading.value = true

  try {
    await userStore.updateProfile(form.value)
    success.value = "Profil mis a jour avec succes."
  } catch (err) {
    error.value = err?.response?.data?.message || "Erreur lors de la mise a jour."
  } finally {
    loading.value = false
  }
}

async function updateConsent(nextValue) {
  error.value = ""
  success.value = ""
  consentLoading.value = true

  try {
    await api.post('/consent', {
      consentGiven: nextValue,
      consentVersion: consentVersion.value,
    })
    await userStore.fetchMe()
    hydrateForm()
    success.value = nextValue
      ? "Consentement mis a jour avec succes."
      : "Consentement retire avec succes."
  } catch (err) {
    error.value = err?.response?.data?.message || "Erreur lors de la mise a jour du consentement."
  } finally {
    consentLoading.value = false
  }
}

async function exportData() {
  error.value = ""
  success.value = ""

  try {
    const res = await api.get('/me/export')
    const blob = new Blob([JSON.stringify(res.data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'mes-donnees-eventflow.json'
    a.click()
    URL.revokeObjectURL(url)
    success.value = "Export telecharge avec succes."
  } catch (err) {
    error.value = err?.response?.data?.message || "Erreur lors de l'export."
  }
}

async function anonymize() {
  if (!confirmAnon.value) return
  if (!confirm("Cette action est irreversible. Confirmer l'anonymisation de votre compte ?")) return

  try {
    await userStore.anonymize()
    router.push("/login")
  } catch (err) {
    error.value = err?.response?.data?.message || "Erreur lors de l'anonymisation."
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-8">
    <div class="glass p-8 md:p-10">
      <div class="section-label mb-3">ProfileView</div>
      <h1 class="text-3xl md:text-4xl font-extrabold text-main">Mes donnees personnelles</h1>
      <p class="mt-3 max-w-3xl text-base leading-7 text-sub">
        Consultez, modifiez, exportez ou anonymisez vos donnees conformement aux exigences RGPD du projet.
      </p>
    </div>

    <div v-if="success" class="rounded-xl border px-4 py-3 text-sm text-emerald-600"
      style="background:rgba(16,185,129,0.08); border-color:rgba(16,185,129,0.2)">
      {{ success }}
    </div>
    <div v-if="error" class="rounded-xl border px-4 py-3 text-sm text-red-600"
      style="background:rgba(239,68,68,0.08); border-color:rgba(239,68,68,0.2)">
      {{ error }}
    </div>

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
      <section class="glass p-6">
        <h2 class="text-xl font-extrabold text-main">Informations du compte</h2>
        <div class="mt-5 space-y-3 text-sm">
          <p><span class="text-muted">Prenom :</span> <strong class="text-main">{{ userStore.user?.firstName }}</strong></p>
          <p><span class="text-muted">Nom :</span> <strong class="text-main">{{ userStore.user?.lastName }}</strong></p>
          <p><span class="text-muted">Email :</span> <strong class="text-main">{{ userStore.user?.email }}</strong></p>
          <p><span class="text-muted">Role :</span> <strong class="text-main">{{ roleLabel }}</strong></p>
          <p>
            <span class="text-muted">Consentement donne le :</span>
            <strong class="text-main">
              {{ userStore.user?.consentDate ? new Date(userStore.user.consentDate).toLocaleDateString('fr-FR') : 'Non renseigne' }}
            </strong>
            (v{{ userStore.user?.consentVersion || '—' }})
          </p>
          <p>
            <span class="text-muted">Compte cree le :</span>
            <strong class="text-main">{{ userStore.user?.createdAt ? new Date(userStore.user.createdAt).toLocaleDateString('fr-FR') : '—' }}</strong>
          </p>
        </div>
      </section>

      <section class="glass p-6">
        <h2 class="text-xl font-extrabold text-main">Gestion du consentement</h2>
        <p class="mt-3 text-sm leading-6 text-sub">
          Activez ou retirez votre consentement au traitement de vos donnees personnelles. Chaque changement est journalise.
        </p>

        <div class="mt-5 flex flex-wrap items-center gap-3">
          <span class="badge-green" v-if="consentGiven">{{ consentStatusLabel }}</span>
          <span class="badge-orange" v-else>{{ consentStatusLabel }}</span>
          <span class="badge-blue">Version {{ consentVersion }}</span>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2">
          <button
            class="btn-primary"
            :disabled="consentLoading || consentGiven"
            @click="updateConsent(true)"
          >
            {{ consentLoading && !consentGiven ? 'Mise a jour...' : 'Donner mon consentement' }}
          </button>
          <button
            class="btn-outline"
            :disabled="consentLoading || !consentGiven"
            @click="updateConsent(false)"
          >
            {{ consentLoading && consentGiven ? 'Mise a jour...' : 'Retirer mon consentement' }}
          </button>
        </div>
      </section>
    </div>

    <section class="glass p-6">
      <h2 class="text-xl font-extrabold text-main">Rectifier mes donnees (Art. 16 RGPD)</h2>
      <form @submit.prevent="saveProfile" class="mt-5 space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Prenom</label>
            <input v-model="form.firstName" class="ef-input" />
          </div>
          <div>
            <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Nom</label>
            <input v-model="form.lastName" class="ef-input" />
          </div>
        </div>
        <div>
          <label class="block text-xs font-semibold text-muted uppercase tracking-wider mb-2">Telephone</label>
          <input v-model="form.phone" class="ef-input" placeholder="+33 6 00 00 00 00" />
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="btn-primary"
        >
          {{ loading ? "Enregistrement..." : "Enregistrer les modifications" }}
        </button>
      </form>
    </section>

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
      <section class="glass p-6">
        <h2 class="text-xl font-extrabold text-main">Exporter mes donnees (Art. 20)</h2>
        <p class="mt-3 text-sm leading-6 text-sub">
          Telechargez toutes vos donnees personnelles et l'historique RGPD au format JSON.
        </p>
        <button @click="exportData" class="btn-outline mt-5">Telecharger mes donnees</button>
      </section>

      <section class="rounded-3xl border p-6" style="border-color:rgba(239,68,68,0.2); background:rgba(255,255,255,0.9)">
        <h2 class="text-xl font-extrabold text-red-600">Supprimer mon compte (Art. 17)</h2>
        <p class="mt-3 text-sm leading-6 text-sub">
          Vos donnees personnelles seront anonymisees de maniere irreversible. Les inscriptions pourront etre conservees de facon anonymisee.
        </p>
        <label class="mt-5 flex items-center gap-3 text-sm text-sub cursor-pointer">
          <input v-model="confirmAnon" type="checkbox" class="rounded" />
          Je comprends que cette action est irreversible.
        </label>
        <button
          @click="anonymize"
          :disabled="!confirmAnon"
          class="btn-danger mt-5"
        >
          Anonymiser mon compte
        </button>
      </section>
    </div>
  </div>
</template>
