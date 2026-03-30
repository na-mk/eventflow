import Event from "../models/Event.js"

// GET /api/events
export const getEvents = async (req, res) => {
  try {
    const events = await Event.find().sort({ date: 1 })
    return res.json(events)
  } catch (err) {
    return res.status(500).json({ message: "Erreur chargement events" })
  }
}

// POST /api/events
export const createEvent = async (req, res) => {
  try {
    const created = await Event.create(req.body)
    return res.status(201).json(created)
  } catch (err) {
    return res.status(400).json({ message: "Erreur création event" })
  }
}

// DELETE /api/events/:id
export const deleteEvent = async (req, res) => {
  try {
    const deleted = await Event.findByIdAndDelete(req.params.id)
    if (!deleted) return res.status(404).json({ message: "Événement introuvable" })
    return res.json({ message: "Événement supprimé" })
  } catch (err) {
    return res.status(500).json({ message: "Erreur suppression" })
  }
}