# EventFlow

Plateforme de gestion d'événements professionnels construite avec une API REST Symfony et une SPA Vue.js.

Stack principale :
- Symfony 6.4
- Vue.js 3
- Vite
- Pinia
- MySQL 8
- JWT
- RGPD natif

## Structure

```text
eventflow/
├── backend/   # API Symfony 6.4 + Doctrine + JWT
└── frontend/  # SPA Vue 3 + Vite + Pinia
```

Le frontend communique exclusivement avec le backend via l'API REST JSON.

## Prérequis

| Outil | Version conseillée |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 20 LTS |
| npm | 9+ |
| MySQL | 8.0+ |

## Installation

### 1. Cloner le dépôt

```bash
git clone https://github.com/na-mk/eventflow.git
cd eventflow
```

### 2. Configurer le backend

```bash
cd backend
composer install
```

Copier le fichier d'exemple :

```bash
cp .env.example .env
```

Configurer au minimum :
- `DATABASE_URL`
- `JWT_SECRET_KEY`
- `JWT_PUBLIC_KEY`
- `JWT_PASSPHRASE`

Exemple MySQL local :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/eventflow?serverVersion=8.0&charset=utf8mb4"
```

### 3. Générer les clés JWT

Depuis `backend/` :

```bash
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

### 4. Créer la base et lancer les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Configurer le frontend

Depuis la racine du projet :

```bash
cd frontend
npm install
cp .env.example .env
```

Le frontend attend par défaut :

```env
VITE_API_URL=http://localhost:8000/api
```

## Lancement

### Backend

Depuis `backend/` :

```bash
php -S 127.0.0.1:8000 -t public
```

### Frontend

Depuis `frontend/` :

```bash
npm run dev -- --host 127.0.0.1 --port 5173
```

Application disponible sur :
- Frontend : `http://127.0.0.1:5173`
- API : `http://127.0.0.1:8000/api`

## Variables d'environnement

### Backend

Le fichier `backend/.env.example` fournit les variables minimales :
- `APP_ENV`
- `APP_SECRET`
- `DATABASE_URL`
- `JWT_SECRET_KEY`
- `JWT_PUBLIC_KEY`
- `JWT_PASSPHRASE`
- `JWT_TTL`
- `CORS_ALLOW_ORIGIN`

### Frontend

Le fichier `frontend/.env.example` fournit :
- `VITE_API_URL`

## Fonctionnalités principales

### Backend API

- inscription avec consentement RGPD
- authentification JWT
- CRUD événements
- publication / dépublication d'événements
- inscription à un événement
- annulation d'inscription
- gestion du profil utilisateur
- rectification et anonymisation
- export des données personnelles
- mise à jour du consentement
- commande d'anonymisation automatique des comptes inactifs

### Frontend SPA

- pages publiques : accueil, liste des événements, détail, confidentialité
- authentification et inscription
- dashboard utilisateur / organisateur
- espace administrateur
- création et édition d'événements
- page "Mes données"
- bandeau cookies
- feedback utilisateur sur les actions clés

## Endpoints principaux

| Méthode | Endpoint | Accès | Description |
|---|---|---|---|
| POST | `/api/auth/register` | Public | Inscription + consentement |
| POST | `/api/auth/login` | Public | Authentification JWT |
| GET | `/api/events` | Public | Liste des événements publiés |
| GET | `/api/events/{id}` | Public | Détail d'un événement |
| POST | `/api/events` | Organizer/Admin | Créer un événement |
| PUT | `/api/events/{id}` | Organizer owner/Admin | Modifier un événement |
| DELETE | `/api/events/{id}` | Organizer owner/Admin | Supprimer un événement |
| PATCH | `/api/events/{id}/publish` | Organizer owner/Admin | Publier ou dépublier |
| POST | `/api/events/{id}/register` | Auth | S'inscrire |
| DELETE | `/api/registrations/{id}` | Auth | Annuler son inscription |
| GET | `/api/registrations/my` | Auth | Voir ses inscriptions |
| GET | `/api/me` | Auth | Voir son profil |
| PUT | `/api/me` | Auth | Rectifier ses données |
| DELETE | `/api/me` | Auth | Anonymiser son compte |
| GET | `/api/me/export` | Auth | Exporter ses données |
| POST | `/api/consent` | Auth | Mettre à jour le consentement |

## Commandes utiles

