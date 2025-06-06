# Guide pour initialiser et committer un projet Git avec PhpStorm

## Prérequis
- Assure-toi que Git est installé sur ton ordinateur. Vérifie avec :
  ```bash
  git --version
  ```
- Configure ton identité Git (si ce n'est pas déjà fait) :
  ```bash
  git config --global user.name "Alan Oudin"
  git config --global user.email "ton.email@example.com"
  ```
- Assure-toi d'avoir un compte GitHub et un dépôt créé à l'adresse : `https://github.com/alan-oudin/journey.git`.

## Étapes via la ligne de commande (Terminal dans PhpStorm)

1. **Ouvrir le terminal dans PhpStorm** :
   - Va dans `View > Tool Windows > Terminal` ou utilise le raccourci `Alt+F12`.

2. **Naviguer vers le dossier du projet** :
   - Si ton projet n'est pas encore ouvert, utilise :
     ```bash
     cd chemin/vers/ton/projet
     ```

3. **Initialiser un dépôt Git** :
   - Crée un dépôt Git vide dans le dossier de ton projet :
     ```bash
     git init
     ```

4. **Ajouter tous les fichiers au suivi Git** :
   - Pour ajouter tous les fichiers du projet :
     ```bash
     git add .
     ```
   - Pour ajouter un fichier spécifique :
     ```bash
     git add nom_du_fichier
     ```

5. **Créer le premier commit** :
   - Valide les fichiers ajoutés avec un message descriptif :
     ```bash
     git commit -m "Initial commit"
     ```

6. **Associer le dépôt distant** :
   - Lie ton dépôt local au dépôt GitHub :
     ```bash
     git remote add origin https://github.com/alan-oudin/journey.git
     ```

7. **Vérifier la configuration du dépôt distant** :
   - Vérifie que l'URL est correcte :
     ```bash
     git remote -v
     ```

8. **Pousser vers le dépôt distant** :
   - Envoie ton commit vers la branche principale (généralement `main` ou `master`) :
     ```bash
     git push -u origin main
     ```
   - **Note** : Si la branche principale est `master`, utilise :
     ```bash
     git push -u origin master
     ```

9. **Vérifier l'état du dépôt** :
   - Vérifie les fichiers modifiés ou en attente avec :
     ```bash
     git status
     ```

## Étapes via l'interface graphique de PhpStorm

1. **Ouvrir ou créer un projet** :
   - Ouvre ton projet dans PhpStorm via `File > Open` ou crée un nouveau projet.

2. **Activer le contrôle de version** :
   - Va dans `VCS > Enable Version Control Integration`.
   - Sélectionne `Git` dans la liste déroulante et clique sur `OK`. Cela initialise un dépôt Git (équivalent à `git init`).

3. **Ajouter les fichiers au commit** :
   - Ouvre la fenêtre de commit avec `Ctrl+K` ou `VCS > Commit`.
   - Dans la fenêtre, coche les fichiers que tu veux inclure (ou tous pour ajouter l'ensemble du projet).

4. **Faire un commit** :
   - Dans la fenêtre de commit, saisis un message descriptif (ex. : "Initial commit").
   - Clique sur `Commit` ou utilise `Ctrl+K`. Cela équivaut à `git commit -m "message"`.

5. **Ajouter le dépôt distant** :
   - Va dans `VCS > Git > Remotes`.
   - Clique sur le bouton `+` et ajoute l'URL du dépôt : `https://github.com/alan-oudin/journey.git`.
   - Nomme-le `origin` (par défaut) et clique sur `OK`. Cela équivaut à `git remote add origin`.

6. **Pousser vers le dépôt distant** :
   - Ouvre la fenêtre de push avec `Ctrl+Shift+K` ou `VCS > Git > Push`.
   - Sélectionne la branche (`main` ou `master`) et clique sur `Push`.
   - Si demandé, saisis tes identifiants GitHub ou configure un token d'accès personnel.

## Résolution des problèmes courants
- **Erreur d'authentification lors du push** :
  - Vérifie que tes identifiants GitHub sont corrects dans PhpStorm (`File > Settings > Version Control > GitHub`).
  - Si GitHub utilise un token d'accès personnel, configure-le dans PhpStorm ou utilise une clé SSH.
  - Pour SSH, génère une clé avec :
    ```bash
    ssh-keygen -t ed25519 -C "ton.email@example.com"
    ```
    Puis ajoute la clé publique à GitHub et configure l'URL du dépôt en SSH :
    ```bash
    git remote set-url origin git@github.com:alan-oudin/journey.git
    ```
- **Branche principale non reconnue** :
  - Vérifie le nom de la branche principale sur GitHub (`main` ou `master`) et adapte la commande `git push`.
- **Conflits ou fichiers non suivis** :
  - Utilise `git status` pour voir les fichiers non suivis ou modifiés.
  - Ajoute les fichiers nécessaires avec `git add` ou ignore-les via un fichier `.gitignore`.

## Conseils
- **Raccourcis PhpStorm** :
  - `Ctrl+K` : Ouvre la fenêtre de commit.
  - `Ctrl+Shift+K` : Ouvre la fenêtre de push.
  - `Alt+9` : Ouvre l'onglet Git pour voir l'historique des commits.
- **Fichier .gitignore** :
  - Crée un fichier `.gitignore` dans la racine du projet pour exclure les fichiers inutiles (ex. : dossiers de cache, fichiers temporaires). Exemple pour un projet PHP :
    ```plaintext
    /vendor/
    .idea/
    *.log
    ```
