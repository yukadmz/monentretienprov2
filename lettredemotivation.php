<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Vérification si l'utilisateur existe toujours dans le fichier JSON
$utilisateurs = json_decode(file_get_contents('assets/data/utilisateurs.json'), true);
if (!isset($utilisateurs[$_SESSION['nom_utilisateur']])) {
    // Si l'utilisateur n'existe plus, détruire la session et rediriger
    session_destroy();
    header('Location: index.php');
    exit;
}

// Récupération du nom unique de cookie basé sur l'identifiant de l'utilisateur
$cookieName = 'role_' . $_SESSION['nom_utilisateur'];

// Vérification de l'existence du cookie
if (!isset($_COOKIE[$cookieName])) {
    // Si le cookie n'existe pas, redirige vers la page d'authentification
    session_destroy();
    header('Location: index.php');
    exit;
}

// Vérification que l'utilisateur a bien le même rôle que dans le cookie
$role = $utilisateurs[$_SESSION['nom_utilisateur']]['role'];
if ($_COOKIE[$cookieName] !== $role) {
    // Si le rôle a changé, détruire la session et rediriger
    setcookie($cookieName, "", time() - 3600, "/"); // Effacer le cookie
    session_destroy();
    header('Location: index.php');
    exit;
}

// Chemin vers le dossier d'uploads
$uploadDir = 'uploads/';

// Vérifier si l'ID du fichier est présent dans l'URL
if (isset($_GET['id'])) {
    // Récupérer l'ID depuis l'URL
    $id = $_GET['id'];
    
    // Construire le chemin du fichier à partir de l'ID
    $filePath = $uploadDir . 'motivation_' . $id . '.pdf';
    
    // Vérifier si le fichier existe
    if (file_exists($filePath)) {
        // Autoriser l'accès au fichier
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit;
    } else {
        // Fichier introuvable, afficher une erreur
        echo "<p style='color:red; text-align:center; font-size:30px;'><strong>Fichier introuvable</strong></p>
                    <script>
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 2000); // 1000 milliseconds = 1 seconde
                  </script>";
        exit;
    }
} else {
    // ID non fourni dans l'URL, afficher une erreur
    echo "<p style='color:red; text-align:center; font-size:30px;'><strong>ID du fichier non spécifié</strong></p>
                    <script>
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 2000); // 1000 milliseconds = 1 seconde
                  </script>";
    exit;
}
?>
