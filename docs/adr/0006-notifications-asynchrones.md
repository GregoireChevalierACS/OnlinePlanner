# ADR 0006 : Notifications par e-mail, asynchrones

- Statut : accepté
- Date : 2026-11-18

## Contexte

Client et prestataire doivent être prévenus des réservations, confirmations et annulations. Le guide demande un comportement défini quand un service externe (ici, le serveur d'e-mail) est indisponible.

## Décision

- **Observer sur les Domain Events** : `NotifyOnAppointmentBooked`, `…Confirmed`, `…Cancelled` (`Scheduling/Application/Notification`) s'abonnent au bus d'événements grâce à l'interface `EventHandler`. Les cas d'usage ne savent rien des e-mails.
- **Port `Notifier`** et message neutre `Notification` (destinataire, sujet, texte) ; adapter `MailerNotifier` (Symfony Mailer). Un canal SMS serait un nouvel adapter.
- **Textes centralisés** dans `AppointmentEmails` (Factory), testés mot pour mot.
- **Anti-corruption layer** : `OwnerDirectory` (port de Scheduling) / `AccountOwnerDirectory` (adapter) retrouve l'e-mail du propriétaire dans le contexte Identity, sans que Scheduling connaisse les comptes.
- **Asynchrone** : les événements sont routés vers le transport Messenger `async` (table `messenger_messages`) et traités par le service `worker`. Une panne du serveur d'e-mail ne fait jamais échouer une réservation : 3 reprises (2 s, 6 s, 18 s), puis transport `failed` (`messenger:failed:show` / `messenger:failed:retry`).
- **Développement** : Mailpit capture tous les e-mails (http://localhost:8025), rien n'est envoyé pour de vrai. **Tests** : transport `async` synchrone et mailer `null://null` imposé, e-mails vérifiés par `assertEmailCount`.

## Conséquences

- Un processus de plus à exploiter (`messenger:consume async`), à superviser en production.
- Les e-mails partent quelques secondes après l'action : acceptable pour ce besoin.
- Texte brut uniquement pour l'instant (pas de mise en forme HTML).
