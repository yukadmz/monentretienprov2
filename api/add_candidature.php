<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');

// Chargement du jeton API depuis api.txt
$expected_token = trim(file_get_contents('cfapi.txt')); // Assurez-vous que le chemin est correct

// Vérification de l'en-tête d'autorisation
$headers = apache_request_headers();

if (!isset($headers['Authorization']) || str_replace('Bearer ', '', $headers['Authorization']) !== $expected_token) {
    http_response_code(403);  // Accès non autorisé
    echo json_encode(["message" => "Access denied"]);
    exit;
}

// Récupérer les données en JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    error_log("Erreur de données JSON : " . file_get_contents('php://input')); // Ajout de logs
    echo json_encode(["message" => "Données invalides"]);
    exit;
}

// Générer un ID unique pour la nouvelle candidature
$id = uniqid();
$nouvelleCandidature = array(
    "id" => $id,
    "applydate" => $input['applydate'],
    "entreprise" => $input['entreprise'],
    "position" => $input['position'],
    "poste" => $input['poste'],
    "lien" => $input['lien'],  
    "contrat" => $input['contrat'],
    "disponibilite" => $input['disponibilite'],
    "contact" => $input['contact'],
    "statut" => $input['statut'],
    "applymethod" => $input['applymethod'],
    "date_entretien" => $input['date_entretien'],
    "deuxieme_entretien" => $input['deuxieme_entretien'],
    "infostatut" => $input['infostatut'],
    "salaire" => $input['salaire'],
    "historique_statut" => array(
        array(
            "date_changement" => date('Y-m-d H:i:s'),
            "statut" => $input['statut']
        )
    )
);

// Charger les candidatures existantes
$candidatures = json_decode(file_get_contents('../assets/data/candidatures.json'), true);

if (!$candidatures) {
    error_log("Erreur lors de la lecture de candidatures.json"); // Ajout de logs
}

// Ajouter la nouvelle candidature au tableau
$candidatures[] = $nouvelleCandidature;

// Sauvegarder les candidatures mises à jour
file_put_contents('../assets/data/candidatures.json', json_encode($candidatures));

echo json_encode(["message" => "Nouvelle candidature ajoutée avec succès"]);
?>