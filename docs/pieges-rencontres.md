# Pièges rencontrés

| Symptôme | Cause | Contournement | Lien |
| --- | --- | --- | --- |
| Alerte GitGuardian « Generic High Entropy Secret » sur `backend/.env.dev` | La recette Flex de `symfony/framework-bundle` génère un `APP_SECRET` réel dans `.env.dev`, fichier versionné | Ne jamais versionner `APP_SECRET` : le définir dans `backend/.env.dev.local` (ignoré). Relire tout `.env*` avant `git add`. Secret scanning + push protection activés sur GitHub | commit `2026-11-13 # 1` |
| Infection échoue : « Could not validate the PHPUnit configuration file … exceeded the timeout of 2 seconds » | Volume Docker monté depuis Windows trop lent | Copier le code dans le conteneur avant de lancer : `docker run --rm -v "$(pwd -W):/src:ro" onlineplanner-api sh -c "cp -r /src /work && cd /work && vendor/bin/infection"` | — |
| Commandes Docker en Git Bash : chemins réécrits (`/app` → `C:/Program Files/Git/app`) | Conversion automatique de chemins MSYS | Préfixer par `MSYS_NO_PATHCONV=1` et utiliser `$(pwd -W)` | — |
