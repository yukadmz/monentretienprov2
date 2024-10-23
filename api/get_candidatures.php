<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Charger le jeton API depuis api.txt
$expected_token = trim(file_get_contents('cfapi.txt')); // Assurez-vous que le chemin est correct

// Vérification des en-têtes
$headers = apache_request_headers();

if (!isset($headers['Authorization']) || str_replace('Bearer ', '', $headers['Authorization']) !== $expected_token) {
    http_response_code(403);  // Accès non autorisé
    echo json_encode(["message" => "Access denied"]);
    exit;
}

// Charger les candidatures existantes
$candidatures = json_decode(file_get_contents('../assets/data/candidatures.json'), true);

// Vérification si les candidatures ont été chargées avec succès
if ($candidatures === null) {
    error_log("Erreur lors de la lecture de candidatures.json"); // Ajout de logs pour le débogage
    echo json_encode(["message" => "Erreur lors du chargement des candidatures."]);
    exit;
}

// Répondre avec les candidatures
echo json_encode($candidatures);
?>