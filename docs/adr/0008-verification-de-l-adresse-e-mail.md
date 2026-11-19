# ADR 0008 : Vérification de l'adresse e-mail

- Statut : accepté
- Date : 2026-11-19

## Contexte

N'importe qui pouvait créer un compte avec l'adresse d'un tiers : les e-mails de nouvelles réservations (nom et e-mail des clients) seraient alors partis chez cette personne.

## Décision

- **Lien de vérification envoyé à l'inscription**, valable 2 jours, à usage unique ; renvoi possible depuis un bandeau du front (3 renvois / 15 min / compte).
- **Un compte non vérifié fonctionne** (agendas, réservations, confirmation) mais **ne reçoit aucun e-mail de réservation** : `AccountOwnerDirectory` ne donne l'adresse que si elle est vérifiée. Les clients, eux, sont toujours prévenus.
- **Template Method** : `OneTimeToken` porte le cycle de vie commun (empreinte SHA-256, expiration, usage unique) ; `PasswordReset` (1 heure) et `EmailVerification` (2 jours) ne précisent que leur durée. Côté Doctrine, `OneTimeToken` est une *mapped superclass* : la table `password_reset` est inchangée.
- **Port `SecretLinks`** (remplace `ResetTokens`) : génère les jetons et construit les deux sortes de liens à partir de `FRONT_URL`.
- **Même exception `InvalidLink`** pour les deux sortes de liens, avec un message unique.
- Comme pour la réinitialisation (ADR 0007), l'e-mail part de façon synchrone pour que le jeton en clair ne soit jamais stocké.

## Conséquences

- Les comptes créés avant cette version sont non vérifiés : il suffit d'utiliser « Renvoyer le lien ».
- Si l'envoi de l'e-mail échoue à l'inscription, le compte n'est pas créé (même transaction) : l'utilisateur recommence.
