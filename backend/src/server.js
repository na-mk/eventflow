import express from "express"
import cors from "cors"
import mongoose from "mongoose"
import eventRoutes from "./routes/event.routes.js"

const app = express()

// Middlewares
app.use(cors())
app.use(express.json())

// ✅ Routes
app.use("/api", eventRoutes)

// ✅ Route test (pour vérifier que le serveur répond)
app.get("/", (req, res) => {
  res.send("✅ EVENTFLOW API is running")
})

// ✅ MongoDB (si tu as déjà une connexion ailleurs, garde la tienne)
const MONGO_URI = process.env.MONGO_URI || "mongodb://127.0.0.1:27017/eventflow"
mongoose
  .connect(MONGO_URI)
  .then(() => console.log("✅ MongoDB connected"))
  .catch((err) => console.error("❌ MongoDB connection error:", err))

// ✅ Port
const PORT = process.env.PORT || 4000
app.listen(PORT, () => {
  console.log(`✅ Backend running on http://localhost:${PORT}`)
})