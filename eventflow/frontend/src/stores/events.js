import { defineStore } from 'pinia'
import { api } from '../services/api'

export const useEventsStore = defineStore('events', {
  state: () => ({
    events: [],
    myRegistrations: [],
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
        this.error = err?.response?.data?.message || 'Erreur chargement events'
      } finally {
        this.loading = false
      }
    },

    async fetchMyEvents() {
      this.loading = true
      try {
        const res = await api.get('/events/all')
        this.events = res.data
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur chargement events'
      } finally {
        this.loading = false
      }
    },

    async createEvent(eventData) {
      this.loading = true
      this.error = null
      try {
        const res = await api.post('/events', eventData)
        this.events.push(res.data)
        return res.data
      } catch (err) {
        this.error = err?.response?.data?.errors || err?.response?.data?.message || 'Erreur création event'
        throw err
      } finally {
        this.loading = false
      }
    },

    async updateEvent(id, eventData) {
      try {
        const res = await api.put(`/events/${id}`, eventData)
        const idx = this.events.findIndex(e => e.id === id)
        if (idx !== -1) this.events[idx] = res.data
        return res.data
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur mise à jour'
        throw err
      }
    },

    async deleteEvent(id) {
      try {
        await api.delete(`/events/${id}`)
        this.events = this.events.filter(e => e.id !== id)
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur suppression'
        throw err
      }
    },

    async togglePublish(id) {
      try {
        const res = await api.patch(`/events/${id}/publish`)
        const idx = this.events.findIndex(e => e.id === id)
        if (idx !== -1) this.events[idx] = res.data
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur publication'
        throw err
      }
    },

    async registerToEvent(eventId) {
      try {
        const res = await api.post(`/registrations/${eventId}`)
        return res.data
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur inscription'
        throw err
      }
    },

    async fetchMyRegistrations() {
      try {
        const res = await api.get('/registrations/my')
        this.myRegistrations = res.data
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur chargement inscriptions'
      }
    }
  }
})
