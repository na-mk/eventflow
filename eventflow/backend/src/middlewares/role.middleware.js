export const roleMiddleware = (allowedRoles = []) => {
  return (req, res, next) => {
    // authMiddleware doit être appelé avant
    if (!req.user) {
      return res.status(401).json({ message: "Unauthorized" })
    }

    if (!allowedRoles.includes(req.user.role)) {
      return res.status(403).json({ message: "Forbidden: insufficient role" })
    }

    next()
  }
}