import mongoose from "mongoose"

const eventSchema = new mongoose.Schema(
  {
    title: { type: String, required: true, trim: true },

    description: { type: String, default: "" },

    date: { type: Date, required: true },

    location: { type: String, required: true, trim: true },

    capacity: { type: Number, required: true, min: 1 },

    participants: [{ type: mongoose.Schema.Types.ObjectId, ref: "User" }],

    createdBy: { type: mongoose.Schema.Types.ObjectId, ref: "User", required: true }
  },
  { timestamps: true }
)

export const Event = mongoose.model("Event", eventSchema)