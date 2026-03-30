import express from "express"
import cors from "cors"
import mongoose from "mongoose"
import dotenv from "dotenv"
import eventRoutes from "./routes/event.routes.js"

dotenv.config()

const app = express()

// Middlewares
app.use(cors({ origin: true }))
app.use(express.json())

// Routes
app.use("/api", eventRoutes)

// Route test
app.get("/", (req, res) => {
  res.send("✅ EVENTFLOW API is running")
})

// MongoDB
const MONGO_URI = process.env.MONGO_URI
if (!MONGO_URI) {
  console.error("❌ MONGO_URI is missing. Check backend/.env")
  process.exit(1)
}

mongoose
  .connect(MONGO_URI)
  .then(() => console.log("✅ MongoDB connected"))
  .catch((err) => console.error("❌ MongoDB connection error:", err))

// Server
const PORT = process.env.PORT || 4000
app.listen(PORT, () => {
  console.log(`✅ Backend running on http://localhost:${PORT}`)
})