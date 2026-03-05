<script setup>
import { reactive } from "vue"
import { useRouter } from "vue-router"
import { useUserStore } from "../stores/user"

const userStore = useUserStore()
const router = useRouter()

const form = reactive({
  name: "",
  email: "",
  password: "",
  role: "participant",
})

async function submit() {
  await userStore.register(form)
  if (!userStore.error) router.push("/")
}
</script>

<template>
  <div class="max-w-md mx-auto">
    <div class="mb-6">
      <h2 class="text-xl font-extrabold tracking-tight">Créer un compte</h2>
      <p class="text-sm text-slate-500">Rejoignez EVENTFLOW en quelques secondes.</p>
    </div>

    <div v-if="userStore.error" class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
      {{ userStore.error }}
    </div>

    <form @submit.prevent="submit" class="bg-white border rounded-2xl p-6 shadow-sm space-y-4">
      <div>
        <label class="text-sm font-medium text-slate-800">Nom</label>
        <input
          v-model="form.name"
          class="input"
          placeholder="Ex: Moustapha"
          autocomplete="name"
        />
      </div>

      <div>
        <label class="text-sm font-medium text-slate-800">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="input"
          placeholder="email@exemple.com"
          autocomplete="email"
        />
      </div>

      <div>
        <label class="text-sm font-medium text-slate-800">Mot de passe</label>
        <input
          v-model="form.password"
          type="password"
          class="input"
          placeholder="Minimum 6 caractères"
          autocomplete="new-password"
        />
      </div>

      <div>
        <label class="text-sm font-medium text-slate-800">Rôle</label>
        <select v-model="form.role" class="input">
          <option value="participant">participant</option>
          <option value="organizer">organizer</option>
          <option value="admin">admin</option>
        </select>
        <p class="mt-1 text-xs text-slate-500">
          Astuce : garde “participant” par défaut et crée un admin depuis la DB si besoin.
        </p>
      </div>

      <button
        type="submit"
        :disabled="userStore.loading"
        class="w-full px-4 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold
               hover:bg-indigo-500 transition disabled:opacity-60"
      >
        {{ userStore.loading ? "Création..." : "Créer le compte" }}
      </button>

      <p class="text-sm text-slate-600">
        Déjà un compte ?
        <router-link to="/login" class="text-indigo-600 font-semibold hover:underline">
          Se connecter
        </router-link>
      </p>
    </form>
  </div>
</template>