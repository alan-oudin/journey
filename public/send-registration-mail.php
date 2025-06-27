<?php
// public/send-registration-mail.php
header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fonction utilitaire pour loguer dans plusieurs emplacements
function log_debug($message) {
    $log1 = __DIR__ . '/mail_debug.log';
    $log2 = sys_get_temp_dir() . '/mail_debug.log';
    $ok1 = @file_put_contents($log1, $message . "\n", FILE_APPEND);
    $ok2 = @file_put_contents($log2, $message . "\n", FILE_APPEND);
    if ($ok1 === false && $ok2 === false) {
        error_log("[PHPMailer] Impossible d'écrire dans mail_debug.log : $message");
    }
}

// Ajout d'un formulaire HTML simple pour tester l'envoi de mail
if ($_SERVER['REQUEST_METHOD'] === 'GET' && empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Test envoi mail</title></head><body>';
    echo '<h2>Test envoi d\'email</h2>';
    echo '<form method="post" action="" style="max-width:400px;margin:2em auto;">';
    echo '<label>Nom : <input type="text" name="nom" required></label><br><br>';
    echo '<label>Email (prenom.nom ou prenom.nom@sncf.fr) : <input type="text" name="email" required></label><br><br>';
    echo '<button type="submit">Envoyer un mail de test</button>';
    echo '</form>';
    echo '</body></html>';
    exit;
}

// Gestion du POST classique (formulaire HTML)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false)) {
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $nom = isset($_POST['nom']) ? $_POST['nom'] : '';
    $input = ['email' => $email, 'nom' => $nom];
}

if (!isset($input)) {
    $input = json_decode(file_get_contents('php://input'), true);
}
$email = isset($input['email']) ? $input['email'] : '';
$nom = isset($input['nom']) ? $input['nom'] : '';
if (!$email || !$nom) {
    // Si la requête vient d'un formulaire HTML, afficher un message HTML
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false)) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<p style="color:red;font-weight:bold;">Erreur : Email et nom requis.</p>';
        echo '<a href="send-registration-mail.php">Retour au formulaire</a>';
        exit;
    }
    http_response_code(400);
    echo json_encode(['error' => 'Email et nom requis']);
    exit;
}

// Compléter l'adresse email avec le domaine SNCF si besoin
$fullEmail = (strpos($email, '@') !== false) ? $email : $email . '@sncf.fr';

// Vérification stricte : format prenom.nom@sncf.fr (lettres, chiffres et tirets acceptés)
if (!preg_match('/^[a-zA-Z0-9-]+\.[a-zA-Z0-9-]+@sncf\.fr$/', $fullEmail)) {
    http_response_code(400);
    echo json_encode(['error' => "Le format de l'adresse doit être prenom.nom@sncf.fr (lettres, chiffres et tirets autorisés)."]);
    exit;
}

// Charger la configuration Mailtrap si présente
$mailConfig = [];
if (file_exists(__DIR__ . '/../config.mailtrap.php')) {
    $mailConfig = include __DIR__ . '/../config.mailtrap.php';
}

$mail = new PHPMailer(true);
try {
    // Utilisation de la config Mailtrap si disponible, sinon valeurs par défaut
    $mail->isSMTP();
    $mail->Host = $mailConfig['host'] ?? 'sandbox.smtp.mailtrap.io';
    $mail->Port = $mailConfig['port'] ?? 2525;

    $mail->SMTPAuth = $mailConfig['SMTPAuth'] ?? true;
    $mail->SMTPAutoTLS = $mailConfig['SMTPAutoTLS'] ?? false;
    $mail->SMTPSecure = $mailConfig['SMTPSecure'] ?? 'tls';

    $mail->Username = $mailConfig['username'] ?? '';
    $mail->Password = $mailConfig['password'] ?? '';
    $mail->setFrom($mailConfig['from_email'] ?? 'mailtrap@demomailtrap.com', $mailConfig['from_name'] ?? 'Test Mailtrap');
    $mail->CharSet = 'UTF-8';
    // Utiliser une adresse d'expéditeur autorisée par Mailtrap
    $mail->addAddress($fullEmail);
    $mail->Subject = 'Bienvenue sur notre plateforme';
    $mail->Body = "Bonjour $nom,\n\nMerci pour votre inscription !";
    $mail->isHTML(false);
    $mail->send();
    log_debug("Envoi réussi à : $fullEmail pour $nom");
    // Affichage direct pour debug (sera visible dans la réponse HTTP)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false)) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<p style="color:green;font-weight:bold;">Email envoyé avec succès à ' . htmlspecialchars($fullEmail) . '.</p>';
        echo '<a href="send-registration-mail.php">Retour au formulaire</a>';
        exit;
    } else {
        echo json_encode([
            'message' => 'Email envoyé avec succès',
            'debug_email_utilise' => $fullEmail
        ]);
        exit;
    }

    /*
    // --- Configuration SNCF (commentée pour tests locaux) ---
    $mail->isSMTP();
    $mail->Host = 'messagerie.sncf.fr';
    $mail->Port = 25;
    $mail->SMTPAuth = true;
    $mail->SMTPAutoTLS = false;
    $mail->SMTPSecure = false;
    $mail->Username = 'fleet-mgr_svc';
    $mail->Password = '847HFT?PvC!wiOIb,2TWcf7r478iopMl.@60';
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('cen_cvl_stf_sct@sncf.fr', 'STF SCT Fleet manager');
    $mail->addAddress($fullEmail);
    $mail->Subject = 'Bienvenue sur notre plateforme';
    $mail->Body = "Bonjour $nom,\n\nMerci pour votre inscription !";
    $mail->isHTML(false);
    $mail->send();
    */
} catch (Exception $e) {
    log_debug("Erreur d'envoi à : $fullEmail pour $nom : " . $mail->ErrorInfo);
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false)) {
        header('Content-Type: text/html; charset=UTF-8');
        echo '<p style="color:red;font-weight:bold;">Erreur lors de l\'envoi de l\'email : ' . htmlspecialchars($mail->ErrorInfo) . '</p>';
        echo '<a href="send-registration-mail.php">Retour au formulaire</a>';
        exit;
    } else {
        http_response_code(500);
        echo json_encode(['error' => "Erreur lors de l'envoi de l'email", 'details' => $mail->ErrorInfo]);
        exit;
    }
}
