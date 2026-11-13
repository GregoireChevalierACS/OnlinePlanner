# ADR 0001 : Architecture hexagonale par contexte métier

- Statut : accepté
- Date : 2026-11-13

## Contexte

Le guide `bonnes-pratiques.md` impose un domaine qui ne dépend de rien d'extérieur, testé à 100 % avec un score de mutation ≥ 80 %, et des services externes derrière des contrats.

## Décision

Le backend est découpé par contexte métier (`Scheduling`, plus tard `Identity`…), et chaque contexte en trois couches :

| Couche | Contenu | Peut dépendre de |
| --- | --- | --- |
| `Domain` | Règles métier en PHP pur : agrégats, Value Objects, Specifications, Domain Events, ports (interfaces) | `Shared/Domain` uniquement |
| `Application` | Cas d'usage (commandes et requêtes) qui orchestrent le domaine | `Domain` |
| `Infrastructure` | Adapters : Doctrine, HTTP (contrôleurs), Messenger, mail, horloge système | tout |

- Le domaine n'utilise ni attribut Doctrine ni composant Symfony : le mapping ORM sera en XML dans `Infrastructure`.
- `config/services.yaml` exclut `src/*/Domain/` du conteneur : ces objets sont créés par le code.
- Les identifiants sont des Value Objects ; leur génération (UUID) est un port de l'application.

## Patrons de conception retenus

| Patron | Où | Pourquoi |
| --- | --- | --- |
| Value Object | `TimeSlot`, `Email`, `OpeningHours`, identifiants | Invariants vérifiés à la construction, comparaison par valeur |
| Aggregate Root + Factory Method | `Appointment::book()` | Impossible de créer un rendez-vous sans passer par les règles |
| State | `Appointment/Status/*` | Chaque statut décide de ses transitions, pas de `switch` |
| Specification + Composite | `Booking/*`, `BookingPolicy` | Règles de réservation isolées, combinables, avec motif de refus |
| Strategy | `SlotGenerationStrategy` | Découpage des créneaux propre à chaque prestataire |
| Domain Event / Observer | `Appointment/Event/*` | Notifications découplées du cas d'usage |
| Factory | `AppointmentStatuses::fromName()` | Reconstruction d'un état persisté |
| Domain Service | `AvailabilityCalculator` | Calcul qui n'appartient à aucun agrégat |
| Port / Adapter | `Clock`, dépôts à venir | Domaine testable sans infrastructure |
| Command / Query (CQRS), Repository, Adapter | phases suivantes | Voir `ROADMAP.md` |

## Conséquences

- Plus de classes qu'un CRUD Symfony classique, en échange d'un domaine testable en millisecondes.
- Le mapping Doctrine XML est plus verbeux que les attributs.
