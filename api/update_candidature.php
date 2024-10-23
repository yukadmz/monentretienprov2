<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, PUT');

// Débogage : affichez les en-têtes pour vérification
$headers = apache_request_headers();

// Charger le jeton API depuis cfapi.txt
$expected_token = trim(file_get_contents('cfapi.txt'));

if (!$expected_token) {
    http_response_code(500); // Erreur serveur si le fichier n'est pas trouvé
    echo json_encode(["message" => "Erreur interne : impossible de charger le jeton API."]);
    exit;
}

// Vérification de l'autorisation
if (!isset($headers['Authorization']) || str_replace('Bearer ', '', $headers['Authorization']) !== $expected_token) {
    http_response_code(403);  // Accès non autorisé
    echo json_encode(["message" => "Access denied"]);
    exit;
}

// Récupérer les données en JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    echo json_encode(["message" => "ID ou données invalides"]);
    exit;
}

// Charger les candidatures existantes
$candidatures = json_decode(file_get_contents('../assets/data/candidatures.json'), true);

if ($candidatures === null) {
    error_log("Erreur lors de la lecture de candidatures.json");
    echo json_encode(["message" => "Erreur lors du chargement des candidatures."]);
    exit;
}

// Rechercher la candidature à modifier
foreach ($candidatures as &$candidature) {
    if ($candidature['id'] === $input['id']) {
        // Mettre à jour les détails de la candidature
        $candidature['entreprise'] = $input['entreprise'];
        $candidature['poste'] = $input['poste'];
        $candidature['lien'] = $input['lien'];
        $candidature['statut'] = $input['statut']; // Manquait le point-virgule
        $candidature['applydate'] = $input['applydate']; // Manquait le point-virgule
        $candidature['position'] = $input['position']; // Manquait le point-virgule
        $candidature['contrat'] = $input['contrat']; // Manquait le point-virgule
        $candidature['disponibilite'] = $input['disponibilite']; // Manquait le point-virgule
        $candidature['applymethod'] = $input['applymethod']; // Manquait le point-virgule
        $candidature['date_entretien'] = $input['date_entretien']; // Manquait le point-virgule
        $candidature['salaire'] = $input['salaire']; // Manquait le point-virgule
        
        // Ajouter un nouvel historique du statut
        $candidature['historique_statut'][] = [
            "date_changement" => date('Y-m-d H:i:s'),
            "statut" => $input['statut']
        ];

        // Sauvegarder les candidatures modifiées
        if (file_put_contents('../assets/data/candidatures.json', json_encode($candidatures)) === false) {
            error_log("Erreur lors de la sauvegarde de candidatures.json");
            echo json_encode(["message" => "Erreur lors de la sauvegarde des candidatures."]);
            exit;
        }

        echo json_encode(["message" => "Candidature mise à jour avec succès"]);
        exit;
    }
}

// Si la candidature n'est pas trouvée
echo json_encode(["message" => "Candidature non trouvée"]);
?>
