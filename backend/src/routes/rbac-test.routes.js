import { Router } from "express"
import { authMiddleware } from "../middlewares/auth.middleware.js"
import { roleMiddleware } from "../middlewares/role.middleware.js"

const router = Router()

// Toute personne connectée
router.get("/private", authMiddleware, (req, res) => {
  res.json({ message: "Private OK", user: req.user })
})

// Admin uniquement
router.get("/admin", authMiddleware, roleMiddleware(["admin"]), (req, res) => {
  res.json({ message: "Admin OK", user: req.user })
})

// Organizer ou Admin
router.get("/organizer", authMiddleware, roleMiddleware(["organizer", "admin"]), (req, res) => {
  res.json({ message: "Organizer/Admin OK", user: req.user })
})

export default router