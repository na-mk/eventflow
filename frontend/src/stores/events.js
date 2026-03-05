import { defineStore } from 'pinia'
import { api } from '../services/api'

export const useEventsStore = defineStore('events', {

  state: () => ({
    events: [],
    loading: false,
    error: null
  }),

  actions: {

    async fetchEvents() {

      this.loading = true
      this.error = null

      try {

        const res = await api.get('/events')
        this.events = res.data

      } catch (err) {

        this.error = err?.response?.data?.message || "Erreur chargement events"

      } finally {

        this.loading = false

      }
    },

    async createEvent(eventData) {

      try {

        const res = await api.post('/events', eventData)
        this.events.push(res.data)

      } catch (err) {

        this.error = err?.response?.data?.message || "Erreur création event"

      }

    },
    async deleteEvent(id) {
  try {
    await api.delete(`/events/${id}`)
    this.events = this.events.filter(e => e._id !== id)
  } catch (err) {
    this.error = err.response?.data?.message || "Erreur suppression"
  }
}

  }
  

})