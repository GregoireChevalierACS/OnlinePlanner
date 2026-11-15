# ADR 0003 : Persistance Doctrine et garantie anti double réservation

- Statut : accepté
- Date : 2026-11-15

## Contexte

La règle « déjà réservé » est vérifiée par le domaine (`BookingPolicy`) à partir des rendez-vous lus en base. Deux requêtes simultanées peuvent lire le même état et réserver le même créneau : la règle métier seule ne suffit pas sous concurrence.

## Décision

1. **Mapping XML** dans `Scheduling/Infrastructure/Persistence/Doctrine/Mapping` : le domaine ne porte aucun attribut Doctrine. Les Value Objects sont des *embeddables* (`Customer`, `Email`, `TimeSlot`, `SlotSettings`) ou des types DBAL dédiés (identifiants, statut, horaires en JSON).
2. **Contrainte d'exclusion PostgreSQL** `appointment_no_overlap` (extension `btree_gist`) : deux rendez-vous non annulés d'un même prestataire ne peuvent pas avoir de plages qui se chevauchent. La base tranche en dernier ressort.
3. **Même erreur métier dans les deux cas** : `DoctrineAppointmentRepository` traduit la violation (SQLSTATE `23P01`) en `SlotUnavailable` (« déjà réservé »). L'adapter en mémoire applique la même règle, vérifiée par la suite de contrat commune.
4. **Bus Messenger** : `command.bus` (middleware `doctrine_transaction`), `query.bus`, `event.bus` (zéro à plusieurs abonnés). Les Domain Events partent avec `DispatchAfterCurrentBusStamp`, donc après le commit de la commande. Les handlers sont branchés par `_instanceof` sur les interfaces `CommandHandler` / `QueryHandler` : la couche application ne dépend pas de Messenger.

## Conséquences

- La contrainte n'est pas exprimable dans le mapping : elle vit dans la migration `Version20261115085004`. Son index est déclaré « en reflet » dans `Appointment.Appointment.orm.xml` pour que `doctrine:migrations:diff` ne propose pas de la supprimer. `doctrine:schema:validate` tourne en CI pour détecter toute dérive.
- Le projet est lié à PostgreSQL (assumé).
