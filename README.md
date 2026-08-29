# EventFlow

EventFlow est une plateforme web de gestion d'événements permettant aux participants de découvrir et rejoindre des conférences, formations et meetups, et aux organisateurs de créer leurs événements, gérer les inscriptions et consulter leurs participants.

Le projet repose sur une architecture API-first avec un backend Symfony et une SPA Vue 3. Il intègre également les fonctionnalités RGPD demandées : consentement explicite, accès et rectification des données, export, anonymisation et journalisation des actions liées aux données personnelles.

## Application en ligne

- Frontend : https://eventflow-front-9961.onrender.com/
- API : https://eventflow-1-g30y.onrender.com/api
- Dépôt GitHub : https://github.com/na-mk/eventflow

> La branche de référence du projet est `cleanup-backend`.

## Stack technique

- PHP 8.2+
- Symfony 6.4
- Doctrine ORM
- Vue 3
- Vite
- Pinia
- Vue Router
- Axios
- JWT signé avec LexikJWTAuthenticationBundle
- MySQL 8 en environnement local
- PostgreSQL 16 sur Render
- Docker / Docker Compose
- GitHub Actions

## Structure

```text
eventflow/
├── backend/                    # API REST Symfony 6.4
├── frontend/                   # SPA Vue 3 + Vite + Pinia
├── .github/workflows/          # CI GitHub Actions
├── REGISTRE_TRAITEMENTS_RGPD.md
├── eventflow.http              # Requêtes API de démonstration
├── docker-compose.yml
├── render.yaml
└── README.md
```

Le frontend communique exclusivement avec le backend via une API REST JSON.

## Rôles

### Visiteur

- consulter les événements publiés ;
- consulter le détail d'un événement ;
- créer un compte participant ou organisateur.

### Participant

- s'inscrire à un événement ;
- annuler une inscription ;
- se réinscrire après annulation ;
- retrouver ses inscriptions dans **Mon espace** ;
- consulter, modifier, exporter ou anonymiser ses données personnelles.

### Organisateur

- créer, modifier, publier, dépublier et supprimer ses événements ;
- suivre le nombre d'inscrits ;
- consulter la liste des participants confirmés de ses propres événements ;
- disposer des mêmes droits RGPD qu'un participant.

### Administrateur

- disposer des autorisations étendues prévues côté backend.

L'inscription publique ne permet jamais de créer un compte administrateur.

## Fonctionnalités principales

### Gestion des événements

- création et modification d'événements ;
- publication / dépublication ;
- consultation publique des événements publiés ;
- protection des brouillons ;
- gestion de la capacité ;
- suivi des places restantes ;
- recherche et tri côté frontend.

### Inscriptions

- inscription à un événement ;
- prévention des doubles inscriptions actives ;
- annulation sans suppression de l'historique ;
- réactivation d'une inscription annulée ;
- confirmation visuelle après inscription ;
- état **Inscrit ✓** conservé après actualisation ;
- liste sécurisée des participants pour le propriétaire de l'événement ou un administrateur.

### Authentification et sécurité

- authentification JWT signée ;
- rôles participant, organisateur et administrateur ;
- contrôles d'accès backend ;
- Symfony Voter pour les autorisations liées aux événements ;
- blocage de l'authentification des comptes anonymisés ;
- secrets et clés JWT non versionnés.

### RGPD

- consentement explicite à l'inscription ;
- date et version du consentement ;
- journalisation via `ConsentLog` ;
- adresse IP stockée sous forme de hash SHA-256 ;
- droit d'accès ;
- rectification ;
- export des données personnelles ;
- anonymisation du compte ;
- commande d'anonymisation des comptes inactifs ;
- préférences de cookies ;
- politique de confidentialité ;
- registre des traitements fourni dans le dépôt.

## Principaux endpoints

| Méthode | Endpoint | Accès | Description |
|---|---|---|---|
| POST | `/api/auth/register` | Public | Créer un compte avec consentement |
| POST | `/api/auth/login` | Public | Se connecter et recevoir un JWT |
| GET | `/api/events` | Public | Lister les événements accessibles |
| GET | `/api/events/{id}` | Selon visibilité | Consulter un événement |
| POST | `/api/events` | Organisateur / Admin | Créer un événement |
| PUT | `/api/events/{id}` | Propriétaire / Admin | Modifier un événement |
| DELETE | `/api/events/{id}` | Propriétaire / Admin | Supprimer un événement |
| PATCH | `/api/events/{id}/publish` | Propriétaire / Admin | Publier ou dépublier |
| POST | `/api/events/{id}/register` | Authentifié | S'inscrire |
| GET | `/api/events/{id}/participants` | Propriétaire / Admin | Consulter les participants confirmés |
| DELETE | `/api/registrations/{id}` | Authentifié | Annuler une inscription |
| GET | `/api/registrations/my` | Authentifié | Consulter ses inscriptions |
| GET | `/api/me` | Authentifié | Consulter ses données |
| PUT | `/api/me` | Authentifié | Rectifier ses données |
| DELETE | `/api/me` | Authentifié | Anonymiser son compte |
| GET | `/api/me/export` | Authentifié | Exporter ses données |
| POST | `/api/consent` | Authentifié | Mettre à jour le consentement |

