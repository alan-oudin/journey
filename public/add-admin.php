<?php
// public/add-admin.php
// Page pour ajouter un admin via un formulaire (login + mot de passe)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $errors = [];

    if ($username === '' || $password === '') {
        $errors[] = 'Login et mot de passe obligatoires.';
    }

    if (empty($errors)) {
        // Connexion à la BDD (infos depuis .env ou fallback)
        $host = getenv('DB_HOST') ?: 'localhost';
        $db   = getenv('DB_NAME') ?: 'journee_proches';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $port = getenv('DB_PORT') ?: 3306;
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        } catch (PDOException $e) {
            $errors[] = 'Erreur de connexion à la base de données : ' . $e->getMessage();
        }
    }

    if (empty($errors)) {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM admins WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Ce login existe déjà.';
        } else {
            // Hash du mot de passe
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // Correction : laisser MySQL gérer l'auto-incrément de l'id
            $stmt = $pdo->prepare('INSERT INTO admins (username, password) VALUES (?, ?)');
            if ($stmt->execute([$username, $hash])) {
                $success = true;
            } else {
                $errors[] = 'Erreur lors de l\'ajout de l\'admin.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un administrateur</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 2em; }
        form { max-width: 400px; margin: auto; }
        label { display: block; margin-top: 1em; }
        input[type=text], input[type=password] { width: 100%; padding: 0.5em; }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <h2>Ajouter un administrateur</h2>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?>
        </div>
    <?php elseif (!empty($success)): ?>
        <div class="success">Administrateur ajouté avec succès !</div>
    <?php endif; ?>
    <form method="post">
        <label for="username">Login :</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Ajouter</button>
    </form>
</body>
</html>
