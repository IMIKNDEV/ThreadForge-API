# Taskboard — ThreadForge, Part 2 : mise en production

**Lancement :** lundi 06/07/2026 – 10:00
**Deadline :** vendredi 10/07/2026 – 14:30
**Mode :** individuel

Objectif de la semaine : ne pas toucher aux fonctionnalités de ThreadForge, mais construire la chaîne qui l'amène jusqu'en production (tests automatiques → CI → déploiement).

---

## 📋 À faire

### Phase 0 — Tests Pest (obligatoire)
- [ ] Test login : bons identifiants → 200 + token
- [ ] Test login : mauvais mot de passe → 401
- [ ] Test `GET /api/blueprints` sans token → 401
- [ ] Test `GET /api/blueprints` avec `Sanctum::actingAs` → 200 + bonne structure JSON
- [ ] Test `POST /api/blueprints` avec champ obligatoire manquant → 422 + erreur sur le bon champ
- [ ] Test `POST /api/content/repurpose` → 202 + `Queue::fake()` + `assertPushed()` sur le Job de génération
- [ ] Être capable d'expliquer la différence feature test / unit test
- [ ] Être capable d'expliquer pourquoi on fake la queue et l'IA dans les tests

### Phase 1 — CI avec GitHub Actions
- [ ] Créer `.github/workflows/ci.yml`
- [ ] Étape : installer PHP + dépendances (composer)
- [ ] Étape : lancer `php artisan test`
- [ ] Push et vérifier le check vert dans l'onglet Actions
- [ ] Casser un test volontairement, push, observer le check rouge
- [ ] Corriger le test, push, observer le retour au vert
- [ ] Être capable d'expliquer le fichier `.yml` étape par étape
- [ ] Être capable d'expliquer pourquoi la CI n'a pas besoin de la clé Groq

### Phase 2 — Déploiement Azure (si le rythme le permet)
- [ ] Créer une VM Ubuntu sur Azure
- [ ] Se connecter en SSH
- [ ] Installer PHP, Composer, MySQL sur le serveur
- [ ] Cloner le repo sur le serveur
- [ ] Préparer le `.env` de production (`APP_DEBUG=false`, secrets non commités)
- [ ] Lancer les migrations en production
- [ ] Lancer le worker de queue en production
- [ ] `composer install --no-dev`
- [ ] `php artisan config:cache`
- [ ] Appeler l'API en ligne depuis Postman ou le téléphone
- [ ] Vérifier que `.env` n'est pas sur GitHub

### Bonus ++
- [ ] `Dockerfile` + `docker-compose` pour ThreadForge
- [ ] Job : ajouter `tries`, `backoff`
- [ ] Job : méthode `failed()` → statut `echoue`
- [ ] Ajouter Pint comme étape dans la CI
- [ ] Test bonus : contenu vide
- [ ] Test bonus : blueprint inexistant
- [ ] Test bonus : post archivé

### Livrables
- [ ] Dossier `docs/` créé
- [ ] Capture des tests verts en local
- [ ] Capture de la CI verte sur GitHub
- [ ] Capture de l'API en ligne (si déployé) + URL
- [ ] README à jour : comment lancer les tests
- [ ] README à jour : URL de l'API en ligne (si déployé)

---

## 🔨 En cours

*(déplace ici les tâches sur lesquelles tu travailles actuellement)*

---

## ✅ Terminé

*(déplace ici les tâches finies et vérifiées)*

---

## Rappels utiles

- **Un feature test** envoie une vraie requête HTTP et vérifie la réponse, comme le ferait un utilisateur. **Un unit test** vérifie une portion isolée du code, sans démarrer Laravel.
- On fake la queue et l'IA dans les tests parce qu'un vrai appel à Groq serait lent, payant, et non-déterministe. Le test doit vérifier ton code, pas le service externe.
- En production : `APP_DEBUG=false` (sinon les erreurs exposent le code et les secrets), et le fichier `.env` ne va jamais sur GitHub.
- Commits attendus, un par étape, avec préfixes clairs : `test: ...`, `ci: ...`.
- Le point le plus important de l'évaluation : être capable d'expliquer ton code et tes choix à l'oral, sans le relire ligne par ligne.

## Checklist de la démo live

- [ ] `php artisan test` tout vert en local, avec explication feature vs unit test
- [ ] Explication du fake queue/IA
- [ ] Lecture commentée du fichier `.yml`
- [ ] Démo en direct : test cassé → push → CI rouge → correction → CI verte
- [ ] Si déployé : appel de l'API en ligne depuis Postman