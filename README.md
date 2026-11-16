# OnlinePlanner

Organisateur de planning et de prise de rendez-vous : API Symfony 8.1 (architecture hexagonale, CQRS) et front Vue 3.

- Architecture et patrons de conception : [docs/adr/0001](docs/adr/0001-architecture-hexagonale.md)
- API : [docs/api.md](docs/api.md)
- Feuille de route et défauts connus : [ROADMAP.md](ROADMAP.md)
- Pièges rencontrés : [docs/pieges-rencontres.md](docs/pieges-rencontres.md)
- Règles du projet : [bonnes-pratiques.md](bonnes-pratiques.md)

## Prérequis

Docker (PHP n'a pas besoin d'être installé), Node 24, Git.

## Démarrer

```bash
git config core.hooksPath .githooks                       # format des commits
echo "APP_SECRET=$(openssl rand -hex 32)" > backend/.env.dev.local   # jamais versionné

docker compose build
docker compose run --rm api composer install
docker compose up -d                                       # API sur http://localhost:8000, PostgreSQL sur 5432
docker compose run --rm api bin/console doctrine:migrations:migrate -n

cd frontend && npm install && npm run dev                  # front sur http://localhost:5173
```

## Tests et qualité (comme la CI)

```bash
docker compose run --rm api sh -c "bin/console doctrine:database:create --env=test --if-not-exists && bin/console doctrine:migrations:migrate --env=test -n"
docker compose run --rm api vendor/bin/phpunit
docker compose run --rm api vendor/bin/phpstan analyse
docker compose run --rm api vendor/bin/php-cs-fixer check
```

Seuils imposés en CI sur le domaine et la couche application : couverture 100 %, score de mutation (Infection) ≥ 80 %.

## Contribuer

Branche par sujet, PR vers `main` (protégée, fusion par rebase). Format de commit :

```
AAAA-MM-JJ # N : (type) description
```

Types : `feat`, `fix`, `docs`, `test`, `refactor`, `chore`, `build`, `ci`, `style`, `perf`.
