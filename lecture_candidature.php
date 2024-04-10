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

// Lecture du contenu du fichier JSON
$candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);

// Récupération de l'identifiant unique de la candidature depuis l'URL
$idCandidature = $_GET['id'] ?? null;

// Vérification de la présence de l'identifiant
if ($idCandidature === null) {
    header('Location: index.php'); // Redirige vers une autre page si aucun identifiant n'est fourni
    exit;
}

// Recherche de la candidature correspondant à l'identifiant
$candidature = null;
foreach ($candidatures as $cand) {
    if ($cand['id'] === $idCandidature) {
        $candidature = $cand;
        break;
    }
}

// Vérification de l'existence de la candidature
if ($candidature === null) {
    header('Location: index.php'); // Redirige vers une autre page si la candidature n'existe pas
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la candidature</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
    <div class="container">
        <h1>Détails de la candidature</h1>
        
            <?php
// Vérification si le statut n'est pas "Config", "En attente", "Refusée" ou "Acceptée"
if (!in_array($candidature['statut'], ['Config', 'En attente', 'Refusée', 'Acceptée'])) {
    // Calcul de la durée entre la date de candidature et la date actuelle
    $applyDate = new DateTime($candidature['applydate']);
    $currentDate = new DateTime();
    $diff = $currentDate->diff($applyDate);
    
    // Affichage de la durée
    echo "<p style='text-align: left;'>Temps écoulé depuis la candidature : " . $diff->format('%a jours') . "</p>";
}
?>

            <?php
// Vérification si une date d'entretien est notée et que le statut est "Entretien passé"
if (!empty($candidature['date_entretien']) && $candidature['statut'] === 'Entretien passé') {
    // Calcul de la durée entre la date de l'entretien et la date actuelle
    $entretienDate = new DateTime($candidature['date_entretien']);
    $currentDate = new DateTime();
    $diff = $currentDate->diff($entretienDate);
    
    // Affichage de la durée
    echo "<p style='text-align: left;'>Temps écoulé depuis le dernier entretien : " . $diff->format('%a jours') . "</p>";
}
?>
        <ul>
            <li><strong>Date de candidature :</strong> <?php echo date('d/m/Y H:i', strtotime($candidature['applydate'])); ?></li>
            <li><strong>Localisation :</strong> <?php echo $candidature['position']; ?></li>
            <li><strong>Entreprise :</strong> <?php echo $candidature['entreprise']; ?></li>
            <li><strong>Poste :</strong> <?php echo $candidature['poste']; ?></li>
            <li><strong>Lien du poste :</strong> <?php echo $candidature['lien']; ?></li>
            <li><strong>Type de contrat :</strong> <?php echo $candidature['contrat']; ?></li>
            <li><strong>Disponibilité :</strong> <?php echo $candidature['disponibilite']; ?></li>
            <li><strong>Personne à contacter :</strong></li><pre style="text-align: justify;"><?php echo $candidature['contact']; ?></pre>
            <li><strong>Statut de la candidature :</strong> <?php echo $candidature['statut']; ?></li>
            <li><strong>Méthode de candidature :</strong> <?php echo $candidature['applymethod']; ?></li>
            <li><strong>Date d'entretien :</strong> <?php echo date('d/m/Y H:i', strtotime($candidature['date_entretien'])); ?></li>
            <li><strong>Deuxième entretien :</strong> <?php echo $candidature['deuxieme_entretien'] ? 'Oui' : 'Non'; ?></li>
            <li><strong>Observations :</strong></li><pre style="text-align: justify;"><?php echo $candidature['infostatut']; ?></pre>
            <li><strong>Salaire :</strong> <?php echo $candidature['salaire']; ?></li>
        </ul>
        <!-- Bouton pour exporter vers un fichier PDF -->
        <form action="export_pdf.php?id=<?php echo $idCandidature; ?>" method="post">
            <input type="hidden" name="applydate" value="<?php echo $candidature['applydate']; ?>">
            <input type="hidden" name="entreprise" value="<?php echo $candidature['entreprise']; ?>">
            <input type="hidden" name="position" value="<?php echo $candidature['position']; ?>">
            <input type="hidden" name="poste" value="<?php echo $candidature['poste']; ?>">
            <input type="hidden" name="lien" value="<?php echo $candidature['lien']; ?>">
            <input type="hidden" name="contrat" value="<?php echo $candidature['contrat']; ?>">
            <input type="hidden" name="disponibilite" value="<?php echo $candidature['disponibilite']; ?>">
            <input type="hidden" name="contact" value="<?php echo $candidature['contact']; ?>">
            <input type="hidden" name="statut" value="<?php echo $candidature['statut']; ?>">
            <input type="hidden" name="applymethod" value="<?php echo $candidature['applymethod']; ?>">
            <input type="hidden" name="date_entretien" value="<?php echo $candidature['date_entretien']; ?>">
            <input type="hidden" name="infostatut" value="<?php echo $candidature['infostatut']; ?>">
            <input type="hidden" name="salaire" id="salaire" value="<?php echo $candidature['salaire']; ?>">
            <input type="submit" value="Exporter en PDF" class="btn">
        </form>
                
    </div>
</body>
</html>