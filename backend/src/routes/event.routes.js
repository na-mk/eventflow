import { Router } from "express"

import { getEvents, createEvent } from "../controllers/event.controller.js"

import { authMiddleware } from "../middlewares/auth.middleware.js"
import { roleMiddleware } from "../middlewares/role.middleware.js"

const router = Router()

// voir les événements
router.get("/", getEvents)

// créer un événement
router.post(
  "/",
  authMiddleware,
  roleMiddleware(["organizer", "admin"]),
  createEvent
)

export default router