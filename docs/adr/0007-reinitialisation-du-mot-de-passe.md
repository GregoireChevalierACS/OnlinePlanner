# ADR 0007 : Réinitialisation du mot de passe

- Statut : accepté
- Date : 2026-11-18

## Contexte

Sans réinitialisation, un prestataire qui oublie son mot de passe perd l'accès à son agenda (défaut noté dans `ROADMAP.md`).

## Décision

- **Lien à usage unique, valable 1 heure**, envoyé par e-mail : `FRONT_URL/mot-de-passe/nouveau?token=…`.
- **Jeton** : 256 bits d'aléa (`random_bytes`). **Seule son empreinte SHA-256 est stockée** (`password_reset.token_hash`) : une fuite de la base ne donne aucun lien utilisable. L'aléa élevé rend un hachage lent inutile.
- **Aucune fuite d'information** : la demande répond toujours `202`, que l'adresse ait un compte ou non ; un lien inconnu, expiré ou déjà utilisé donne le même message.
- **Limitation** : 3 demandes par adresse IP sur 15 minutes (`rate_limiter.password_reset`), puis `429`.
- **Sessions** : changer le mot de passe change son hash ; Symfony déconnecte alors les sessions ouvertes avec l'ancien.
- **Envoi synchrone, exception assumée à l'ADR 0006** : l'e-mail de réinitialisation part pendant la requête, sans passer par la file `messenger_messages`, pour que le jeton en clair ne soit jamais écrit, même temporairement, en base. Si le serveur d'e-mail est indisponible, la demande échoue et l'utilisateur la refait.
- **Port `Notifier` déplacé dans `Shared`** : Identity et Scheduling l'utilisent tous deux.

| Patron | Où |
| --- | --- |
| Aggregate (cycle de vie) | `PasswordReset::issue()` / `consume()` : expiration et usage unique sont des règles du domaine |
| Port / Adapter | `ResetTokens` / `RandomResetTokens` (génération et lien), `PasswordResetRepository` (Doctrine, mémoire, suite de contrat) |
| Factory | `AccountEmails::passwordReset()` |

## Conséquences

- Les demandes expirées restent en base ; une purge périodique (Scheduler Symfony) sera utile en production.
- La vérification de l'adresse e-mail à l'inscription pourra réutiliser le même mécanisme de jeton.
