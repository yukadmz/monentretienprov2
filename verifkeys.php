<?php
// Chemin vers la clé privée
$privateKeyFilePath = 'assets/keys/private.pem';

// Chemin vers la clé publique
$publicKeyFilePath = 'assets/keys/public.pem';

// Mot de passe de la clé privée
$privateKeyPassword = 'Axel18152156';

// Charger le contenu des clés privée et publique
$privateKey = file_get_contents($privateKeyFilePath);
$publicKey = file_get_contents($publicKeyFilePath);

// Vérifier si le chargement des clés s'est bien passé
if ($privateKey === false) {
    die("Erreur lors du chargement de la clé privée");
}

if ($publicKey === false) {
    die("Erreur lors du chargement de la clé publique");
}

echo "Clé privée chargée avec succès<br>";
echo "Clé publique chargée avec succès<br>";

// Charger les informations sur la clé privée
$privateKeyResource = openssl_pkey_get_private($privateKey, $privateKeyPassword);
if ($privateKeyResource === false) {
    die("Erreur lors de la récupération des détails de la clé privée: " . openssl_error_string());
}

// Charger les informations sur la clé publique
$publicKeyResource = openssl_pkey_get_public($publicKey);
if ($publicKeyResource === false) {
    die("Erreur lors de la récupération des détails de la clé publique: " . openssl_error_string());
}

echo "Détails de la clé privée récupérés avec succès<br>";
echo "Détails de la clé publique récupérés avec succès<br>";

// Vérifier si les modules (n) des clés privée et publique sont identiques
$privateKeyDetails = openssl_pkey_get_details($privateKeyResource);
$publicKeyDetails = openssl_pkey_get_details($publicKeyResource);

if ($privateKeyDetails['rsa']['n'] === $publicKeyDetails['rsa']['n']) {
    echo "La clé publique correspond à la clé privée.";
} else {
    echo "La clé publique ne correspond pas à la clé privée.";
}
?>
