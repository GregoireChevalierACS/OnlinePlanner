# Feuille de route

## Phase 1 : Socle complet et testé

- [x] Squelettes Symfony 8.1 / Vue 3, environnement Docker
- [x] CI : style, PHPStan max, tests, format des commits ; `main` protégée
- [x] Domaine `Scheduling` : créneaux, horaires, règles de réservation, statuts, événements
- [x] Couverture 100 % et MSI ≥ 80 % du domaine imposés en CI
- [x] Application : commandes `RegisterProvider`, `BookAppointment`, `ConfirmAppointment`, `CancelAppointment` ; requêtes `ListAvailableSlots`, `GetAppointment`, `ListAppointments`
- [x] Prestataire (`Provider`) : horaires, durée, battement, délai de prévenance
- [x] Dépôts en mémoire + suite de tests de contrat commune
- [x] Infrastructure : bus Messenger (commandes, requêtes, événements), dépôts Doctrine soumis à la suite de contrat, horloge et UUID v7
- [x] API HTTP (JSON) et traduction des erreurs métier en 4xx (`problem+json`), voir `docs/api.md`
- [x] Front : création d'agenda, réservation (jour, créneaux, formulaire) et agenda du prestataire (confirmation, annulation avec motif), voir ADR 0004
- [x] Notifications par e-mail sur les Domain Events, envoyées par un worker avec reprises (ADR 0006)
- [x] Authentification du prestataire : comptes, session par cookie, propriété des agendas (ADR 0005)
- [x] Réinitialisation du mot de passe par lien à usage unique (ADR 0007)
- [ ] Vérification de l'adresse e-mail à l'inscription (même mécanisme de jeton)

**Terminée quand :** la CI est verte avec couverture 100 % et MSI ≥ 80 % sur domaine et application, et un rendez-vous peut être réservé, confirmé et annulé de bout en bout via le front.

## Phase 2 : Essai réel

**Terminée quand :** 3 prestataires l'ont utilisé 2 semaines, chaque défaut est corrigé ou noté ici.

## Phase 3 : Confort d'usage

Rappels, vues semaine / mois, export iCal, synchronisation d'agendas externes (via contrat + adapters).

## Phase 4 : Déploiement

Déploiement automatisé, sauvegardes, supervision.

## Défauts connus

- **Fuseaux horaires** : les horaires d'ouverture sont interprétés dans le fuseau des dates reçues. Ajouter un fuseau par prestataire avant l'essai réel.
- **Adresse non vérifiée** : un compte peut être créé avec l'adresse de quelqu'un d'autre ; les e-mails de nouvelles réservations partiraient alors chez cette personne.
- **Demandes de réinitialisation expirées** conservées en base : prévoir une purge périodique.
