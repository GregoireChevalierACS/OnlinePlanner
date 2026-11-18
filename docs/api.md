# API HTTP

JSON uniquement. Les dates sont au format ISO 8601 (`2026-01-05T09:30:00`), interprétées dans le fuseau du serveur (voir « Défauts connus » dans `ROADMAP.md`).

## Authentification

Session par cookie `HttpOnly` posé par l'API (voir ADR 0005). Côté navigateur : `fetch(…, { credentials: 'include' })`.

| Méthode | Route | Corps | Réponse |
| --- | --- | --- | --- |
| `POST` | `/api/auth/register` | `email`, `password` (12 caractères min.) | `201` `{ id, email }`, session ouverte |
| `POST` | `/api/auth/login` | `email`, `password` | `200` `{ id, email }` ; `401` identifiants incorrects ; `429` trop de tentatives |
| `GET` | `/api/auth/me` | — | `200` `{ id, email }` ou `401` |
| `POST` | `/api/auth/logout` | — | `204` |
| `POST` | `/api/auth/password-reset` | `email` | `202` toujours (lien envoyé seulement si le compte existe) ; `429` au-delà de 3 demandes / 15 min / IP |
| `POST` | `/api/auth/password-reset/confirm` | `token` (reçu par e-mail), `password` | `204` ; `422` lien invalide, expiré ou déjà utilisé |

## Routes

Accès : 🌐 public · 🔒 prestataire connecté · 👤 propriétaire de l'agenda.

| Accès | Méthode | Route | Corps / paramètres | Réponse |
| --- | --- | --- | --- | --- |
| 🔒 | `GET` | `/api/providers` | — | `200` `[{ id, name }]` (mes agendas) |
| 🔒 | `POST` | `/api/providers` | `name`, `openingHours[]` (`weekday` 1–7, `opensAt`, `closesAt` en `HH:MM`), `slotDurationMinutes`, `bufferMinutes`?, `minimumNoticeMinutes`? | `201` `{ "id" }` |
| 🌐 | `GET` | `/api/providers/{id}/slots` | `?date=AAAA-MM-JJ` | `200` `[{ "startsAt", "endsAt" }]` |
| 🌐 | `POST` | `/api/providers/{id}/appointments` | `startsAt`, `customerName`, `customerEmail` | `201` `{ "id" }` + en-tête `Location` |
| 👤 | `GET` | `/api/providers/{id}/appointments` | `?from=AAAA-MM-JJ&to=AAAA-MM-JJ` (bornes incluses) | `200` liste de rendez-vous |
| 👤 | `GET` | `/api/appointments/{id}` | — | `200` `{ id, providerId, customerName, customerEmail, startsAt, endsAt, status }` |
| 👤 | `POST` | `/api/appointments/{id}/confirm` | — | `204` |
| 👤 | `POST` | `/api/appointments/{id}/cancel` | `reason` | `204` |

`status` : `pending`, `confirmed`, `cancelled`, `completed`.

## Erreurs (`application/problem+json`, RFC 9457)

| Statut | Quand | Champs en plus |
| --- | --- | --- |
| `400` | JSON illisible, paramètre de requête invalide | `violations[]` le cas échéant |
| `401` | Pas de session (route 🔒 ou 👤), identifiants incorrects | — |
| `403` | Connecté, mais l'agenda appartient à un autre compte | — |
| `404` | Prestataire ou rendez-vous introuvable | — |
| `409` | Créneau indisponible, transition de statut interdite, adresse déjà inscrite | `reasons[]` (ex. `"déjà réservé"`, `"délai de prévenance non respecté"`) |
| `429` | Trop de tentatives de connexion | — |
| `422` | Champ invalide (`violations[]` : `field`, `message`) ou règle métier non respectée | `violations[]` le cas échéant |

Exemple :

```json
{
  "type": "about:blank",
  "title": "Conflict",
  "status": 409,
  "detail": "Créneau indisponible : déjà réservé.",
  "reasons": ["déjà réservé"]
}
```
