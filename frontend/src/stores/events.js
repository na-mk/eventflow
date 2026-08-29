import { defineStore } from 'pinia'
import { api } from '../services/api'

export const useEventsStore = defineStore('events', {
  state: () => ({
    events: [],
    myRegistrations: [],
    participantsByEvent: {},
    loading: false,
    error: null
  }),

  getters: {
    isRegisteredToEvent: (state) => (eventId) => state.myRegistrations.some(
      (registration) => registration.status === 'confirmed' && Number(registration.event?.id) === Number(eventId)
    )
  },

  actions: {
    async fetchEventById(id) {
      const res = await api.get(`/events/${id}`)
      return res.data
    },

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
      const activeRegistration = this.myRegistrations.find(
        (registration) => registration.status === 'confirmed' && Number(registration.event?.id) === Number(eventId)
      )
      if (activeRegistration) return activeRegistration

      try {
        const res = await api.post(`/events/${eventId}/register`)
        const registration = res.data
        const existingIndex = this.myRegistrations.findIndex(
          (item) => Number(item.event?.id) === Number(eventId)
        )

        if (existingIndex === -1) {
          this.myRegistrations.push(registration)
        } else {
          this.myRegistrations[existingIndex] = registration
        }

        const eventIndex = this.events.findIndex((event) => Number(event.id) === Number(eventId))
        if (eventIndex !== -1) {
          const event = this.events[eventIndex]
          const remainingPlaces = Math.max(0, Number(event.remainingPlaces) - 1)
          this.events[eventIndex] = {
            ...event,
            participantsCount: Math.max(0, Number(event.maxParticipants) - remainingPlaces),
            remainingPlaces,
            isFull: remainingPlaces === 0,
          }
        }

        return registration
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur inscription'
        throw err
      }
    },

    async cancelRegistration(registrationId) {
      try {
        const res = await api.delete(`/registrations/${registrationId}`)
        this.myRegistrations = this.myRegistrations.filter((registration) => registration.id !== registrationId)
        return res.data
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur annulation inscription'
        throw err
      }
    },

    async fetchMyRegistrations() {
      try {
        const res = await api.get('/registrations/my')
        this.myRegistrations = res.data
        return res.data
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur chargement inscriptions'
        return []
      }
    },

    async fetchEventParticipants(eventId) {
      try {
        const res = await api.get(`/events/${eventId}/participants`)
        this.participantsByEvent[eventId] = res.data
        return res.data
      } catch (err) {
        this.error = err?.response?.data?.message || 'Erreur chargement participants'
        throw err
      }
    }
  }
})
