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

?>
<?php
// Inclure la page option_contrat.php
include_once("assets/include/option_contrat.php");
?>
<?php
// Inclure le script des villes
include_once("assets/script/villes.php");
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une candidature - Mon Entretien Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script>
    function afficherFichier(cheminFichier) {
        // Ouvrir le fichier dans une nouvelle fenêtre ou un nouvel onglet
        window.open(cheminFichier);
    }
</script>
<script>
    function ajouterObservation() {
        // Récupération de la date et heure actuelle au format français
        const now = new Date();
        const dateString = now.toLocaleDateString('fr-FR');
        const timeString = now.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
        const dateTimeString = dateString + ' ' + timeString;

        // Récupération du textarea et de son contenu actuel
        const observationTextarea = document.getElementById('infostatut');
        let currentText = observationTextarea.value.trim();

        // Ajout du nouveau paragraphe avec la date et heure actuelle
        if (currentText === '') {
            currentText += 'Le ' + dateTimeString + ' :\n';
        } else {
            currentText += '\n\nLe ' + dateTimeString + ' :\n';
        }

        // Mise à jour du contenu du textarea
        observationTextarea.value = currentText;
    }
</script>

</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
<div class="container">
    <h1>Modifier une candidature</h1>
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
                $indexCandidature = $key;
                break;
            }
        }

        // Vérification de l'existence de la candidature
        if ($candidature === null) {
            echo "<p>Cette candidature n'existe pas.</p>";
        } else {
            // Vérification si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire
    $applydate = $_POST['applydate'];
    $position = $_POST['position'];
    $entreprise = $_POST['entreprise'];
    $poste = $_POST['poste'];
    $lien = $_POST['lien'];
    $contrat = $_POST['contrat'];
    $disponibilite = $_POST['disponibilite'];
    $contact = $_POST['contact'];
    $statut = $_POST['statut'];
    $applymethod = $_POST['applymethod'];
    $date_entretien = $_POST['date_entretien'];
    $deuxieme_entretien = isset($_POST['deuxieme_entretien']) ? true : false;
    $infostatut = $_POST['infostatut'];
    $salaire = $_POST['salaire'];

    // Vérification si le statut actuel est différent du nouveau statut
    if ($candidature['statut'] !== $statut) {
        // Mise à jour du statut de la candidature
        $candidatures[$indexCandidature]['statut'] = $statut;

        // Enregistrement de l'historique des changements de statut
        $candidatures[$indexCandidature]['historique_statut'][] = array(
            "date_changement" => date('Y-m-d H:i:s'),
            "statut" => $statut
        );
    }
    
    // Traitement du téléchargement de la lettre de motivation
if ($_FILES['lettre_motivation']['error'] === UPLOAD_ERR_OK) {
    $motivationTmpName = $_FILES['lettre_motivation']['tmp_name'];
    $motivationPath = 'uploads/motivation_' . $idCandidature . '.pdf'; // Nouveau nom de fichier avec le préfixe "motivation_" et l'identifiant unique de la candidature, avec une extension PDF

    // Déplacement du fichier téléchargé vers le dossier d'uploads avec le nouveau nom
    if (move_uploaded_file($motivationTmpName, $motivationPath)) {
        echo "Lettre de motivation téléchargée avec succès.";
        // Ajout du nom du fichier de lettre de motivation à la nouvelle candidature
        $candidature['lettre_motivation'] = $motivationPath;
    } else {
        echo "Erreur lors du téléchargement de la lettre de motivation.";
    }
} else {
    echo "Erreur lors du téléchargement de la lettre de motivation.";
}


    // Mise à jour des autres champs
    $candidatures[$indexCandidature]['applydate'] = $applydate;
    $candidatures[$indexCandidature]['position'] = $position;
    $candidatures[$indexCandidature]['entreprise'] = $entreprise;
    $candidatures[$indexCandidature]['poste'] = $poste;
    $candidatures[$indexCandidature]['lien'] = $lien;
    $candidatures[$indexCandidature]['contrat'] = $contrat;
    $candidatures[$indexCandidature]['disponibilite'] = $disponibilite;
    $candidatures[$indexCandidature]['contact'] = $contact;
    $candidatures[$indexCandidature]['applymethod'] = $applymethod;
    $candidatures[$indexCandidature]['date_entretien'] = $date_entretien;
    $candidatures[$indexCandidature]['deuxieme_entretien'] = $deuxieme_entretien;
    $candidatures[$indexCandidature]['infostatut'] = $infostatut;
    $candidatures[$indexCandidature]['salaire'] = $salaire;

    // Enregistrement des données mises à jour dans le fichier JSON
    file_put_contents('assets/data/candidatures.json', json_encode($candidatures, JSON_PRETTY_PRINT));

    echo "<p>Modifications enregistrées avec succès !</p>";
}



            // Affichage du formulaire de modification avec les données de la candidature
            ?>
            <form action="supprimer_candidature.php?id=<?php echo $idCandidature; ?>" method="post" class="delete-btn-container">
                <input type="submit" value="Supprimer" class="btn btn-red">
            </form>
            <?php
