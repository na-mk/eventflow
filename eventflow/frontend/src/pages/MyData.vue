<script setup>
import { ref, onMounted } from "vue"
import { useRouter } from "vue-router"
import { useUserStore } from "../stores/user"
import { api } from "../services/api"

const userStore = useUserStore()
const router = useRouter()

const form = ref({ firstName: "", lastName: "", phone: "" })
const success = ref("")
const error = ref("")
const confirmAnon = ref(false)
const loading = ref(false)

onMounted(() => {
  if (userStore.user) {
    form.value.firstName = userStore.user.firstName || ""
    form.value.lastName = userStore.user.lastName || ""
    form.value.phone = userStore.user.phone || ""
  }
})

async function saveProfile() {
  error.value = ""
  success.value = ""
  loading.value = true
  try {
    await userStore.updateProfile(form.value)
    success.value = "Profil mis à jour avec succès."
  } catch (err) {
    error.value = err?.response?.data?.message || "Erreur lors de la mise à jour."
  } finally {
    loading.value = false
  }
}

async function exportData() {
  try {
    const res = await api.get('/me/export')
    const blob = new Blob([JSON.stringify(res.data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = 'mes-donnees-eventflow.json'
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    error.value = "Erreur lors de l'export."
  }
}

async function anonymize() {
  if (!confirmAnon.value) return
  if (!confirm("Cette action est irréversible. Confirmer l'anonymisation de votre compte ?")) return
  try {
    await userStore.anonymize()
    router.push("/login")
  } catch (err) {
    error.value = err?.response?.data?.message || "Erreur lors de l'anonymisation."
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto space-y-8">
    <div>
      <h2 class="text-2xl font-extrabold tracking-tight">Mes données personnelles</h2>
      <p class="text-sm text-slate-500 mt-1">
        Consultez, modifiez ou supprimez vos données conformément au RGPD (Art. 15, 16, 17).
      </p>
    </div>

    <!-- Feedback -->
    <div v-if="success" class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm">{{ success }}</div>
    <div v-if="error" class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">{{ error }}</div>

    <!-- Infos du compte -->
    <div class="bg-white border rounded-2xl p-6 shadow-sm space-y-2 text-sm">
      <h3 class="font-bold text-slate-800 mb-3">Informations du compte</h3>
      <p><span class="text-slate-500">Email :</span> <strong>{{ userStore.user?.email }}</strong></p>
      <p><span class="text-slate-500">Rôles :</span> <strong>{{ userStore.user?.roles?.join(', ') }}</strong></p>
      <p><span class="text-slate-500">Consentement donné le :</span>
        <strong>{{ userStore.user?.consentDate ? new Date(userStore.user.consentDate).toLocaleDateString('fr-FR') : 'Non renseigné' }}</strong>
        (v{{ userStore.user?.consentVersion || '—' }})
      </p>
      <p><span class="text-slate-500">Compte créé le :</span>
        <strong>{{ userStore.user?.createdAt ? new Date(userStore.user.createdAt).toLocaleDateString('fr-FR') : '—' }}</strong>
      </p>
    </div>

    <!-- Rectification Art. 16 -->
    <div class="bg-white border rounded-2xl p-6 shadow-sm">
      <h3 class="font-bold text-slate-800 mb-4">Rectifier mes données (Art. 16 RGPD)</h3>
      <form @submit.prevent="saveProfile" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-sm font-medium text-slate-700">Prénom</label>
            <input v-model="form.firstName" class="input" />
          </div>
          <div>
            <label class="text-sm font-medium text-slate-700">Nom</label>
            <input v-model="form.lastName" class="input" />
          </div>
        </div>
        <div>
          <label class="text-sm font-medium text-slate-700">Téléphone</label>
          <input v-model="form.phone" class="input" placeholder="+33 6 00 00 00 00" />
        </div>
        <button
          type="submit"
          :disabled="loading"
          class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-500 transition disabled:opacity-60"
        >
          {{ loading ? "Enregistrement..." : "Enregistrer les modifications" }}
        </button>
      </form>
    </div>

    <!-- Export Art. 20 -->
    <div class="bg-white border rounded-2xl p-6 shadow-sm">
      <h3 class="font-bold text-slate-800 mb-2">Exporter mes données (Art. 20 RGPD — Portabilité)</h3>
      <p class="text-sm text-slate-500 mb-4">Téléchargez toutes vos données personnelles au format JSON.</p>
      <button
        @click="exportData"
        class="px-5 py-2.5 rounded-xl bg-slate-800 text-white text-sm font-semibold hover:bg-slate-700 transition"
      >
        Télécharger mes données
      </button>
    </div>

    <!-- Anonymisation Art. 17 -->
    <div class="bg-white border border-red-200 rounded-2xl p-6 shadow-sm">
      <h3 class="font-bold text-red-700 mb-2">Supprimer mon compte (Art. 17 RGPD — Droit à l'oubli)</h3>
      <p class="text-sm text-slate-500 mb-4">
        Vos données personnelles seront anonymisées de manière irréversible. Vos inscriptions aux événements seront conservées de façon anonyme.
      </p>
      <label class="flex items-center gap-2 text-sm text-slate-700 mb-4 cursor-pointer">
        <input v-model="confirmAnon" type="checkbox" class="rounded" />
        Je comprends que cette action est irréversible
      </label>
      <button
        @click="anonymize"
        :disabled="!confirmAnon"
        class="px-5 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-500 transition disabled:opacity-40"
      >
        Anonymiser mon compte
      </button>
    </div>
  </div>
</template>

<style scoped>
.input {
  @apply mt-1 w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400;
}
</style>
