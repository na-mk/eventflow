import express from "express"
import cors from "cors"
import dotenv from "dotenv"
import { connectDB } from "./config/db.js"
import authRoutes from "./routes/auth.routes.js"
import testRoutes from "./routes/test.routes.js"
import rbacTestRoutes from "./routes/rbac-test.routes.js"
import eventRoutes from "./routes/event.routes.js"

dotenv.config()

const app = express()

app.use(cors())
app.use(express.json())

connectDB()

app.use("/api/auth", authRoutes)
app.use("/api/rbac", rbacTestRoutes)
app.use("/api/events", eventRoutes)
app.use("/api/test", testRoutes)

app.get("/api/health", (req, res) => {
  res.json({
    status: "ok",
    service: "eventflow-backend"
  })
})

const PORT = process.env.PORT || 4000

app.listen(PORT, () => {
  console.log(`Backend running on http://localhost:${PORT}`)
})