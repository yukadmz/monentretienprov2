<?php
session_start();

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page d'authentification
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Récupération des utilisateurs depuis le fichier JSON pour vérifier si l'utilisateur existe toujours
$utilisateurs = json_decode(file_get_contents('assets/data/utilisateurs.json'), true);

// Vérifie si l'utilisateur actuel existe dans le fichier utilisateurs
if (!isset($utilisateurs[$_SESSION['nom_utilisateur']])) {
    // Si l'utilisateur n'existe plus (par exemple, supprimé), détruire la session et rediriger
    session_destroy();
    setcookie('PHPSESSID', '', time() - 3600, '/'); // Supprimer le cookie de session
    header('Location: index.php');
    exit;
}

// Récupération du nom unique de cookie basé sur l'identifiant de l'utilisateur
$cookieName = 'role_' . $_SESSION['nom_utilisateur'];

// Vérification de l'existence du cookie et de la correspondance avec le rôle dans la session
if (!isset($_COOKIE[$cookieName]) || $_COOKIE[$cookieName] !== $utilisateurs[$_SESSION['nom_utilisateur']]['role']) {
    // Si le cookie n'existe pas ou que le rôle a changé, détruire la session et rediriger
    session_destroy();
    setcookie('PHPSESSID', '', time() - 3600, '/'); // Supprimer le cookie de session
    setcookie($cookieName, '', time() - 3600, '/'); // Supprimer le cookie de rôle
    header('Location: index.php');
    exit;
}

// Vérifie si l'utilisateur a le rôle d'administrateur ou de modification
if ($_COOKIE[$cookieName] !== 'administrateur' && $_COOKIE[$cookieName] !== 'modification') {
    header('Location: index.php'); // Redirection pour les utilisateurs non autorisés
    exit;
}

// Configuration pour la génération de la paire de clés RSA
$config = array(
    'private_key_bits' => 2048,  // Taille de la clé privée
    'private_key_type' => OPENSSL_KEYTYPE_RSA,  // Type de la clé privée
);

// Générer une paire de clés RSA
$res = openssl_pkey_new($config);

// Vérifie si la génération de la paire de clés a réussi
if ($res === false) {
    // Gestion de l'erreur
    echo 'Erreur lors de la génération des clés RSA.';
    exit;
}

// Extraire la clé privée
openssl_pkey_export($res, $privateKey);

// Extraire la clé publique
$publicKey = openssl_pkey_get_details($res);
$publicKey = $publicKey['key'];

// Enregistrer les clés dans des fichiers
file_put_contents('assets/keys/private.pem', $privateKey);
file_put_contents('assets/keys/public.pem', $publicKey);

echo 'La paire de clés RSA a été générée avec succès.';

// Redirection vers le tableau de bord après la génération des clés
header('refresh:2; url=dashboard.php');
exit; // Assurez-vous de terminer l'exécution du script après la redirection
?>
