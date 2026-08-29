# Registre des Traitements RGPD — EventFlow

**Responsable du traitement :** EventFlow — Projet M2 Lead Dev Full Stack 2025-2026
**DPO fictif désigné :** dpo@eventflow.local
**Date de mise à jour :** 30/03/2026
**Version :** 1.0

---

## Traitement 1 — Création de compte utilisateur

| Champ | Détail |
|---|---|
| **Finalité** | Permettre l'authentification et l'accès à la plateforme |
| **Base légale** | Consentement explicite (Art. 6.1.a RGPD) |
| **Données concernées** | Nom, prénom, email, mot de passe (hashé bcrypt), téléphone (optionnel), date/version du consentement |
| **Destinataires** | Application EventFlow uniquement |
| **Durée de conservation** | 24 mois après la dernière activité |
| **Mesures de sécurité** | Mot de passe hashé bcrypt, transmission HTTPS, JWT signé RS256 |

---

## Traitement 2 — Authentification et gestion des sessions

| Champ | Détail |
|---|---|
| **Finalité** | Vérifier l'identité de l'utilisateur et sécuriser l'accès |
| **Base légale** | Intérêt légitime (Art. 6.1.f RGPD) — sécurité du service |
| **Données concernées** | Email, token JWT, adresse IP (hashée SHA-256) |
| **Destinataires** | Application EventFlow uniquement |
| **Durée de conservation** | Token : 1 heure. Logs de connexion : 12 mois |
| **Mesures de sécurité** | JWT signé RS256 (clé 4096 bits), IP stockée en hash SHA-256 irréversible |

---

## Traitement 3 — Inscription aux événements

| Champ | Détail |
|---|---|
| **Finalité** | Gérer les inscriptions des participants aux événements |
| **Base légale** | Exécution d'un contrat (Art. 6.1.b RGPD) |
| **Données concernées** | Identifiant utilisateur, identifiant événement, date d'inscription, statut |
| **Destinataires** | Organisateur de l'événement, administrateurs |
| **Durée de conservation** | 3 ans après l'événement |
| **Mesures de sécurité** | Accès restreint par rôle (Voter Symfony), authentification JWT requise |

---

## Traitement 4 — Création et gestion d'événements

| Champ | Détail |
|---|---|
| **Finalité** | Permettre aux organisateurs de publier et gérer leurs événements |
| **Base légale** | Exécution d'un contrat (Art. 6.1.b RGPD) |
| **Données concernées** | Titre, description, date, lieu, capacité, identifiant organisateur |
| **Destinataires** | Participants (données publiées), administrateurs |
| **Durée de conservation** | 5 ans après la date de l'événement |
| **Mesures de sécurité** | Accès en écriture limité aux organisateurs/admins via Voter |

---

## Traitement 5 — Journalisation des consentements (ConsentLog)

| Champ | Détail |
|---|---|
| **Finalité** | Prouver et tracer les actions relatives aux données personnelles (obligation RGPD) |
| **Base légale** | Obligation légale (Art. 6.1.c RGPD) |
| **Données concernées** | Identifiant utilisateur, action, horodatage, adresse IP (hashée), détails |
| **Destinataires** | DPO, administrateurs |
| **Durée de conservation** | 5 ans (obligation légale de preuve) |
| **Mesures de sécurité** | Accès restreint aux admins, IP en hash SHA-256, logs non modifiables |

---

## Traitement 6 — Rectification des données personnelles (Art. 16)

| Champ | Détail |
|---|---|
| **Finalité** | Permettre à l'utilisateur de corriger ses données inexactes |
| **Base légale** | Obligation légale (Art. 6.1.c + Art. 16 RGPD) |
| **Données concernées** | Nom, prénom, téléphone |
| **Destinataires** | Utilisateur concerné uniquement |
| **Durée de conservation** | Mise à jour immédiate, log de l'action conservé 5 ans |
| **Mesures de sécurité** | Authentification JWT requise, log ConsentLog automatique |

---

## Traitement 7 — Anonymisation des comptes (Art. 17 — Droit à l'oubli)

| Champ | Détail |
|---|---|
| **Finalité** | Permettre la suppression effective des données personnelles |
| **Base légale** | Obligation légale (Art. 17 RGPD) |
| **Données concernées** | Nom, prénom, email, téléphone remplacés par des valeurs anonymes |
| **Destinataires** | Système interne uniquement |
| **Durée de conservation** | Les données anonymisées ne contiennent plus de données personnelles |
| **Mesures de sécurité** | Action irréversible, log conservé avant anonymisation, commande CLI sécurisée |

---

## Traitement 8 — Export des données personnelles (Art. 20 — Portabilité)

| Champ | Détail |
|---|---|
| **Finalité** | Permettre à l'utilisateur de récupérer ses données dans un format portable |
| **Base légale** | Obligation légale (Art. 20 RGPD) |
| **Données concernées** | Toutes les données personnelles de l'utilisateur + historique ConsentLog |
| **Destinataires** | Utilisateur concerné uniquement |
| **Durée de conservation** | Export ponctuel, non stocké côté serveur |
| **Mesures de sécurité** | Authentification JWT requise, log de l'export dans ConsentLog |

---

## Synthèse des mesures de sécurité globales

- Mots de passe : hashés bcrypt (coût 12+)
- Tokens JWT : signés RS256, durée de vie 1h
- Adresses IP : stockées en hash SHA-256 irréversible
- Communications : HTTPS obligatoire en production
- Accès aux données : contrôlé par rôles (Voter Symfony)
- Migrations : versionnées avec Doctrine
- Clés JWT : stockées hors du code source

---

*Ce registre doit être mis à jour à chaque nouveau traitement ou modification substantielle.*
