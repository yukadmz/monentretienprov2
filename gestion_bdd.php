<?php
// Vérifie si l'utilisateur est connecté, sinon redirige vers la page d'authentification
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Vérifie si l'utilisateur a le rôle d'administrateur
if ($_COOKIE['role'] !== 'administrateur') {
    header('Location: index.php'); // Remplacez "autre_page.php" par l'URL de la page vers laquelle vous souhaitez rediriger les utilisateurs non autorisés
    exit;
}

// Fonction pour récupérer les candidatures à partir du fichier JSON
function getCandidatures() {
    $candidatures_json = file_get_contents('assets/data/candidatures.json');
    // Conversion du JSON en tableau associatif
    return json_decode($candidatures_json, true);
}

// Fonction pour mettre à jour les candidatures dans le fichier JSON
function updateCandidatures($candidatures) {
    // Réécriture du contenu mis à jour dans le fichier JSON
    file_put_contents('assets/data/candidatures.json', json_encode($candidatures, JSON_PRETTY_PRINT));
}

// Code pour l'importation de candidatures
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["import"])) {
    // Vérifier si un fichier a été envoyé
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Vérifier si le fichier est un fichier JSON
        $file_info = pathinfo($_FILES['file']['name']);
        if ($file_info['extension'] === 'json') {
            // Lire le contenu du fichier JSON
            $json_data = file_get_contents($_FILES['file']['tmp_name']);

            // Décoder le contenu JSON en tableau associatif
            $candidatures = json_decode($json_data, true);

            // Vérifier si le décodage JSON a réussi
            if ($candidatures !== null) {
                // Mettre à jour vos données de candidatures avec les nouvelles données
                // ...

                // Exemple : remplacer les données existantes par les nouvelles données
                updateCandidatures($candidatures);

                echo "<div class='success-message'>Importation réussie.</div>";
            } else {
                echo "<div class='error-message'>Erreur lors du décodage du fichier JSON.</div>";
            }
        } else {
            echo "<div class='error-message'>Le fichier doit être au format JSON.</div>";
        }
    } else {
        echo "<div class='error-message'>Erreur lors de l'importation du fichier.</div>";
    }
}

// Code pour l'exportation de candidatures
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["export"])) {
    // Lecture du contenu du fichier JSON
    $candidatures = getCandidatures();

    // Définition des en-têtes pour le téléchargement
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="candidatures.json"');

    // Envoi du contenu du fichier JSON au client pour téléchargement
    echo json_encode($candidatures);
    exit(); // Assurez-vous de terminer l'exécution du script après l'exportation
}

// Code pour la suppression de la base de données
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm"])) {
    // Vérifier si le mot de confirmation est correct
    if ($_POST["confirm"] === "SUPPRIMER") {
        // Supprimer la base de données
        unlink('assets/data/candidatures.json');
        echo "<div class='success-message'>Base de données supprimée avec succès.</div>";
    } else {
        echo "<div class='error-message'>Mot de confirmation incorrect. La base de données n'a pas été supprimée.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration de la base de données</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="manifest" href="manifest.json">
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
<div class="container">
    <h1>Administration de la base de données</h1>
    <!-- Formulaire d'importation -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="file">Sélectionnez un fichier JSON à importer :</label>
        <input type="file" id="file" name="file" accept=".json" required>
        <button type="submit" name="import">Importer</button>
    </form>

    <!-- Formulaire d'exportation -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <button type="submit" name="export">Exporter</button>
    </form>

    <!-- Formulaire de suppression -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label for="confirm_delete">Entrez "SUPPRIMER" pour confirmer la suppression de la base de données :</label>
        <input type="text" id="confirm_delete" name="confirm" required>
        <button class="btn btn-red" type="submit"><i class="fas fa-exclamation-triangle"></i> Supprimer</button>
    </form>

</body>
</html>
