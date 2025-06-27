## Structure du projet et particularités

### Arborescence principale

```
journey/
├── composer.json           # Dépendances PHP (backend)
├── package.json            # Dépendances Node.js (frontend Vue.js)
├── vite.config.js          # Configuration Vite (build/dev serveur Vue)
├── README.md               # Documentation du projet
├── database/               # Scripts SQL, batch d'import, migrations
│   ├── import_database.bat
│   └── localhost_journee_proches.sql
├── public/                 # Fichiers accessibles publiquement (API, tests)
│   ├── api.php             # Point d'entrée backend/API PHP
│   ├── send-registration-mail.php
│   ├── test-db.php         # Script de test de connexion DB
│   └── test-wamp.php       # Script de test spécifique WAMP
├── src/                    # Code source Vue.js (frontend)
│   ├── App.vue
│   ├── main.js
│   ├── assets/             # Fichiers statiques (CSS, images)
│   ├── components/         # Composants Vue réutilisables
│   ├── router/             # Configuration du routage Vue
│   ├── stores/             # Stores Pinia (état global)
│   └── views/              # Pages principales de l'application
├── vendor/                 # Dépendances PHP installées par Composer
└── emails/                 # (Potentiellement) templates ou scripts liés aux emails

```

### Particularités du projet

- **Frontend** :
  - Utilise Vue.js avec Vite pour le développement et le build.
  - Organisation modulaire : composants, vues, stores (Pinia), assets, router.
  - Configuration multi-environnements via fichiers `.env`.

- **Backend** :
  - API PHP unique (`public/api.php`) pour la gestion des requêtes.
  - Utilisation de Composer pour la gestion des dépendances PHP (ex : PHPMailer pour l'envoi d'emails).
  - Scripts de test et d'envoi d'emails dans `public/`.

- **Base de données** :
  - Scripts SQL versionnés dans `database/`.
  - Batch d'importation rapide pour Windows (`import_database.bat`).

- **Sécurité & bonnes pratiques** :
  - Variables sensibles dans des fichiers `.env` (non versionnés).
  - Exemples de configuration fournis (`.env.example`).
  - Tests de configuration et de connexion à la base de données accessibles via le navigateur.

- **Déploiement** :
  - Compatible WAMP/XAMPP (Windows) et Linux/Mac.
  - Prévu pour fonctionner en local ou sur VM, avec adaptation facile à Docker.

---

# Déploiement de la Base de Données - Journée des Proches

## Méthodes de Déploiement de la Base de Données

Voici les méthodes recommandées pour déployer la base de données de l'application "Journée des Proches", classées par ordre de préférence selon les différents environnements.

### 1. Utilisation de Scripts SQL avec Gestion de Version

**Méthode recommandée pour tous les environnements**

- **Description** : Utiliser le fichier SQL existant (`localhost_journee_proches.sql`) comme base pour créer des scripts de migration versionnés.
- **Étapes** :
  1. Organiser les scripts SQL dans un dossier `database/migrations` avec un système de versionnage (ex: `V1_initial_schema.sql`, `V2_add_new_field.sql`).
  2. Créer un script d'installation qui exécute les migrations dans l'ordre.
  3. Documenter chaque changement de schéma.

- **Avantages** :
  - Traçabilité des modifications
  - Facilité de mise à jour incrémentale
  - Compatible avec tous les environnements
  - Possibilité d'automatisation

### 2. Déploiement via phpMyAdmin

**Idéal pour les environnements de développement et de test**

