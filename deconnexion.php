<?php
// Démarre la session
session_start();

// Détruit toutes les variables de session
$_SESSION = array();

// Supprime les cookies en fixant leur expiration à une date passée
setcookie('PHPSESSID', '', time() - 3600, '/');
setcookie('loggedin', '', time() - 3600, '/');
setcookie('role', '', time() - 3600, '/');

// Détruit la session
session_destroy();

// Redirige vers la page de connexion après la déconnexion
header("Location: index.php");
exit;
?>
