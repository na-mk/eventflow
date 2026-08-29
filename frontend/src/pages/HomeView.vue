<script setup>
import { computed, onMounted } from 'vue'
import { useEventsStore } from '../stores/events'
import { useAuthStore } from '../stores/auth'

const eventsStore = useEventsStore()
const authStore = useAuthStore()

onMounted(() => {
  if (!eventsStore.events.length) {
    eventsStore.fetchEvents()
  }
})

const featuredEvents = computed(() => eventsStore.events.slice(0, 3))

function formatDate(value) {
  if (!value) return 'Date à confirmer'
  return new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value))
}

function formatRange(start, end) {
  if (!start) return 'Date à confirmer'
  if (!end) return formatDate(start)

  const startDate = new Date(start)
  const endDate = new Date(end)
  const sameDay = startDate.toDateString() === endDate.toDateString()

  if (sameDay) {
    return `${formatDate(start)} - ${new Intl.DateTimeFormat('fr-FR', { timeStyle: 'short' }).format(endDate)}`
  }

  return `${formatDate(start)} - ${formatDate(end)}`
}
</script>

<template>
  <section class="space-y-20 py-10">
    <section>
      <div class="mb-7 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <div class="section-label mb-2">À découvrir</div>
          <h2 class="text-3xl md:text-4xl font-extrabold text-main">Votre prochain rendez-vous commence ici</h2>
          <p class="mt-3 max-w-2xl text-sm md:text-base leading-7 text-sub">
            Parcourez les événements récemment publiés et trouvez celui qui vous donne envie de participer.
          </p>
        </div>
        <router-link to="/events" class="btn-ghost">Voir tous les événements</router-link>
      </div>

      <div v-if="eventsStore.loading" class="grid gap-5 md:grid-cols-3">
        <div v-for="i in 3" :key="i" class="ef-card animate-pulse p-6">
          <div class="h-5 w-28 rounded" style="background:var(--border)"></div>
          <div class="mt-4 h-4 rounded" style="background:var(--border-light)"></div>
          <div class="mt-2 h-4 w-2/3 rounded" style="background:var(--border-light)"></div>
        </div>
      </div>

      <div v-else-if="featuredEvents.length" class="grid gap-5 md:grid-cols-3">
        <article v-for="event in featuredEvents" :key="event.id" class="ef-card overflow-hidden flex flex-col">
          <div class="h-2 bg-gradient-to-r from-orange-500 to-amber-400"></div>
          <div class="p-6 flex flex-col flex-1">
            <div class="text-xs font-semibold text-orange-500">{{ formatRange(event.eventDate, event.endDate) }}</div>
            <h3 class="mt-3 text-xl font-extrabold text-main">{{ event.title }}</h3>
            <p class="mt-3 line-clamp-3 text-sm leading-6 text-sub">{{ event.description }}</p>
            <div class="mt-5 flex items-center gap-2 text-sm text-muted">
              <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              {{ event.location || 'Lieu à confirmer' }}
            </div>
            <router-link :to="`/events/${event.id}`" class="btn-outline mt-6 self-start">Découvrir</router-link>
          </div>
        </article>
      </div>

      <div v-else class="glass p-8 text-center">
        <h3 class="text-xl font-extrabold text-main">Les prochains événements arrivent bientôt</h3>
        <p class="mt-2 text-sm text-sub">Revenez prochainement ou créez le premier événement de la communauté.</p>
        <router-link v-if="authStore.isOrganizer" to="/events/create" class="btn-primary mt-5">Créer un événement</router-link>
        <router-link v-else to="/register" class="btn-primary mt-5">Rejoindre EventFlow</router-link>
      </div>
    </section>

    <section class="rounded-[2rem] border p-8 md:p-12" style="border-color:var(--border);background:var(--bg-card)">
      <div class="max-w-2xl">
        <div class="section-label mb-2">Simple au quotidien</div>
        <h2 class="text-3xl md:text-4xl font-extrabold text-main">Comment ça marche ?</h2>
        <p class="mt-3 text-sub leading-7">Quelques étapes suffisent pour passer de la découverte à la participation.</p>
      </div>

      <div class="mt-10 grid gap-5 md:grid-cols-3">
        <article class="rounded-2xl border p-6" style="border-color:var(--border);background:var(--bg-base)">
          <div class="h-10 w-10 rounded-full bg-orange-500 text-white grid place-items-center font-black">1</div>
          <h3 class="mt-5 text-xl font-extrabold text-main">Découvrez</h3>
          <p class="mt-2 text-sm leading-6 text-sub">Parcourez les événements disponibles selon vos centres d'intérêt et vos disponibilités.</p>
        </article>
        <article class="rounded-2xl border p-6" style="border-color:var(--border);background:var(--bg-base)">
          <div class="h-10 w-10 rounded-full bg-orange-500 text-white grid place-items-center font-black">2</div>
          <h3 class="mt-5 text-xl font-extrabold text-main">Inscrivez-vous</h3>
          <p class="mt-2 text-sm leading-6 text-sub">Réservez votre place en quelques secondes et retrouvez votre inscription dans votre espace.</p>
        </article>
        <article class="rounded-2xl border p-6" style="border-color:var(--border);background:var(--bg-base)">
          <div class="h-10 w-10 rounded-full bg-orange-500 text-white grid place-items-center font-black">3</div>
          <h3 class="mt-5 text-xl font-extrabold text-main">Participez</h3>
          <p class="mt-2 text-sm leading-6 text-sub">Consultez les informations utiles et concentrez-vous sur l'essentiel : vivre l'événement.</p>
        </article>
      </div>
    </section>

    <section class="relative overflow-hidden rounded-[2rem] border px-8 py-12 md:px-12 md:py-14"
      style="border-color:rgba(249,115,22,0.22);background:linear-gradient(135deg, rgba(249,115,22,0.10), var(--bg-card))">
      <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-orange-500/10 blur-3xl"></div>
      <div class="relative flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-2xl">
          <div class="section-label mb-2">Pour les organisateurs</div>
          <h2 class="text-3xl md:text-4xl font-extrabold text-main">Vous organisez un événement ?</h2>
          <p class="mt-4 text-base leading-7 text-sub">
            Créez votre événement, publiez-le et suivez vos inscriptions simplement depuis EventFlow.
          </p>
        </div>
        <router-link v-if="authStore.isOrganizer" to="/events/create" class="btn-primary px-7 py-3.5 rounded-xl shrink-0">Créer un événement</router-link>
        <router-link v-else to="/register" class="btn-primary px-7 py-3.5 rounded-xl shrink-0">Devenir organisateur</router-link>
      </div>
    </section>
  </section>
</template>