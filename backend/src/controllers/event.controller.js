import { Event } from "../models/Event.js"


// ==============================
// GET ALL EVENTS
// ==============================
export const getEvents = async (req, res) => {
  try {

    const events = await Event.find().sort({ date: 1 })

    res.json(events)

  } catch (error) {

    console.error("getEvents error:", error)

    res.status(500).json({
      message: "Erreur récupération events",
      error: error.message
    })

  }
}



// ==============================
// CREATE EVENT
// ==============================
export const createEvent = async (req, res) => {

  try {

    const { title, description, date, location, capacity } = req.body

    if (!title || !date || !location || !capacity) {
      return res.status(400).json({
        message: "title, date, location, capacity are required"
      })
    }

    const event = await Event.create({

      title,
      description: description || "",
      date,
      location,
      capacity: Number(capacity),

      createdBy: req.user.id

    })

    res.status(201).json(event)

  } catch (error) {

    console.error("createEvent error:", error)

    res.status(500).json({
      message: "Erreur création event",
      error: error.message
    })

  }

}