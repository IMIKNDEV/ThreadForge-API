# Notes de sprint — ThreadForge DevOps

## Séance 1 — Sécurité du compte Azure (Entra ID / MFA)

- Activation du MFA (Multi-Factor Authentication) sur le compte propriétaire Azure via
  Microsoft Authenticator (méthode App, QR code scanné, confirmation par code à 6 chiffres).
- Sauvegarde des codes de récupération dans un gestionnaire de mots de passe.
- Validation du MFA en se déconnectant puis reconnectant sur portal.azure.com : le code
  Authenticator est bien demandé.
- Création d'un utilisateur dédié `devops-<prénom>` dans Microsoft Entra ID, avec mot de
  passe défini manuellement (pas d'auto-génération).
- Attribution du rôle **Owner** à `devops-<prénom>` au scope de la Subscription, via
  Access control (IAM) → Add role assignment → onglet "Privileged administrator roles"
  (et non la barre de recherche classique, qui ne liste que les Job function roles).
- Sélection de la condition "Allow user to assign all roles except privileged administrator
  roles" (obligatoire pour débloquer le bouton Review + assign).
- Première connexion avec `devops-<prénom>` : changement de mot de passe forcé, puis
  configuration d'une nouvelle entrée MFA (distincte du compte propriétaire) dans
  Microsoft Authenticator.
- Vérification des droits : la Subscription est visible, le rôle Owner apparaît bien
  dans Access control (IAM) → Role assignments.
- Test de création/suppression d'un Resource Group (`test-droits`) pour confirmer que
  les permissions fonctionnent réellement, puis suppression immédiate.

**Point clé retenu** : ne jamais travailler au quotidien avec le compte propriétaire —
une session volée sur ce compte peut fermer la Subscription ou changer le moyen de
paiement. Le compte `devops-<prénom>` devient le compte d'usage courant.

## Séance 2 — Provisioning de la VM Azure (Ubuntu + SSH)

- Création du Resource Group `threadforge-rg` dans la région **Belgium Central**
  (région choisie car elle supporte la taille `Standard_B2als_v2` et offre une latence
  acceptable depuis le Maroc).
- Création de la VM `threadforge-vm` :
  - Image : Ubuntu Server 24.04 LTS
  - Taille : `Standard_B2als_v2` (2 vCPU, 4 Go RAM) — burstable, économique, suffisant
    pour Nginx + PHP-FPM + MySQL
  - Disque OS : Standard SSD (LRS), pas Premium (pas de gain réel pour cet usage)
  - Authentification : clé publique SSH (pas de mot de passe)
  - Utilisateur : `azureuser`
  - Ports entrants ouverts : 22 (SSH), 80 (HTTP), 443 (HTTPS)
- Téléchargement de la clé privée `threadforge-key.pem` (disponible une seule fois).
- Sécurisation de la clé en local :
```bash
  chmod 600 ~/.ssh/threadforge-key.pem
```
- Connexion SSH réussie depuis le poste local :
```bash
  ssh -i ~/.ssh/threadforge-key.pem azureuser@<PUBLIC_IP>
```
  Vérification avec `whoami` → retourne bien `azureuser`.
- Ajout d'une entrée dans `~/.ssh/config` pour simplifier les connexions futures
  (`ssh threadforge-vm` au lieu de la commande complète).

**Point clé retenu** : toujours utiliser **Deallocate** (bouton Stop du Portal) en fin
de séance, jamais un `sudo shutdown` depuis l'intérieur de la VM — sinon Azure continue
de facturer le compute alors que la VM est éteinte.

## Séance 3 — Tests et intégration continue (CI)

- Ajout d'un test de type feature dans `tests/` pour vérifier le bon fonctionnement de
  l'endpoint `blueprints`.
- Exécution des tests en local (`php artisan test`) : tous verts.
- Création du workflow GitHub Actions dans `.github/workflows/ci.yml`, qui installe les
  dépendances PHP, configure l'environnement, puis lance automatiquement les tests à
  chaque push/pull request sur `main`.
- Push sur GitHub et vérification dans l'onglet **Actions** : le workflow s'exécute et
  affiche un check vert.
- Commits séparés par étape logique pour garder un historique clair
  (`test: ...`, `ci: ...`, `docs: ...`).

**Point clé retenu** : la CI valide automatiquement que le code fonctionne avant
d'être fusionné — ça remplace le réflexe "je teste à la main avant de push".