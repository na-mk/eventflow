import express from "express"
import { getEvents, createEvent, deleteEvent } from "../controllers/event.controller.js"

const router = express.Router()

// URL finale: /api/events
router.get("/events", getEvents)
router.post("/events", createEvent)
router.delete("/events/:id", deleteEvent)

export default router