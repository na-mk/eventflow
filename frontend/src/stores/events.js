import { defineStore } from 'pinia'

export const useEventsStore = defineStore('events', {
  state: () => ({
    events: [
      {
        id: '1',
        title: 'Vue.js Meetup',
        description: 'Découverte de Vue 3 et bonnes pratiques.',
        date: '2026-03-10',
        location: 'Paris',
        capacity: 50,
        registered: 12,
        createdBy: 'organizer'
      },
      {
        id: '2',
        title: 'Conf Full Stack',
        description: 'API Node + Front Vue + Docker.',
        date: '2026-03-18',
        location: 'Lyon',
        capacity: 100,
        registered: 100,
        createdBy: 'organizer'
      },
      {
        id: '3',
        title: 'Séminaire DevOps',
        description: 'Docker compose, CI/CD, bonnes pratiques.',
        date: '2026-04-02',
        location: 'Marseille',
        capacity: 30,
        registered: 5,
        createdBy: 'organizer'
      }
    ],

    filters: {
      q: '',
      location: 'all',
      onlyAvailable: false
    }
  }),

  getters: {
    locations(state) {
      const set = new Set(state.events.map(e => e.location))
      return ['all', ...Array.from(set)]
    },

    filteredEvents(state) {
      const q = state.filters.q.toLowerCase().trim()
      return state.events.filter((e) => {
        const matchesText =
          !q ||
          e.title.toLowerCase().includes(q) ||
          e.description.toLowerCase().includes(q)

        const matchesLocation =
          state.filters.location === 'all' || e.location === state.filters.location

        const remaining = e.capacity - e.registered
        const matchesAvailability =
          !state.filters.onlyAvailable || remaining > 0

        return matchesText && matchesLocation && matchesAvailability
      })
    }
  },

  actions: {

  createEvent(eventData) {
    const newEvent = {
      id: Date.now().toString(),
      ...eventData,
      registered: 0
    }

    this.events.push(newEvent)
  },

  registerOneClick(eventId) {
    const ev = this.events.find(e => e.id === eventId)
    if (!ev) return

    const remaining = ev.capacity - ev.registered
    if (remaining <= 0) {
      alert('Événement complet')
      return
    }

    ev.registered += 1
  }
}
})