- **Description** : Utiliser l'interface phpMyAdmin pour importer le fichier SQL.
- **Étapes** :
  1. Accéder à phpMyAdmin (http://localhost/phpmyadmin/ sur WAMP/XAMPP)
  2. Créer une base de données nommée `journee_proches` si elle n'existe pas
  3. Sélectionner la base de données et cliquer sur "Importer"
  4. Choisir le fichier `database/localhost_journee_proches.sql`
  5. Cliquer sur "Exécuter"

- **Avantages** :
  - Interface graphique intuitive
  - Visualisation immédiate des résultats
  - Pas besoin de ligne de commande

### 3. Déploiement via Ligne de Commande MySQL

**Recommandé pour les environnements de production et l'automatisation**

- **Description** : Utiliser la ligne de commande MySQL pour importer le fichier SQL.
- **Étapes** :
  ```bash
  # Windows (si mysql est dans le PATH)
  mysql -u root -p journee_proches < C:\wamp64\www\journey\database\localhost_journee_proches.sql

  # Windows (avec chemin complet pour WAMP)
  C:\wamp64\bin\mysql\mysql<version>\bin\mysql.exe -u root -p journee_proches < C:\wamp64\www\journey\database\localhost_journee_proches.sql

  # Windows (avec chemin complet pour XAMPP)
  C:\xampp\mysql\bin\mysql.exe -u root -p journee_proches < C:\wamp64\www\journey\database\localhost_journee_proches.sql

  # Linux/Mac
  mysql -u root -p journee_proches < /wamp64/www/journey/database/localhost_journee_proches.sql
  ```

  > **Note**: Remplacez `<version>` par votre version de MySQL (ex: `mysql9.1.0`, `mysql5.7.36`). Vous pouvez vérifier la version installée en regardant les dossiers dans `C:\wamp64\bin\mysql\`.

  > **Alternative**: Si vous rencontrez des problèmes avec la commande MySQL, vous pouvez utiliser le script batch fourni dans le dossier `database` ou utiliser phpMyAdmin comme décrit dans la méthode 2.

  > **Script automatisé**: Un script batch `database\import_database.bat` a été créé pour faciliter l'importation. Double-cliquez simplement sur ce fichier pour importer automatiquement la base de données.

- **Avantages** :
  - Facilement scriptable pour l'automatisation
  - Idéal pour les déploiements CI/CD
  - Pas de dépendance à une interface graphique

### 4. Utilisation d'Outils de Migration de Base de Données

**Pour les projets plus complexes ou en équipe**

- **Description** : Utiliser des outils spécialisés comme Flyway, Liquibase ou Phinx pour gérer les migrations.
- **Étapes** :
  1. Installer l'outil de migration choisi
  2. Configurer l'outil pour pointer vers votre base de données
  3. Convertir le schéma existant en format de migration initial
  4. Exécuter les migrations

- **Avantages** :
  - Gestion avancée des versions
  - Rollback possible
  - Validation des migrations
  - Intégration avec les pipelines CI/CD

### 5. Conteneurisation avec Docker

**Pour les environnements modernes et le développement en équipe**

- **Description** : Utiliser Docker pour créer un conteneur MySQL/MariaDB avec la base de données préchargée.
- **Étapes** :
  1. Créer un Dockerfile ou utiliser une image MySQL/MariaDB officielle
  2. Configurer le volume pour persister les données
  3. Ajouter le script SQL dans le dossier d'initialisation
  4. Utiliser docker-compose pour orchestrer l'application et la base de données

- **Avantages** :
  - Environnement isolé et reproductible
  - Facilité de partage entre développeurs
  - Cohérence entre les environnements

## Déploiement et gestion de la base de données avec migrations

### Création et mise à jour automatique de la base de données

L'application utilise un système de migrations SQL pour créer et faire évoluer la base de données automatiquement.

#### 1. Organisation des migrations

- Les scripts SQL sont placés dans le dossier `database/migrations/` et nommés avec un numéro de version croissant, par exemple :
  - `V1__initial_schema.sql` (création des tables de base)
  - `V2__ajout_colonne_check_utilisateur.sql` (ajout d'une colonne)
- Chaque fichier correspond à une évolution du schéma.

#### 2. Exécution des migrations

- Le script `database/migrate.php` applique automatiquement toutes les migrations non encore exécutées.
- Il crée la base de données si elle n'existe pas, puis applique les scripts dans l'ordre.

**Pour initialiser ou mettre à jour la base de données, exécute simplement :**

```bash
php database/migrate.php
```

- Le script se connecte à la base (infos dans `.env`), crée la base si besoin, puis applique les migrations.
- L'état d'avancement est enregistré dans la table `migrations`.

#### 3. Ajouter une migration

1. Crée un nouveau fichier SQL dans `database/migrations/` (ex : `V3__nouvelle_evolution.sql`).
2. Ajoute tes instructions SQL (ALTER, CREATE, etc.).
3. Relance `php database/migrate.php` pour appliquer la migration.

#### 4. Avantages

- Création automatique de la base de données
- Historique et traçabilité des évolutions
- Facilité de déploiement sur tout environnement (local, VM, CI/CD)
- Pas besoin de phpMyAdmin ni de ligne de commande MySQL manuelle

#### 5. Méthodes alternatives

- Import manuel via phpMyAdmin ou ligne de commande (voir plus bas dans ce README)
- Script batch Windows : `database/import_database.bat`

---

## Recommandations pour la Sécurité

1. **Modifier les identifiants par défaut** : Changer les identifiants de connexion à la base de données (actuellement `root` sans mot de passe) dans le fichier `api.php`.
2. **Utiliser des variables d'environnement** : Stocker les informations de connexion dans des variables d'environnement plutôt que directement dans le code.
3. **Créer un utilisateur dédié** : Créer un utilisateur MySQL spécifique avec des privilèges limités pour l'application.
4. **Sauvegardes régulières** : Mettre en place un système de sauvegarde automatique de la base de données.

## Configuration Multi-environnements

L'application est configurée pour fonctionner avec différents environnements (développement, production) grâce à des fichiers de variables d'environnement.

### Fichiers d'environnement

Les fichiers suivants ont été mis en place :

- `.env` : Configuration par défaut, chargée automatiquement
- `.env.development` : Configuration spécifique à l'environnement de développement (WAMP)
- `.env.production` : Configuration spécifique à l'environnement de production (XAMPP sur VM)
- `.env.example` : Exemple de configuration (à copier pour créer votre propre fichier)

Ces fichiers contiennent les variables d'environnement nécessaires au fonctionnement de l'application, notamment :

```
# URL de l'API pour le frontend
VITE_API_URL=http://localhost/journey/public/api.php

# Configuration de la base de données
DB_HOST=localhost
DB_NAME=journee_proches
DB_USER=root
DB_PASSWORD=
```

### Configuration spécifique pour WAMP et XAMPP

#### Configuration WAMP (Développement)

Le fichier `.env.development` est configuré spécifiquement pour WAMP avec Apache sur port 8080 et MySQL sur port 3306 :

```
# Configuration de l'environnement de développement avec WAMP
# URL de l'API pour le frontend avec WAMP (port 8080)
VITE_API_URL=http://localhost:8080/journey/public/api.php

# Configuration de la base de données pour WAMP
DB_HOST=localhost
DB_PORT=3306
DB_NAME=journee_proches
DB_USER=root
DB_PASSWORD=
```

#### Configuration XAMPP (Production sur VM)

Le fichier `.env.production` est configuré pour XAMPP sur une VM :

```
# Configuration de l'environnement de production avec XAMPP sur VM
# URL de l'API pour le frontend avec XAMPP
VITE_API_URL=/journey/public/api.php

# Configuration de la base de données pour XAMPP
DB_HOST=localhost
DB_NAME=journee_proches
DB_USER=root
DB_PASSWORD=
# Remplacez les valeurs ci-dessus par vos identifiants XAMPP réels
```

### Utilisation des environnements

#### Pour le développement avec WAMP

```bash
# Démarrer le serveur de développement avec les variables de développement
npm run dev

# Construire l'application avec les variables de développement
npm run build:dev
```

#### Pour la production avec XAMPP

```bash
# Construire l'application avec les variables de production
npm run build
# ou
npm run build:prod
```

### Configuration du backend (PHP)

Le fichier `api.php` a été modifié pour charger automatiquement les variables d'environnement depuis le fichier `.env` :

```php
// Charger les variables d'environnement
$envFile = __DIR__ . '/../.env';
loadEnv($envFile);

// Configuration base de données
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'journee_proches';
$username = $_ENV['DB_USER'] ?? getenv('DB_USER') ?? 'root';
$password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';
```

### Test de la configuration

Deux scripts de test ont été créés pour vérifier la connexion à la base de données et détecter automatiquement votre environnement :

#### Test pour WAMP (port 8080)

1. Accédez à `http://localhost:8080/journey/public/test-wamp.php` dans votre navigateur
2. Le script affichera des informations détaillées sur :
   - L'environnement (PHP, MySQL, serveur web)
   - La détection de WAMP avec Apache sur port 8080
   - La connexion à la base de données MySQL sur port 3306
   - Les variables d'environnement chargées depuis .env.development

Ce test est spécifiquement conçu pour la configuration WAMP avec Apache sur port 8080 et MySQL sur port 3306.

#### Test général

1. Accédez à `http://localhost/journey/public/test-db.php` dans votre navigateur (ou utilisez le port approprié pour votre serveur)
2. Le script affichera des informations détaillées sur :
   - L'environnement (PHP, MySQL, serveur web)
   - La détection automatique de WAMP ou XAMPP
   - La connexion à la base de données
   - Les variables d'environnement chargées

Ces tests sont particulièrement utiles pour vérifier que votre configuration fonctionne correctement avant de déployer l'application.

---

## Commandes de développement et gestion du projet

### Installation des dépendances

- **npm install**
  - Installe toutes les dépendances Node.js nécessaires au projet (frontend Vue.js).
  - À exécuter après avoir cloné le dépôt ou après modification du fichier `package.json`.
  - **Implication** : Nécessaire avant toute commande de build ou de lancement du serveur de dev.

- **composer update**
  - Met à jour les dépendances PHP (backend/API).
  - À exécuter si le backend PHP évolue ou après modification de `composer.json`.
  - **Implication** : Assure la compatibilité et la sécurité des librairies PHP utilisées.

### Lancement et build du projet

- **npm run dev**
  - Démarre le serveur de développement Vite pour Vue.js.
  - Accès généralement via http://localhost:5173 ou http://localhost:8080 selon la configuration.
  - **Implication** : Permet le développement avec rechargement à chaud (hot reload).

- **npm run build:dev**
  - Génère une version optimisée du projet pour l’environnement de développement (dossier `dist/`).
  - **Implication** : Permet de tester le build sans activer le mode production complet.

- **npm start**
  - Démarre l’application en mode production (si configuré dans `package.json`).
  - **Implication** : Sert à simuler le comportement final de l’application.

### Gestion des serveurs Vue.js

- **Trouver les serveurs Vue actifs** :
  - `npx vite --host` (affiche les serveurs Vite actifs)
  - Ou, sous Windows : `tasklist | findstr node` (liste les processus Node.js)
  - **Implication** : Utile pour vérifier si un serveur de dev tourne déjà.

- **Arrêter un serveur Vue** :
  - Dans le terminal où il tourne : `Ctrl+C`
  - Ou, sous Windows : `taskkill /F /IM node.exe` (arrête tous les processus Node.js)
  - **Implication** : Nécessaire avant de relancer un serveur ou libérer des ports.

---

## Ajout d'un administrateur via l'interface web

Pour ajouter un administrateur à l'application, une page dédiée est disponible.

### Étapes :

1. Assurez-vous que la base de données et la table `admins` sont bien créées (voir section sur les migrations).
2. Lancez votre serveur local (Laragon, WAMP, XAMPP, etc.).
3. Ouvrez votre navigateur et accédez à l'URL suivante :
   
   ```
   http://localhost/journey/public/add-admin.php
   ```
4. Remplissez le formulaire avec le login et le mot de passe souhaités pour l'administrateur.
5. Validez. Le mot de passe sera automatiquement hashé et l'admin ajouté à la base de données.

- Si le login existe déjà, un message d'erreur s'affichera.
- En cas de succès, un message de confirmation apparaîtra.

> Cette méthode est recommandée pour créer rapidement un ou plusieurs comptes administrateurs sans passer par des requêtes SQL manuelles.

---