// Vérification si le statut n'est pas "Config", "En attente", "Refusée" ou "Acceptée"
if (!in_array($candidature['statut'], ['Config', 'En attente', 'Refusée', 'Acceptée'])) {
    // Calcul de la durée entre la date de candidature et la date actuelle
    $applyDate = new DateTime($candidature['applydate']);
    $currentDate = new DateTime();
    $diff = $currentDate->diff($applyDate);
    
    // Affichage de la durée
    echo "<p style='text-align: center;'>Temps écoulé depuis la candidature : " . $diff->format('%a jours') . "</p>";
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
    echo "<p style='text-align: center;'>Temps écoulé depuis le dernier entretien : " . $diff->format('%a jours') . "</p>";
}
?>
            <form class="edit-form" action="modif_candidature.php?id=<?php echo $idCandidature; ?>" method="post" enctype="multipart/form-data">
                <!-- Champs du formulaire -->
                <label for="applydate">Date de candidature :</label>
                <input type="date" name="applydate" id="applydate" value="<?php echo $candidature['applydate']; ?>"><br><br>
                
                <?php if (file_exists('uploads/motivation_' . $idCandidature . '.pdf')): ?>
                <label>Lettre de motivation :</label>
                <button type="button" onclick="afficherFichier('lettredemotivation.php?id=<?php echo $idCandidature; ?>')">Afficher la lettre de motivation</button><br><br>
                <?php else: ?>
                <label for="motivation">Lettre de motivation :</label>
                <input type="file" name="lettre_motivation"><br><br>
                <?php endif; ?>
                
                    
                    <label for="position">Localisation :</label>
                    <input type="text" name="position" id="position" value="<?php echo $candidature['position']; ?>">
                    <span>Département : <?php echo getDepartementFromPosition($candidature['position']); ?></span><br><br>
                    
                    <label for="entreprise">Entreprise :</label>
                    <input type="text" name="entreprise" id="entreprise" value="<?php echo $candidature['entreprise']; ?>"><br><br>

                    <label for="poste">Poste :</label>
                    <input type="text" name="poste" id="poste" value="<?php echo $candidature['poste']; ?>"><br><br>

                    <label for="lien">Lien du poste :</label>
                    <input type="text" name="lien" id="lien" value="<?php echo $candidature['lien']; ?>"><br><br>

                    <label for="contrat">Type de contrat :</label>
                    <select name="contrat" id="contrat">
                        <?php echo genererOptionsContrats($options_contrats, $candidature); ?>
                    </select><br><br>

                    <label for="disponibilite">Disponibilité :</label>
                    <input type="text" name="disponibilite" id="disponibilite" value="<?php echo $candidature['disponibilite']; ?>"><br><br>
                    
                    <label for="contact">Personne à contacter :</label>
                      <textarea name="contact" id="contact" rows="4"><?php echo $candidature['contact']; ?></textarea><br><br>

                    <label for="statut">Statut de la candidature :</label>
                    <select name="statut" id="statut">
                        <option value="En attente" <?php echo ($candidature['statut'] === 'En attente') ? 'selected' : ''; ?>>En attente</option>
                        <option value="En cours" <?php echo ($candidature['statut'] === 'En cours') ? 'selected' : ''; ?>>En cours</option>
                        <option value="Entretien" <?php echo ($candidature['statut'] === 'Entretien') ? 'selected' : ''; ?>>Entretien</option>
                      <option value="Entretien passé" <?php echo ($candidature['statut'] === 'Entretien passé') ? 'selected' : ''; ?>>Entretien passé</option>  
                        <option value="Acceptée" <?php echo ($candidature['statut'] === 'Acceptée') ? 'selected' : ''; ?>>Acceptée</option>
                        <option value="Refusée" <?php echo ($candidature['statut'] === 'Refusée') ? 'selected' : ''; ?>>Refusée</option>
                        <option value="Config" <?php echo ($candidature['statut'] === 'Config') ? 'selected' : ''; ?>>Config</option>
                    </select><br><br>
                    
                    <label for="applymethod">Méthode de candidature :</label>
                    <select id="applymethod" name="applymethod">
                        <option value="Candidature en ligne" <?php echo ($candidature['applymethod'] === 'Candidature en ligne') ? 'selected' : '';?>>Candidature en ligne</option>
                        <option value="Candidature papier" <?php echo ($candidature['applymethod'] === 'Candidature papier') ? 'selected' : '';?>>Candidature papier</option>
                        <option value="Envoyée par mail" <?php echo ($candidature['applymethod'] === 'Envoyée par mail') ? 'selected' : '';?>>Envoyée par mail</option>
                        <option value="Transfert de profil" <?php echo ($candidature['applymethod'] === 'Transfert de profil') ? 'selected' : '';?>>Transfert de profil</option>
                        <option value="Intéressée par le profil" <?php echo ($candidature['applymethod'] === 'Intéressée par le profil') ? 'selected' : '';?>>Intéressée par le profil</option>
                    </select><br><br>

                    <label for="date_entretien">Date d'entretien :</label>
                    <input type="datetime-local" name="date_entretien" id="date_entretien" value="<?php echo $candidature['date_entretien']; ?>"><br><br>

                    <label for="date_entretien">Deuxième entretien :</label>
                    <input type="checkbox" name="deuxieme_entretien" id="deuxieme_entretien" <?php echo $candidature['deuxieme_entretien'] ? 'checked' : ''; ?>><br><br>


                    <label for="infostatut">Observations :</label>
                    <button type="button" onclick="ajouterObservation()">Nouvelle observation</button>
                    <textarea name="infostatut" id="infostatut" rows="4"><?php echo $candidature['infostatut']; ?></textarea><br><br>
                    
                    <label for="salaire">Salaire :</label>
                    <input type="number" name="salaire" inputmode="numeric" id="salaire" value="<?php echo $candidature['salaire']; ?>"><br><br>
                    
                    <label for="historique_statut">Historique de modification du statut :</label>
                    <?php foreach ($candidature['historique_statut'] as $statut): ?>
                        <input type="text" disabled value="<?php echo date('d/m/Y H:i', strtotime($statut['date_changement'])) . ' : ' . $statut['statut']; ?>"><br><br>
                    <?php endforeach; ?>


                    <input type="submit" value="Enregistrer" class="btn">
            </form>

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
                <input type="hidden" name="deuxieme_entretien" value="<?php echo $candidature['deuxieme_entretien']; ?>">
                <input type="hidden" name="infostatut" value="<?php echo $candidature['infostatut']; ?>">
                <input type="hidden" name="salaire" id="salaire" value="<?php echo $candidature['salaire']; ?>">
                <!-- Ajout des données de l'historique -->
                <?php foreach ($candidature['historique_statut'] as $statut): ?>
                    <input type="hidden" name="historique_statut[]" value="<?php echo $statut['date'] . ':' . $statut['statut']; ?>">
                <?php endforeach; ?>
                <input type="submit" value="Exporter en PDF" class="btn">
            </form>
        <?php
        }
    }
    ?>
</div>
<?php include 'assets/include/footer.php'; ?>
</body>

</html>
