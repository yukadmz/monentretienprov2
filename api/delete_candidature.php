<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');

// Charger le jeton API depuis api.txt
$expected_token = trim(file_get_contents('cfapi.txt')); // Assurez-vous que le chemin est correct

// Vérification de l'autorisation
$headers = apache_request_headers();
if (!isset($headers['Authorization']) || str_replace('Bearer ', '', $headers['Authorization']) !== $expected_token) {
    http_response_code(403);  // Accès non autorisé
    echo json_encode(["message" => "Access denied"]);
    exit;
}

// Récupérer les données en JSON
$input = json_decode(file_get_contents('php://input'), true);

// Vérifiez si l'ID est valide
if (!$input || !isset($input['id'])) {
    echo json_encode(["message" => "ID manquant ou invalide"]);
    exit;
}

$id = $input['id'];

// Charger les candidatures existantes
$candidatures = json_decode(file_get_contents('../assets/data/candidatures.json'), true);

// Vérifiez si les candidatures ont été chargées avec succès
if ($candidatures === null) {
    error_log("Erreur lors de la lecture de candidatures.json"); // Ajout de logs pour le débogage
    echo json_encode(["message" => "Erreur lors du chargement des candidatures."]);
    exit;
}

// Filtrer pour garder les candidatures qui ne correspondent pas à l'ID
$candidatures = array_filter($candidatures, function ($candidature) use ($id) {
    return $candidature['id'] !== $id;
});

// Sauvegarder les candidatures restantes
file_put_contents('../assets/data/candidatures.json', json_encode(array_values($candidatures))); // Reindexer les clés

echo json_encode(["message" => "Candidature supprimée avec succès"]);
?>