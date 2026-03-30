import { Router } from "express"
import { authMiddleware } from "../middlewares/auth.middleware.js"
import { roleMiddleware } from "../middlewares/role.middleware.js"

const router = Router()

// accessible à tout utilisateur connecté
router.get("/private", authMiddleware, (req, res) => {
  res.json({ message: "Private OK", user: req.user })
})

// accessible uniquement admin
router.get("/admin-only", authMiddleware, roleMiddleware(["admin"]), (req, res) => {
  res.json({ message: "Admin OK", user: req.user })
})

export default router