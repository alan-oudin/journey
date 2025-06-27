<?php
// database/migrate.php
// Système de migration SQL simple pour MySQL

$host = getenv('DB_HOST') ?: 'localhost';
$db   = getenv('DB_NAME') ?: 'journee_proches';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$port = getenv('DB_PORT') ?: 3306;

// 1. Connexion sans base pour créer la base si besoin
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Base de données '$db' vérifiée/créée.\n";
} catch (PDOException $e) {
    echo "Erreur lors de la création de la base de données : ", $e->getMessage(), "\n";
    exit(1);
}

// 2. Connexion sur la base pour les migrations
try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : ", $e->getMessage(), "\n";
    exit(1);
}

// Créer la table de suivi des migrations si elle n'existe pas
echo "Vérification de la table de migrations...\n";
$pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL,
    migrated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Lister les migrations déjà appliquées
$applied = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

// Lister les fichiers de migration
$migrationsDir = __DIR__ . '/migrations';
$files = glob($migrationsDir . '/*.sql');
// Tri naturel (V1, V2, ...)
natsort($files);

foreach ($files as $file) {
    $name = basename($file);
    if (in_array($name, $applied)) {
        echo "[OK] $name déjà appliquée\n";
        continue;
    }
    echo "[RUN] Application de $name... ";
    $sql = file_get_contents($file);
    try {
        $pdo->exec($sql);
        $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)")->execute([$name]);
        echo "succès.\n";
    } catch (PDOException $e) {
        echo "échec : ", $e->getMessage(), "\n";
        exit(1);
    }
}
echo "Toutes les migrations sont à jour !\n";