Depuis `backend/` :

```bash
php bin/console debug:router
php bin/console doctrine:migrations:status
php bin/console app:anonymize-old-users
php bin/console app:anonymize-old-users --months=12
php bin/phpunit
```

Depuis `frontend/` :

```bash
npm run build
```

## Docker

Une stack Docker est fournie à la racine du projet :
- `mysql`
- `backend`
- `frontend`
- `nginx`

Lancement :

```bash
docker compose up --build
```

Accès :
- application via `http://localhost`
- API via `http://localhost/api`

## CI

Une GitHub Action est fournie dans `.github/workflows/backend-tests.yml`.

Elle exécute automatiquement :
- installation des dépendances backend
- génération d'une paire JWT
- `php bin/phpunit`

## Déploiement sur Render

Une configuration Render Blueprint est fournie dans `render.yaml`.

### Services Render recommandés

- `eventflow-api` : Web Service Docker pour Symfony
- `eventflow-front` : Static Site pour Vue/Vite
- la base PostgreSQL 16 déclarée dans le Blueprint `render.yaml` (le développement local reste basé sur MySQL 8)

### Variables à définir sur Render

Pour `eventflow-api` :
- `DATABASE_URL`
- `JWT_PRIVATE_KEY_B64`
- `JWT_PUBLIC_KEY_B64`
- `CORS_ALLOW_ORIGIN`

Pour `eventflow-front` :
- `VITE_API_URL`

### Valeurs conseillées

`DATABASE_URL` est fournie automatiquement par la base PostgreSQL déclarée dans `render.yaml`. Pour un déploiement MySQL distinct, utiliser :

```env
mysql://USER:PASSWORD@MYSQL_HOST:3306/eventflow?serverVersion=8.0&charset=utf8mb4
```

`CORS_ALLOW_ORIGIN` :

```env
^https://.*onrender\.com$
```

`VITE_API_URL` :

```env
https://eventflow-api.onrender.com/api
```

### JWT sur Render

Le backend Render attend les clés JWT au format Base64 dans :
- `JWT_PRIVATE_KEY_B64`
- `JWT_PUBLIC_KEY_B64`

Tu peux générer ces valeurs localement avec :

```bash
base64 -w 0 backend/config/jwt/private.pem
base64 -w 0 backend/config/jwt/public.pem
```

Sur PowerShell :

```powershell
[Convert]::ToBase64String([IO.File]::ReadAllBytes("backend/config/jwt/private.pem"))
[Convert]::ToBase64String([IO.File]::ReadAllBytes("backend/config/jwt/public.pem"))
```

### Déploiement

1. Push ton dépôt sur GitHub.
2. Sur Render, crée un nouveau Blueprint depuis ce repo.
3. Fournis les variables manquantes demandées.
4. Vérifie que la base PostgreSQL du Blueprint est créée.
5. Vérifie que `DATABASE_URL` est injectée automatiquement depuis `eventflow-db`.
6. Déploie d'abord `eventflow-api`, puis `eventflow-front`.

Références officielles Render :
- Blueprints : https://render.com/docs/infrastructure-as-code
- Web services : https://render.com/docs/web-services
- Static sites : https://render.com/docs/static-sites
- MySQL : https://render.com/docs/deploy-mysql

## Tests et livrables

Le projet contient :
- une suite PHPUnit backend
- un fichier de requêtes HTTP exécutable : `eventflow.http`
- un registre RGPD : `REGISTRE_TRAITEMENTS_RGPD.md`

### Lancer les tests backend

```bash
cd backend
php bin/phpunit
```

## RGPD

Les points pris en charge dans l'application :
- consentement explicite à l'inscription
- journalisation des actions liées aux données personnelles
- IP stockée sous forme de hash SHA-256 dans `ConsentLog`
- rectification des données
- anonymisation manuelle
- anonymisation automatique des comptes inactifs
- export des données personnelles
- bandeau cookies granulaire
- politique de confidentialité

## Comptes utiles en local

Compte admin local utilisé pendant le développement :
- email : `admin@eventflow.local`
- mot de passe : `Admin1234!`

## Remarques

- Ne pas commiter les fichiers `.env`
- Ne pas commiter les clés JWT privées
- En production, planifier `app:anonymize-old-users` via cron
- Le frontend suppose que l'API répond sur le port `8000`

## Auteur

Anna Merveille KAYA