## Installation locale

### Prérequis

| Outil | Version |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 20+ |
| npm | 9+ |
| MySQL | 8.0+ |

### 1. Cloner le dépôt

```bash
git clone https://github.com/na-mk/eventflow.git
cd eventflow
git checkout cleanup-backend
```

### 2. Installer le backend

```bash
cd backend
cp .env.example .env
composer install
```

Configurer au minimum :

- `DATABASE_URL`
- `JWT_SECRET_KEY`
- `JWT_PUBLIC_KEY`
- `JWT_PASSPHRASE`
- `CORS_ALLOW_ORIGIN`

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

### 4. Créer la base et appliquer les migrations

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Installer le frontend

Depuis la racine du projet :

```bash
cd frontend
npm install
cp .env.example .env
```

Configuration locale :

```env
VITE_API_URL=http://localhost:8000/api
```

## Lancement local

### Backend

```bash
cd backend
php -S 127.0.0.1:8000 -t public
```

### Frontend

```bash
cd frontend
npm run dev -- --host 127.0.0.1 --port 5173
```

Accès local :

- Frontend : `http://127.0.0.1:5173`
- API : `http://127.0.0.1:8000/api`

## Tests

### Backend

```bash
cd backend
php bin/phpunit
```

Dernière validation connue :

```text
OK (24 tests, 88 assertions)
```

Autres commandes de contrôle :

```bash
php bin/console lint:container
php bin/console lint:yaml config
php bin/console doctrine:schema:validate --skip-sync
php bin/console debug:router
```

### Frontend

```bash
cd frontend
npm run build
```

Le build Vite est validé sur la version livrée.

## Intégration continue

Le workflow `.github/workflows/backend-tests.yml` est exécuté automatiquement sur les pushes et pull requests.

Il réalise notamment :

1. la configuration de PHP 8.2 avec SQLite pour les tests ;
2. la préparation de l'environnement Symfony de test ;
3. la génération d'une paire de clés JWT de test ;
4. l'installation des dépendances Composer ;
5. l'exécution de PHPUnit.

La CI GitHub Actions de la version finale est opérationnelle.

## Docker

Une stack Docker est disponible à la racine du projet avec :

- MySQL ;
- backend Symfony ;
- frontend Vue ;
- Nginx.

Lancement :

```bash
docker compose up --build
```

Accès :

- application : `http://localhost`
- API : `http://localhost/api`

## Déploiement Render

La version de démonstration est déployée sur Render.

### Backend

- Web Service Docker Symfony
- PHP 8.2
- PostgreSQL 16
- migrations exécutées au démarrage
- clés JWT injectées via variables d'environnement encodées en Base64

URL :

```text
https://eventflow-1-g30y.onrender.com/api
```

### Frontend

- Static Site Vue / Vite
- variable `VITE_API_URL` configurée vers l'API Render

URL :

```text
https://eventflow-front-9961.onrender.com/
```

### Variables principales en production

Backend :

- `APP_ENV`
- `APP_SECRET`
- `DATABASE_URL`
- `JWT_PRIVATE_KEY_B64`
- `JWT_PUBLIC_KEY_B64`
- `JWT_PASSPHRASE`
- `JWT_TTL`
- `CORS_ALLOW_ORIGIN`

Frontend :

- `VITE_API_URL`

Aucun secret de production n'est versionné dans le dépôt.

## Commande d'anonymisation

Depuis `backend/` :

```bash
php bin/console app:anonymize-old-users
```

La politique du projet prévoit l'anonymisation des comptes inactifs depuis plus de 24 mois.

## Livrables présents

- application backend Symfony ;
- application frontend Vue 3 ;
- migrations Doctrine ;
- tests fonctionnels backend ;
- workflow GitHub Actions ;
- Docker / Docker Compose ;
- configuration Render ;
- fichier `eventflow.http` ;
- registre `REGISTRE_TRAITEMENTS_RGPD.md` ;
- politique de confidentialité intégrée à l'application.

## Points d'architecture

Le projet utilise notamment :

- Controllers Symfony ;
- Repositories Doctrine ;
- Voter Symfony pour les autorisations ;
- service de journalisation du consentement ;
- Pinia pour l'état partagé frontend ;
- Vue Router et guards de navigation ;
- instance Axios centralisée avec intercepteur JWT.

La séparation Service / DTO pourrait être approfondie dans une évolution future afin de réduire davantage la logique présente dans certains contrôleurs.

## Perspectives

Parmi les évolutions possibles :

- profils publics d'organisateurs avec logo ou photo ;
- pages dédiées aux organisateurs et à leurs événements ;
- catégories et filtres avancés ;
- recherche par ville et par date ;
- favoris ;
- notifications ;
- enrichissement des visuels des événements.

## Auteur

Anna Merveille KAYA
