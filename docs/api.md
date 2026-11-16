# API HTTP

JSON uniquement. Les dates sont au format ISO 8601 (`2026-01-05T09:30:00`), interprétées dans le fuseau du serveur (voir « Défauts connus » dans `ROADMAP.md`).

## Routes

| Méthode | Route | Corps / paramètres | Réponse |
| --- | --- | --- | --- |
| `POST` | `/api/providers` | `name`, `openingHours[]` (`weekday` 1–7, `opensAt`, `closesAt` en `HH:MM`), `slotDurationMinutes`, `bufferMinutes`?, `minimumNoticeMinutes`? | `201` `{ "id" }` |
| `GET` | `/api/providers/{id}/slots` | `?date=AAAA-MM-JJ` | `200` `[{ "startsAt", "endsAt" }]` |
| `POST` | `/api/providers/{id}/appointments` | `startsAt`, `customerName`, `customerEmail` | `201` `{ "id" }` + en-tête `Location` |
| `GET` | `/api/providers/{id}/appointments` | `?from=AAAA-MM-JJ&to=AAAA-MM-JJ` (bornes incluses) | `200` liste de rendez-vous |
| `GET` | `/api/appointments/{id}` | — | `200` `{ id, providerId, customerName, customerEmail, startsAt, endsAt, status }` |
| `POST` | `/api/appointments/{id}/confirm` | — | `204` |
| `POST` | `/api/appointments/{id}/cancel` | `reason` | `204` |

`status` : `pending`, `confirmed`, `cancelled`, `completed`.

## Erreurs (`application/problem+json`, RFC 9457)

| Statut | Quand | Champs en plus |
| --- | --- | --- |
| `400` | JSON illisible, paramètre de requête invalide | `violations[]` le cas échéant |
| `404` | Prestataire ou rendez-vous introuvable | — |
| `409` | Créneau indisponible, transition de statut interdite | `reasons[]` (ex. `"déjà réservé"`, `"délai de prévenance non respecté"`) |
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
