<?php
/*
========================================
FICHIER: public/api.php
Version 2.1 - Gestion créneaux détaillés
========================================
*/

// Headers CORS et JSON
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Gestion des requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Configuration base de données
$host = 'localhost';
$dbname = 'journee_proches';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur de connexion à la base de données',
        'details' => $e->getMessage()
    ]);
    exit();
}

// Récupération du path
$path = $_GET['path'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Router principal
try {
    switch ($path) {
        case 'test':
            if ($method === 'GET') {
                echo json_encode([
                    'status' => 'OK',
                    'message' => 'API v2.1 fonctionnelle',
                    'timestamp' => date('Y-m-d H:i:s'),
                    'database' => 'Connecté à ' . $dbname,
                    'version' => '2.1'
                ]);
            } else {
                throw new Exception('Méthode non autorisée pour /test');
            }
            break;

        case 'agents':
            if ($method === 'GET') {
                // Récupérer tous les agents - CORRECTION: bonne table
                $stmt = $pdo->query("
                    SELECT id, code_personnel, nom, prenom, service, nombre_proches, 
                           heure_arrivee, date_inscription, updated_at 
                    FROM agents_inscriptions 
                    ORDER BY nom, prenom
                ");
                $agents = $stmt->fetchAll();
                echo json_encode($agents);

            } elseif ($method === 'POST') {
                // Ajouter un nouvel agent
                $input = json_decode(file_get_contents('php://input'), true);

                if (!$input) {
                    throw new Exception('Données JSON invalides');
                }

                // Validation des champs requis
                $required = ['code_personnel', 'nom', 'prenom', 'service', 'nombre_proches', 'heure_arrivee'];
                foreach ($required as $field) {
                    if (!isset($input[$field])) {
                        throw new Exception("Champ requis manquant: $field");
                    }
                }

                // Validation des données
                if (!preg_match('/^[0-9]{4,8}$/', $input['code_personnel'])) {
                    throw new Exception('Le code personnel doit contenir entre 4 et 8 chiffres');
                }

                if ($input['nombre_proches'] < 0 || $input['nombre_proches'] > 4) {
                    throw new Exception('Le nombre de proches doit être entre 0 et 4');
                }

                // Vérifier que l'agent n'existe pas déjà
                $stmt = $pdo->prepare("SELECT id FROM agents_inscriptions WHERE code_personnel = ?");
                $stmt->execute([$input['code_personnel']]);
                if ($stmt->fetch()) {
                    throw new Exception('Un agent avec ce code personnel est déjà inscrit');
                }

                // Vérifier la capacité du créneau
                $stmt = $pdo->prepare("
                    SELECT COALESCE(SUM(nombre_proches + 1), 0) as personnes_total 
                    FROM agents_inscriptions 
                    WHERE heure_arrivee = ?
                ");
                $stmt->execute([$input['heure_arrivee']]);
                $result = $stmt->fetch();
                $personnesActuelles = $result['personnes_total'];
                $personnesTotal = $personnesActuelles + $input['nombre_proches'] + 1;

                if ($personnesTotal > 14) {
                    throw new Exception("Capacité du créneau dépassée. Places restantes: " . (14 - $personnesActuelles));
                }

                // Insérer l'agent
                $stmt = $pdo->prepare("
                    INSERT INTO agents_inscriptions 
                    (code_personnel, nom, prenom, service, nombre_proches, heure_arrivee) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $input['code_personnel'],
                    strtoupper(trim($input['nom'])),
                    ucfirst(strtolower(trim($input['prenom']))),
                    trim($input['service']),
                    (int)$input['nombre_proches'],
                    $input['heure_arrivee']
                ]);

                $id = $pdo->lastInsertId();

                echo json_encode([
                    'success' => true,
                    'message' => 'Agent inscrit avec succès',
                    'id' => $id,
                    'agent' => [
                        'id' => $id,
                        'code_personnel' => $input['code_personnel'],
                        'nom' => strtoupper(trim($input['nom'])),
                        'prenom' => ucfirst(strtolower(trim($input['prenom']))),
                        'service' => trim($input['service']),
                        'nombre_proches' => (int)$input['nombre_proches'],
                        'heure_arrivee' => $input['heure_arrivee'],
                        'date_inscription' => date('Y-m-d H:i:s')
                    ]
                ]);

            } elseif ($method === 'DELETE') {
                // Supprimer un agent
                $code = $_GET['code'] ?? '';
                if (empty($code)) {
                    throw new Exception('Code personnel manquant pour la suppression');
                }

                // Vérifier que l'agent existe
                $stmt = $pdo->prepare("SELECT id, nom, prenom FROM agents_inscriptions WHERE code_personnel = ?");
                $stmt->execute([$code]);
                $agent = $stmt->fetch();

                if (!$agent) {
                    http_response_code(404);
                    echo json_encode([
                        'error' => 'Agent non trouvé',
                        'code_personnel' => $code
                    ]);
                    return;
                }

                // Supprimer l'agent
                $stmt = $pdo->prepare("DELETE FROM agents_inscriptions WHERE code_personnel = ?");
                $stmt->execute([$code]);

                echo json_encode([
                    'success' => true,
                    'message' => "Agent {$agent['prenom']} {$agent['nom']} supprimé avec succès",
                    'code_personnel' => $code
                ]);

            } else {
                throw new Exception('Méthode non autorisée pour /agents');
            }
            break;

        case 'search':
            if ($method === 'GET') {
                $q = $_GET['q'] ?? '';
                if (empty($q)) {
                    throw new Exception('Paramètre de recherche manquant');
                }

                $stmt = $pdo->prepare("
                    SELECT id, code_personnel, nom, prenom, service, nombre_proches, 
                           heure_arrivee, date_inscription 
                    FROM agents_inscriptions 
                    WHERE code_personnel = ?
                ");
                $stmt->execute([$q]);
                $agent = $stmt->fetch();

                if ($agent) {
                    echo json_encode($agent);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'error' => 'Agent non trouvé',
                        'code_personnel' => $q
                    ]);
                }
            } else {
                throw new Exception('Méthode non autorisée pour /search');
            }
            break;

        case 'creneaux':
            if ($method === 'GET') {
                // Définir tous les créneaux possibles
                $creneauxMatin = ['09:00', '09:20', '09:40', '10:00', '10:20', '10:40', '11:00', '11:20', '11:40'];
                $creneauxApresMidi = ['13:00', '13:20', '13:40', '14:00', '14:20', '14:40', '15:00', '15:20', '15:40'];

                // Récupérer les statistiques actuelles
                $stmt = $pdo->query("
                    SELECT heure_arrivee, 
                           COUNT(*) as agents_inscrits,
                           SUM(nombre_proches + 1) as personnes_total
                    FROM agents_inscriptions 
                    GROUP BY heure_arrivee
                ");
                $stats = [];
                while ($row = $stmt->fetch()) {
                    $stats[$row['heure_arrivee']] = $row;
                }

                // Construire la réponse
                $response = [
                    'matin' => [],
                    'apres-midi' => []
                ];

                foreach ($creneauxMatin as $heure) {
                    $personnesTotal = isset($stats[$heure]) ? (int)$stats[$heure]['personnes_total'] : 0;
                    $agentsInscrits = isset($stats[$heure]) ? (int)$stats[$heure]['agents_inscrits'] : 0;
                    $placesRestantes = max(0, 14 - $personnesTotal);

                    $response['matin'][$heure] = [
                        'agents_inscrits' => $agentsInscrits,
                        'personnes_total' => $personnesTotal,
                        'places_restantes' => $placesRestantes,
                        'complet' => $personnesTotal >= 14
                    ];
                }

                foreach ($creneauxApresMidi as $heure) {
                    $personnesTotal = isset($stats[$heure]) ? (int)$stats[$heure]['personnes_total'] : 0;
                    $agentsInscrits = isset($stats[$heure]) ? (int)$stats[$heure]['agents_inscrits'] : 0;
                    $placesRestantes = max(0, 14 - $personnesTotal);

                    $response['apres-midi'][$heure] = [
                        'agents_inscrits' => $agentsInscrits,
                        'personnes_total' => $personnesTotal,
                        'places_restantes' => $placesRestantes,
                        'complet' => $personnesTotal >= 14
                    ];
                }

                echo json_encode($response);
            } else {
                throw new Exception('Méthode non autorisée pour /creneaux');
            }
            break;

        case 'stats':
            if ($method === 'GET') {
                $stmt = $pdo->query("
                    SELECT 
                        COUNT(*) as total_agents,
                        SUM(nombre_proches) as total_proches,
                        SUM(nombre_proches + 1) as total_personnes,
                        SUM(CASE WHEN heure_arrivee BETWEEN '09:00' AND '11:40' THEN 1 ELSE 0 END) as agents_matin,
                        SUM(CASE WHEN heure_arrivee BETWEEN '13:00' AND '15:40' THEN 1 ELSE 0 END) as agents_apres_midi,
                        SUM(CASE WHEN heure_arrivee BETWEEN '09:00' AND '11:40' THEN (nombre_proches + 1) ELSE 0 END) as personnes_matin,
                        SUM(CASE WHEN heure_arrivee BETWEEN '13:00' AND '15:40' THEN (nombre_proches + 1) ELSE 0 END) as personnes_apres_midi
                    FROM agents_inscriptions
                ");
                $stats = $stmt->fetch();

                // Convertir en entiers
                foreach ($stats as $key => $value) {
                    $stats[$key] = (int)$value;
                }

                echo json_encode($stats);
            } else {
                throw new Exception('Méthode non autorisée pour /stats');
            }
            break;

        default:
            // Gestion du cas où le path est vide ou invalide
            if (empty($path)) {
                // Retourner la liste des endpoints disponibles
                echo json_encode([
                    'error' => 'Endpoint non trouvé',
                    'method' => $method,
                    'path' => $path,
                    'available_endpoints' => [
                        'GET /api.php?path=test',
                        'GET /api.php?path=creneaux',
                        'GET /api.php?path=agents',
                        'POST /api.php?path=agents',
                        'GET /api.php?path=search&q=CODE',
                        'DELETE /api.php?path=agents&code=CODE',
                        'GET /api.php?path=stats'
                    ]
                ]);
            } else {
                throw new Exception("Endpoint non trouvé: /$path");
            }
            break;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'path' => $path,
        'method' => $method
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erreur de base de données',
        'details' => $e->getMessage()
    ]);
}
?>