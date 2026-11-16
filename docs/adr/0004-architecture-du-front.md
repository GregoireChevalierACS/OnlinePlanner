# ADR 0004 : Architecture du front Vue

- Statut : accepté
- Date : 2026-11-16

## Contexte

Le front doit rester testable sans serveur, résister aux réponses qui arrivent dans le désordre et ne jamais exécuter d'action irréversible sans accord (`bonnes-pratiques.md`).

## Décision

| Patron | Où | Pourquoi |
| --- | --- | --- |
| Port / Adapter | `api/SchedulingGateway.ts` (interface) et `HttpSchedulingGateway` | Les vues et stores ne connaissent que l'interface ; les tests injectent un double |
| Injection de dépendances (composition root) | `main.ts` : `app.provide(schedulingGatewayKey, …)`, lu par `useSchedulingGateway()` | Un seul endroit choisit l'adapter concret |
| Repli défini | `ApiError`, `toProblem()` | Toute erreur (API, réseau, réponse illisible) devient un message affichable |
| « Dernière demande gagnante » | `composables/useLatestRequest.ts` | Annule la demande précédente et ignore sa réponse tardive |
| Store (Pinia) + état dérivé | `stores/booking.ts`, `stores/agenda.ts` (`canBook`, `byDay` calculés) | L'état affiché est calculé, jamais synchronisé à la main |
| Confirmation avant action irréversible | `components/ConfirmDialog.vue` (annulation avec motif obligatoire) | « L'outil propose, l'utilisateur décide » |

En développement, Vite redirige `/api` vers `http://localhost:8000` : pas de CORS. En production, `VITE_API_URL` donne l'origine de l'API.

## Conséquences

- Couverture 100 % imposée en CI sur `api/`, `stores/` et `composables/` (la logique) ; les vues sont testées sur les parcours sensibles (annulation).
- Pas d'authentification pour l'instant : l'agenda est accessible à qui connaît l'identifiant du prestataire. À traiter avant l'essai réel (voir `ROADMAP.md`).
