<?php
// Vérifie si l'utilisateur est connecté, sinon redirige vers la page d'authentification
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Récupération du nom unique de cookie basé sur l'identifiant de l'utilisateur
$cookieName = 'role_' . $_SESSION['nom_utilisateur'];

// Vérification de l'existence du cookie
if (!isset($_COOKIE[$cookieName])) {
    // Si le cookie n'existe pas, redirige vers la page d'authentification
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda des entretiens - Mon Entretien Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <!-- Inclure Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-fJ+qBBsBWT52R4cpkz0w9znsMm4OkuSlVqNNP8HjjFwYwGh/Ppuyt8ASc7s2FYskS4DOoVtmFYxZt7c2OvhMfA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .card {
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
        }
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .calendar-icon {
            margin-right: 10px;
            color: #5e72e4;
            font-size: 24px;
        }
        .card-content {
            margin-bottom: 5px;
        }
        .btn-done {
            background-color: green;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
<div class="container">
    <h1>Agenda des entretiens</h1>

<?php
// Récupération des données du fichier JSON
$candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);

// Trier les candidatures par date d'entretien
usort($candidatures, function ($a, $b) {
    return strtotime($a['date_entretien']) - strtotime($b['date_entretien']);
});

// Variable pour vérifier s'il y a des entretiens programmés
$entretiens_programmes = false;

// Parcourir les candidatures triées et afficher les entretiens futurs ou égaux à aujourd'hui ayant le statut "Entretien"
foreach ($candidatures as $key => $candidature) {
    $dateEntretien = strtotime($candidature['date_entretien']);
    if ($dateEntretien >= strtotime('today') && $candidature['statut'] == 'Entretien') {
        // Indiquer qu'il y a des entretiens programmés
        $entretiens_programmes = true;
        echo "<div class='card'>";
        echo "<div class='card-header'>";
        echo "<div class='calendar-icon'>";
        echo "<i class='far fa-calendar-alt'></i>";
        echo "</div>";
        setlocale(LC_TIME, 'fr_FR.utf8', 'fra'); // Définit la locale pour l'affichage en français
        $dateFormatted = strftime('%a %e %B %Y %H:%M', $dateEntretien); // Format de date en français avec jour, heure et mois
        // Vérifier si la case "Deuxième entretien" est cochée pour cette candidature
        if (!empty($candidature['deuxieme_entretien'])) {
            $dateFormatted .= " - 2nd entretien"; // Ajouter "2nd entretien" à la date si la case est cochée
        }
        echo "<div>$dateFormatted</div>";
        echo "</div>";

        // Afficher les détails de la candidature (entreprise, poste, type de contrat)
        echo "<div class='card-content'><strong>Entreprise :</strong> " . $candidature['entreprise'] . "</div>";
        echo "<div class='card-content'><strong>Poste :</strong> " . $candidature['poste'] . "</div>";
        echo "<div class='card-content'><strong>Type de contrat :</strong> " . $candidature['contrat'] . "</div>";

        // Ajout d'un formulaire pour marquer l'entretien comme terminé
        if ($_COOKIE[$cookieName] === 'administrateur' || $_COOKIE[$cookieName] === 'modification') {
            echo "<div class='card-footer'>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='candidature_id' value='$key'>";
            echo "<button type='submit' class='btn btn-done' name='mark_as_done'>Marquer comme terminé</button>";
            echo "</form>";
            echo "</div>";
        }
        echo "</div>";
    }
}

// Si aucun entretien n'est programmé, afficher un message approprié
if (!$entretiens_programmes) {
    echo "<p>Aucun entretien programmé pour le moment.</p>";
}

// Si des entretiens sont programmés, afficher le lien d'abonnement
if ($entretiens_programmes) {
    echo "<div class='card'>";
    echo "<div class='card-content'>";
    // Lien vers le fichier calendrier.php
    echo "<a href='synchro_calendrier.php' class='btn' target='_blank'><i class='fas fa-calendar-plus'></i> S'abonner au calendrier</a>";
    echo "</div>";
    echo "</div>";
}

// Traitement du formulaire pour marquer l'entretien comme terminé
if (isset($_POST['mark_as_done']) && isset($_POST['candidature_id'])) {
    $candidature_id = $_POST['candidature_id'];
    // Mettre à jour l'état de la candidature comme "Entretien passé"
    $candidatures[$candidature_id]['statut'] = 'Entretien passé';
    // Enregistrer les données mises à jour dans le fichier JSON
    file_put_contents('assets/data/candidatures.json', json_encode($candidatures, JSON_PRETTY_PRINT));
    // Actualiser la page pour afficher les changements
    header('Location: agenda.php');
    exit;
}
?>
</div>

<?php include 'assets/include/footer.php'; ?>
</body>
</html>
