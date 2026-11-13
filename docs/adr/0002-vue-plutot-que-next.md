# ADR 0002 : Vue 3 (SPA) plutôt que Next.js

- Statut : accepté
- Date : 2026-11-13

## Contexte

Le frontend consomme l'API Symfony. Deux options étaient envisagées : Vue.js ou Next.js.

## Décision

Vue 3 + TypeScript + Vite, en SPA, avec Pinia (état) et Vue Router.

## Raisons

- Symfony porte déjà le rendu serveur éventuel et toute la logique métier : le rendu serveur de Next.js n'apporterait qu'une deuxième couche serveur à déployer et sécuriser.
- Un agenda de prise de rendez-vous est une interface très interactive, derrière connexion pour la partie prestataire : le référencement (principal atout du SSR) ne concerne que la page publique de réservation.
- Un seul serveur à exploiter (l'API), le front se déploie en fichiers statiques.

## Conséquences

- Si la page publique de réservation doit être référencée, on pourra la rendre côté Symfony (Twig) ou passer à Nuxt sans toucher à l'API.
