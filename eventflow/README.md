# EventFlow — Plateforme de Gestion d'Événements

Application web de gestion d'événements professionnels (conférences, meetups, formations).

**Stack :** Symfony 6.4 (API REST + JWT) · Vue.js 3 (SPA + Pinia) · MySQL 8 · RGPD natif

---

## Prérequis

| Outil | Version minimale |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Symfony CLI | 5.x |
| Node.js | 20 LTS |
| npm | 9+ |
| MySQL | 8.0+ |

---

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/na-mk/eventflow.git
cd eventflow
```

### 2. Backend Symfony

```bash
cd backend-symfony

# Copier et configurer les variables d'environnement
cp .env.example .env
# Éditer .env : DATABASE_URL avec vos identifiants MySQL

# Installer les dépendances
composer install

# Générer les clés JWT
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

# Créer la base de données et exécuter les migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate

# Lancer le serveur
symfony server:start
# ou : php -S localhost:8000 -t public/
```

Le backend est disponible sur **http://localhost:8000**

### 3. Frontend Vue.js

```bash
cd frontend

# Copier les variables d'environnement
cp .env.example .env

# Installer les dépendances
npm install

# Lancer le serveur de développement
npm run dev
```

Le frontend est disponible sur **http://localhost:5173**

---

## Architecture du projet

```
eventflow/
├── backend-symfony/          # API REST Symfony 6.4
│   ├── src/
│   │   ├── Controller/       # AuthController, EventController, RegistrationController, UserController
│   │   ├── Entity/           # User, Event, Registration, ConsentLog
│   │   ├── Repository/       # Repositories Doctrine
│   │   ├── Security/         # EventVoter (RBAC)
│   │   ├── Service/          # ConsentLogService
│   │   ├── EventSubscriber/  # JwtSubscriber (log connexions)
│   │   └── Command/          # AnonymizeOldUsersCommand (RGPD)
│   ├── migrations/           # Migrations Doctrine versionnées
│   └── config/
│       ├── jwt/              # Clés JWT (private.pem, public.pem)
│       └── packages/         # security.yaml, nelmio_cors.yaml
│
└── frontend/                 # SPA Vue.js 3
    └── src/
        ├── pages/            # Dashboard, Login, Register, CreateEvent, MyData, Privacy
        ├── components/       # EventCard, CookieBanner
        ├── stores/           # user.js, events.js (Pinia)
        ├── router/           # index.js (guards de navigation)
        └── services/         # api.js (Axios)
```

---

## API Endpoints

### Authentification
| Méthode | Endpoint | Accès | Description |
|---|---|---|---|
| POST | /api/auth/register | Public | Créer un compte (avec consentement RGPD) |
| POST | /api/auth/login | Public | Connexion → retourne JWT |
| GET | /api/auth/me | Auth | Profil de l'utilisateur connecté |

### Événements
| Méthode | Endpoint | Accès | Description |
|---|---|---|---|
| GET | /api/events | Public | Liste des événements publiés |
| GET | /api/events/all | Auth | Tous mes événements (organizer/admin) |
| GET | /api/events/{id} | Public | Détail d'un événement |
| POST | /api/events | Organizer/Admin | Créer un événement |
| PUT | /api/events/{id} | Owner/Admin | Modifier un événement |
| DELETE | /api/events/{id} | Owner/Admin | Supprimer un événement |
| PATCH | /api/events/{id}/publish | Owner/Admin | Publier/dépublier |

### Inscriptions
| Méthode | Endpoint | Accès | Description |
|---|---|---|---|
| POST | /api/registrations/{eventId} | Auth | S'inscrire à un événement |
| DELETE | /api/registrations/{eventId} | Auth | Se désinscrire |
| GET | /api/registrations/my | Auth | Mes inscriptions |

### Profil & RGPD
| Méthode | Endpoint | Accès | Description |
|---|---|---|---|
| GET | /api/me | Auth | Profil complet |
| PUT | /api/me | Auth | Rectifier mes données (Art.16) |
| DELETE | /api/me | Auth | Anonymiser mon compte (Art.17) |
| GET | /api/me/export | Auth | Exporter mes données (Art.20) |
| POST | /api/me/password | Auth | Changer de mot de passe |

---

## Commandes utiles

```bash
# Anonymiser les utilisateurs inactifs depuis 36 mois (RGPD)
php bin/console app:anonymize-old-users

# Anonymiser depuis 12 mois
php bin/console app:anonymize-old-users --months=12

# Build frontend
npm run build

# Vérifier les routes Symfony
php bin/console debug:router
```

---

## Rôles utilisateurs

| Rôle | Valeur | Droits |
|---|---|---|
| Participant | ROLE_USER | S'inscrire aux événements publiés |
| Organisateur | ROLE_ORGANIZER | Créer/modifier/publier ses événements |
| Administrateur | ROLE_ADMIN | Accès total |

---

## RGPD

- Consentement explicite à l'inscription (base légale Art. 6.1.a)
- Bandeau cookies granulaire (3 catégories : nécessaires, analytiques, marketing)
- Page "Mes données" : consultation, rectification, anonymisation, export
- Adresses IP stockées sous forme de hash SHA-256
- Log de tous les accès aux données personnelles (ConsentLog)
- Commande d'anonymisation automatique des comptes inactifs
- Politique de confidentialité accessible à /privacy

---

## Auteurs

Anna Merveille KAYA
