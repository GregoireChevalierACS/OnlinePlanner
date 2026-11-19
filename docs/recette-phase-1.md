# Recette manuelle de la phase 1

À dérouler dans un vrai navigateur avant de déclarer la phase 1 terminée (critère « Terminée quand » de `ROADMAP.md`). Cocher chaque ligne ; noter tout écart dans « Défauts connus » de la feuille de route.

## Préparation

```bash
docker compose up -d
docker compose exec api bin/console doctrine:migrations:migrate -n
cd frontend && npm run dev
```

- Front : http://localhost:5173 · E-mails : http://localhost:8025 (Mailpit)
- Prévoir deux navigateurs (ou une fenêtre privée) : un pour le **prestataire**, un pour le **client**.

## 1. Compte prestataire

- [ ] `/connexion` → « Créer un compte » avec un mot de passe de 11 caractères : refusé, message clair.
- [ ] Création avec 12 caractères ou plus : connecté, bandeau « Confirmez votre adresse ».
- [ ] Mailpit : e-mail « Confirmez votre adresse e-mail » reçu ; le lien ouvre `/verifier-email` → « Adresse confirmée ».
- [ ] Recharger la page : le bandeau a disparu.
- [ ] Rouvrir le même lien : « Ce lien est invalide ou a expiré ».

## 2. Agenda

- [ ] Créer un agenda (horaires du lundi au vendredi, 30 min, prévenance 60 min) : redirection vers l'agenda vide.
- [ ] Copier le « Lien de réservation à partager ».
- [ ] Accueil : l'agenda apparaît dans « Mes agendas ».

## 3. Réservation (navigateur client, non connecté)

- [ ] Ouvrir le lien de réservation : seuls les créneaux ouverts et à plus de 60 min apparaissent.
- [ ] Changer rapidement de jour plusieurs fois : la liste affichée correspond toujours au dernier jour choisi.
- [ ] Réserver un créneau : message « Rendez-vous demandé… », le créneau disparaît de la liste.
- [ ] Réserver le même créneau depuis un second onglet ouvert avant la réservation : refus « déjà réservé », la liste se rafraîchit.
- [ ] Mailpit : « Demande de rendez-vous reçue » (client) et « Nouvelle demande de rendez-vous » (prestataire).

## 4. Gestion (navigateur prestataire)

- [ ] L'agenda affiche le rendez-vous « À confirmer ».
- [ ] « Confirmer » : statut « Confirmé » ; Mailpit : « Rendez-vous confirmé » au client.
- [ ] « Annuler » : une fenêtre demande confirmation ; le bouton reste grisé sans motif ; « Retour » n'annule rien.
- [ ] Annuler avec un motif : statut « Annulé », Mailpit : « Rendez-vous annulé » avec le motif ; le créneau redevient réservable côté client.

## 5. Sécurité

- [ ] Se déconnecter puis ouvrir l'URL de l'agenda : redirection vers la connexion, puis retour sur l'agenda après connexion.
- [ ] Avec un **second compte**, ouvrir l'URL de l'agenda du premier : refus (403), aucune donnée client visible.
- [ ] 6 connexions avec un mauvais mot de passe : « Trop de tentatives » à la 6e.
- [ ] « Mot de passe oublié ? » avec une adresse inconnue : même message qu'avec une adresse connue, aucun e-mail.
- [ ] Avec l'adresse du compte : e-mail reçu, nouveau mot de passe enregistré ; l'ancien ne fonctionne plus ; le lien ne sert qu'une fois.

## 6. Repli

- [ ] `docker compose stop mailpit`, faire une réservation : elle aboutit. `docker compose start mailpit` : les e-mails arrivent dans la minute.
- [ ] Couper l'API (`docker compose stop api`) : le front affiche « Le serveur ne répond pas » au lieu de planter.

## Résultat

- Date : ……… · Testeur : ……… · Navigateur : ………
- Écarts relevés : ………
