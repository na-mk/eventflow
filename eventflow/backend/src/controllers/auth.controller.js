import bcrypt from "bcrypt"
import jwt from "jsonwebtoken"
import { User } from "../models/User.js"

const signToken = (user) => {
  return jwt.sign(
    { userId: user._id.toString(), role: user.role },
    process.env.JWT_SECRET,
    { expiresIn: "7d" }
  )
}

export const register = async (req, res) => {
  try {
    const { name, email, password, role } = req.body

    if (!name || !email || !password) {
      return res.status(400).json({ message: "name, email, password are required" })
    }

    const existing = await User.findOne({ email })
    if (existing) {
      return res.status(409).json({ message: "Email already used" })
    }

    const passwordHash = await bcrypt.hash(password, 10)

    const user = await User.create({
      name,
      email,
      passwordHash,
      role: role || "participant"
    })

    const token = signToken(user)

    return res.status(201).json({
      token,
      user: { id: user._id, name: user.name, email: user.email, role: user.role }
    })
  } catch (err) {
    return res.status(500).json({ message: "Server error", error: err.message })
  }
}

export const login = async (req, res) => {
  try {
    const { email, password } = req.body

    if (!email || !password) {
      return res.status(400).json({ message: "email, password are required" })
    }

    const user = await User.findOne({ email })
    if (!user) {
      return res.status(401).json({ message: "Invalid credentials" })
    }

    const ok = await bcrypt.compare(password, user.passwordHash)
    if (!ok) {
      return res.status(401).json({ message: "Invalid credentials" })
    }

    const token = signToken(user)

    return res.json({
      token,
      user: { id: user._id, name: user.name, email: user.email, role: user.role }
    })
  } catch (err) {
    return res.status(500).json({ message: "Server error", error: err.message })
  }
}

export const me = async (req, res) => {
  // authMiddleware a mis req.user
  return res.json({ user: req.user })
}