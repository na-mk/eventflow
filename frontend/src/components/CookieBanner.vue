<script setup>
import { ref, onMounted } from "vue"

const visible = ref(false)
const showDetails = ref(false)

const prefs = ref({
  necessary: true,    // toujours activé
  analytics: false,
  marketing: false,
})

onMounted(() => {
  if (!localStorage.getItem('cookie_consent')) {
    visible.value = true
  }
})

function acceptAll() {
  prefs.value.analytics = true
  prefs.value.marketing = true
  save()
}

function rejectAll() {
  prefs.value.analytics = false
  prefs.value.marketing = false
  save()
}

function save() {
  localStorage.setItem('cookie_consent', JSON.stringify({
    version: '1.0',
    date: new Date().toISOString(),
    necessary: true,
    analytics: prefs.value.analytics,
    marketing: prefs.value.marketing,
  }))
  visible.value = false
}
</script>

<template>
  <Transition name="slide-up">
    <div
      v-if="visible"
      class="fixed bottom-0 left-0 right-0 z-50 border-t shadow-2xl p-4 sm:p-6"
      style="background:var(--bg-card);border-color:var(--border)"
    >
      <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-start gap-4">
          <div class="flex-1">
            <p class="text-sm font-semibold text-main mb-1">
              Gestion des cookies
            </p>
            <p class="text-xs text-sub">
              EVENTFLOW utilise des cookies pour assurer le bon fonctionnement du service. Vous pouvez choisir les catégories que vous acceptez.
              <button @click="showDetails = !showDetails" class="text-indigo-600 underline ml-1">
                {{ showDetails ? 'Masquer' : 'En savoir plus' }}
              </button>
            </p>

            <!-- Détail des catégories -->
            <div v-if="showDetails" class="mt-3 space-y-2 text-xs">
              <label class="flex items-center gap-2">
                <input type="checkbox" checked disabled class="rounded opacity-60" />
                <span><strong>Nécessaires</strong> — Authentification JWT, session. Toujours actifs.</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="prefs.analytics" type="checkbox" class="rounded" />
                <span><strong>Analytiques</strong> — Mesure d'audience anonyme.</span>
              </label>
              <label class="flex items-center gap-2 cursor-pointer">
                <input v-model="prefs.marketing" type="checkbox" class="rounded" />
                <span><strong>Marketing</strong> — Contenu personnalisé.</span>
              </label>
            </div>
          </div>

          <div class="flex flex-wrap gap-2 shrink-0">
            <button
              @click="rejectAll"
              class="btn-outline text-xs px-4 py-2"
            >
              Refuser tout
            </button>
            <button
              v-if="showDetails"
              @click="save"
              class="px-4 py-2 text-xs font-semibold border border-indigo-300 text-indigo-700 rounded-lg hover:bg-indigo-50 transition"
            >
              Enregistrer mes choix
            </button>
            <button
              @click="acceptAll"
              class="btn-primary text-xs px-4 py-2"
            >
              Tout accepter
            </button>
          </div>
        </div>

        <p class="text-xs text-muted mt-3">
          Voir notre
          <router-link to="/privacy" class="text-indigo-600 hover:underline">politique de confidentialité</router-link>
        </p>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); }
</style>
