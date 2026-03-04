import { Event } from "../models/Event.js"

// GET all events
export const getEvents = async (req, res) => {

  try {

    const events = await Event.find().sort({ date: 1 })

    res.json(events)

  } catch (error) {

    res.status(500).json({ message: "Erreur récupération events" })

  }

}


// CREATE event (organizer ou admin)
export const createEvent = async (req, res) => {

  try {

    const { title, description, date, location } = req.body

    const event = await Event.create({
      title,
      description,
      date,
      location,
      createdBy: req.user.id
    })

    res.status(201).json(event)

  } catch (error) {

    res.status(500).json({ message: "Erreur création event" })

  }

}