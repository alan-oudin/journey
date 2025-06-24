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

### Sécurité

Les fichiers d'environnement contenant des informations sensibles (`.env`, `.env.development`, `.env.production`) sont exclus du contrôle de version via le fichier `.gitignore`. Seul le fichier `.env.example` est versionné pour servir de modèle.

Cette approche permet de déployer l'application dans différents environnements sans modifier le code source et sans exposer d'informations sensibles.
