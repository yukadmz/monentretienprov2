<?php
// Lecture du fichier JSON des correspondances ville-département
$villesDepartements = [];
$jsonFile = 'assets/data/villes.json'; // Chemin vers le fichier JSON
$jsonData = file_get_contents($jsonFile);
if ($jsonData !== false) {
    $villesData = json_decode($jsonData, true);
    foreach ($villesData as $ville) {
        // Ajouter la correspondance ville-département dans le tableau si la région est Île-de-France
        if ($ville['nom_region'] === 'Île-de-France') {
            $villesDepartements[$ville['nom_commune_complet']] = $ville['nom_departement'] . ' (' . $ville['code_departement'] . ')';
        }
    }
} else {
    echo "Erreur de lecture du fichier JSON des villes.";
}

// Fonction pour obtenir le département à partir de la localisation
function getDepartementFromPosition($position) {
    global $villesDepartements;
    if (array_key_exists($position, $villesDepartements)) {
        return $villesDepartements[$position];
    }
    return "Inconnu";
}

?>
