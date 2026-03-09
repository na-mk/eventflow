# EventFlow

EventFlow is a full-stack web application for managing events.  
Users can create, view, and manage events through a modern interface with authentication and role-based access control.

---

## Features

- User registration and login (JWT authentication)
- Role-based access control (Admin / Organizer / Participant)
- Create and manage events
- View events dashboard
- Delete events
- MongoDB database storage
- RESTful API
- Modern UI with TailwindCSS

---

## Tech Stack

### Frontend
- Vue 3
- Vue Router
- Pinia
- TailwindCSS
- Axios
- Vite

### Backend
- Node.js
- Express.js
- MongoDB
- Mongoose
- JWT Authentication
- RBAC (Role-Based Access Control)

### Deployment
- Backend: Render
- Frontend: Vercel
- Database: MongoDB Atlas

---

## ⚙️ Installation

Clone the repository

```bash
git clone https://github.com/na-mk/eventflow.git
cd eventflow

Install backend
cd backend
npm install
npm run dev

Install frontend
cd frontend
npm install
npm run dev

🌐 API Endpoints
| Method | Endpoint        | Description  |
| ------ | --------------- | ------------ |
| GET    | /api/events     | Get events   |
| POST   | /api/events     | Create event |
| DELETE | /api/events/:id | Delete event |




👨‍💻 Author
Anna Merveille KAYA
