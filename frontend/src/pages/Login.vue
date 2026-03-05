<script setup>
import { reactive } from "vue"
import { useRouter } from "vue-router"
import { useUserStore } from "../stores/user"

const userStore = useUserStore()
const router = useRouter()

const form = reactive({
  email: "",
  password: "",
})

async function submit() {
  await userStore.login(form)
  if (!userStore.error) router.push("/")
}
</script>

<template>
  <div class="max-w-md mx-auto">
    <div class="mb-6">
      <h2 class="text-xl font-extrabold tracking-tight">Connexion</h2>
      <p class="text-sm text-slate-500">Accédez à votre espace EVENTFLOW.</p>
    </div>

    <div v-if="userStore.error" class="mb-4 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
      {{ userStore.error }}
    </div>

    <form @submit.prevent="submit" class="bg-white border rounded-2xl p-6 shadow-sm space-y-4">
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
          placeholder="••••••••"
          autocomplete="current-password"
        />
      </div>

      <button
        type="submit"
        :disabled="userStore.loading"
        class="w-full px-4 py-2.5 rounded-xl bg-slate-900 text-white font-semibold
               hover:bg-slate-800 transition disabled:opacity-60"
      >
        {{ userStore.loading ? "Connexion..." : "Se connecter" }}
      </button>

      <p class="text-sm text-slate-600">
        Pas de compte ?
        <router-link to="/register" class="text-indigo-600 font-semibold hover:underline">
          Créer un compte
        </router-link>
      </p>
    </form>
  </div>
</template>