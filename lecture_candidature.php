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



            // Vérification si un fichier a été téléchargé
            if(isset($_FILES['motivation']) && $_FILES['motivation']['error'] === UPLOAD_ERR_OK) {
                // Déplacer le fichier téléchargé vers le répertoire de destination
                $motivationPath = 'uploads/motivation_' . $idCandidature . '.pdf';
                move_uploaded_file($_FILES['motivation']['tmp_name'], $motivationPath);
                
                // Afficher le bouton "Afficher" avec le chemin du fichier comme paramètre
                echo '<button onclick="afficherFichier(\'' . $motivationPath . '\')">Afficher</button>';
            }
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la candidature - Mon Entretien Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script>
    function afficherFichier(cheminFichier) {
        // Ouvrir le fichier dans une nouvelle fenêtre ou un nouvel onglet
        window.open(cheminFichier);
    }
</script>

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
<?php
// Lecture du fichier JSON des correspondances ville-département
$villesDepartements = [];
$jsonFile = 'assets/data/villes.json'; // Chemin vers le fichier JSON
$jsonData = file_get_contents($jsonFile);
if ($jsonData !== false) {
    $villesData = json_decode($jsonData, true);
    foreach ($villesData as $ville) {
        // Ajouter la correspondance ville-département dans le tableau
        $villesDepartements[$ville['nom_commune_complet']] = $ville['nom_departement'] . ' (' . $ville['code_departement'] . ')';
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
        <ul>
            <li><strong>Date de candidature :</strong> <?php echo date('d/m/Y H:i', strtotime($candidature['applydate'])); ?></li>
            <?php if (file_exists('uploads/motivation_' . $idCandidature . '.pdf')): ?>
                <li><strong>Lettre de motivation : </strong><button type="button" onclick="afficherFichier('lettredemotivation.php?id=<?php echo $idCandidature; ?>')">Afficher la lettre de motivation</button><br><br>
                <?php else: ?>
                <?php endif; ?>
            <li><strong>Localisation :</strong> <?php echo $candidature['position']; ?></li>
            <li><span><strong>Département :</strong> <?php echo getDepartementFromPosition($candidature['position']); ?></span></li>
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
            <li><strong>Observations :</strong></li><pre style="max-width: calc(100vw - 20px); overflow-y: auto; white-space: pre-wrap; text-align: justify; word-wrap: break-word;"><?php echo $candidature['infostatut']; ?></pre>
            <li><strong>Salaire :</strong> <?php echo $candidature['salaire']; ?></li>
            <li><strong>Historique de modification du statut :</strong></li>
            <ul>
                <?php foreach ($candidature['historique_statut'] as $statut): ?>
                    <li>
                        <?php echo date('d/m/Y H:i', strtotime($statut['date_changement'])) . ' : ' . $statut['statut']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
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
                <!-- Ajout des données de l'historique -->
                <?php foreach ($candidature['historique_statut'] as $statut): ?>
                    <input type="hidden" name="historique_statut[]" value="<?php echo $statut['date'] . ':' . $statut['statut']; ?>">
                <?php endforeach; ?>
            <input type="submit" value="Exporter en PDF" class="btn">
        </form>
                
    </div>
<?php include 'assets/include/footer.php'; ?>
</body>
</html>