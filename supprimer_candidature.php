<?php
// Vérifie si l'utilisateur est connecté, sinon redirige vers la page d'authentification
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Vérifie si l'utilisateur a le rôle d'administrateur ou de modification
if ($_COOKIE['role'] !== 'administrateur' && $_COOKIE['role'] !== 'modification') {
    header('Location: index.php'); // Remplacez "autre_page.php" par l'URL de la page vers laquelle vous souhaitez rediriger les utilisateurs non autorisés
    exit;
}
// Reste du code de votre page...
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer une candidature</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="manifest" href="manifest.json">
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
    <div class="container">
        <h1>Supprimer une candidature</h1>
<?php
// Récupération de l'identifiant unique de la candidature depuis l'URL
$idCandidature = $_GET['id'] ?? null;

// Vérification de la présence de l'identifiant
if ($idCandidature === null) {
    echo "<p>Aucune candidature sélectionnée.</p>";
} else {
    // Lecture du contenu du fichier JSON
    $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);

    // Recherche de la candidature correspondant à l'identifiant
    $candidature = null;
    foreach ($candidatures as $key => $cand) {
        if ($cand['id'] === $idCandidature) {
            $candidature = $cand;
            break;
        }
    }

    // Vérification de l'existence de la candidature et affichage de la confirmation de suppression
    if ($candidature === null) {
        echo "<p>Cette candidature n'existe pas.</p>";
    } else {
        echo "<p style='text-align: center;'>Êtes-vous sûr de vouloir supprimer la candidature : {$candidature['entreprise']} - {$candidature['poste']} ?</p>";
        echo '<form action="" method="post">';
        echo '<input type="submit" name="confirm" value="Oui" class="btn btn-red">';
        echo '<a href="dashboard.php" class="btn btn-green">Non</a>';
        echo '</form>';

        // Si la confirmation est donnée, supprimer la candidature
        if (isset($_POST['confirm'])) {
            unset($candidatures[$key]);
            file_put_contents('assets/data/candidatures.json', json_encode($candidatures, JSON_PRETTY_PRINT));
            echo "<p>La candidature : {$candidature['entreprise']} - {$candidature['poste']} a été supprimée avec succès.</p>";
            // Redirection vers le tableau de bord après la suppression
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "dashboard.php";
                    }, 2000); // 1000 milliseconds = 1 seconde
                  </script>';
            exit(); // Assurez-vous de terminer l'exécution du script après la redirection
        }
    }
}
?>

    </div>
</body>
</html>
