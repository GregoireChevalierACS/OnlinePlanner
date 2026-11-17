# ADR 0005 : Authentification par comptes propres et session

- Statut : accepté
- Date : 2026-11-17

## Contexte

L'agenda d'un prestataire (rendez-vous, coordonnées des clients) était accessible à quiconque connaissait son identifiant. Le guide recommande OAuth + PKCE pour les applications de bureau ou mobiles ; ici, il s'agit d'une application web servie avec sa propre API.

## Décision

- **Comptes propres à l'application** (choix du porteur de projet), dans un contexte métier `Identity` séparé de `Scheduling`.
- **Session par cookie** `HttpOnly`, `SameSite=Lax` (composant Security de Symfony, `json_login`) : aucun jeton dans le JavaScript, donc rien à voler par XSS ; le navigateur n'envoie pas le cookie sur un POST venu d'un autre site.
- **Mots de passe** : 12 caractères minimum (`PlainPassword`), hachés par l'algorithme `auto` de Symfony (bcrypt/argon2) derrière le port `PasswordHasher`.
- **Connexion freinée** : 5 tentatives par adresse et par IP sur 15 minutes (`login_throttling`), puis `429`.
- **Aucune fuite d'information** : même message pour une adresse inconnue et un mauvais mot de passe ; message neutre si l'adresse est déjà inscrite.
- **Propriété des agendas** : `Provider` porte un `OwnerId` ; la règle `Provider::isManagedBy()` est métier. `ScheduleAccessVoter` l'applique aux routes de l'agenda et des rendez-vous (`403` sinon).
- **Parcours public inchangé** : voir les créneaux et réserver ne demandent pas de compte.

| Patron | Où |
| --- | --- |
| Adapter | `SecurityUser` présente un `Account` du domaine au composant Security |
| Port / Adapter | `PasswordHasher` / `SymfonyPasswordHasher` ; `AuthGateway` / `HttpAuthGateway` côté front |
| Voter (Strategy d'autorisation) | `ScheduleAccessVoter` |
| Garde de navigation | `composables/authGuard.ts` (avec `safeRedirect` contre les redirections ouvertes) |

## Conséquences

- Les agendas créés avant cette version n'ont pas de propriétaire (`sans-proprietaire`) et ne sont plus gérables.
- Pas encore de réinitialisation de mot de passe ni de vérification de l'adresse e-mail : elles demandent l'envoi d'e-mails (voir `ROADMAP.md`).
- Contrôle des mots de passe divulgués (Have I Been Pwned) écarté pour l'instant : il ajoute un appel à un service externe à chaque inscription.
