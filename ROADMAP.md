# Feuille de route

## Phase 1 : Socle complet et testé

- [x] Squelettes Symfony 8.1 / Vue 3, environnement Docker
- [x] CI : style, PHPStan max, tests, format des commits ; `main` protégée
- [x] Domaine `Scheduling` : créneaux, horaires, règles de réservation, statuts, événements
- [x] Couverture 100 % et MSI ≥ 80 % du domaine imposés en CI
- [x] Application : commandes `RegisterProvider`, `BookAppointment`, `ConfirmAppointment`, `CancelAppointment` ; requêtes `ListAvailableSlots`, `GetAppointment`, `ListAppointments`
- [x] Prestataire (`Provider`) : horaires, durée, battement, délai de prévenance
- [x] Dépôts en mémoire + suite de tests de contrat commune
- [ ] Infrastructure : bus Messenger (commandes / requêtes), dépôts Doctrine soumis à la suite de contrat, horloge et UUID
- [ ] API HTTP (JSON) et traduction des erreurs métier en 4xx
- [ ] Front : réservation (choix du jour, créneaux, formulaire) et agenda du prestataire
- [ ] Notifications par e-mail sur les Domain Events
- [ ] Authentification du prestataire

**Terminée quand :** la CI est verte avec couverture 100 % et MSI ≥ 80 % sur domaine et application, et un rendez-vous peut être réservé, confirmé et annulé de bout en bout via le front.

## Phase 2 : Essai réel

**Terminée quand :** 3 prestataires l'ont utilisé 2 semaines, chaque défaut est corrigé ou noté ici.

## Phase 3 : Confort d'usage

Rappels, vues semaine / mois, export iCal, synchronisation d'agendas externes (via contrat + adapters).

## Phase 4 : Déploiement

Déploiement automatisé, sauvegardes, supervision.

## Défauts connus

- **Double réservation concurrente** : deux requêtes simultanées peuvent passer la règle « déjà réservé » avant l'enregistrement. À couvrir côté base (contrainte d'exclusion PostgreSQL sur la plage horaire) lors de l'adapter Doctrine.
- **Fuseaux horaires** : les horaires d'ouverture sont interprétés dans le fuseau des dates reçues. Ajouter un fuseau par prestataire avant l'essai réel.
