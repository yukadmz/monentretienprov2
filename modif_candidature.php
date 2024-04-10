<?php
// Vérifie si l'utilisateur est connecté, sinon redirige vers la page d'authentification
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une candidature</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
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
        foreach ($candidatures as $cand) {
            if ($cand['id'] === $idCandidature) {
                $candidature = $cand;
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
                $applydate = $_POST["applydate"] ?? '';
                $entreprise = $_POST['entreprise'] ?? '';
                $position = $_POST['position'] ?? '';
                $poste = $_POST['poste'] ?? '';
                $lien = $_POST['lien'] ?? '';
                $contrat = $_POST['contrat'] ?? '';
                $disponibilite = $_POST['disponibilite'] ?? '';
                $contact = $_POST['contact'] ?? '';
                $statut = $_POST['statut'] ?? '';
                $applymethod = $_POST['applymethod'] ?? '';
                $dateEntretien = $_POST['date_entretien'] ?? '';
                $deuxiemeEntretien = isset($_POST['deuxieme_entretien']) ? 1 : 0; // Valeur de la case à cocher
                $infostatut = $_POST['infostatut'] ?? '';
                $salaire = $_POST['salaire'] ?? '';

                // Mise à jour des données de la candidature
                foreach ($candidatures as &$cand) {
                    if ($cand['id'] === $idCandidature) {
                        $cand['applydate'] = $applydate;
                        $cand['entreprise'] = $entreprise;
                        $cand['position'] = $position;
                        $cand['poste'] = $poste;
                        $cand['lien'] = $lien;
                        $cand['contrat'] = $contrat;
                        $cand['disponibilite'] = $disponibilite;
                        $cand['contact'] = $contact;
                        $cand['statut'] = $statut;
                        $cand['applymethod'] = $applymethod;
                        $cand['date_entretien'] = $dateEntretien;
                        $cand['deuxieme_entretien'] = $deuxiemeEntretien;
                        $cand['infostatut'] = $infostatut;
                        $cand['salaire'] = $salaire;
                        break;
                    }
                }

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

                <form class="edit-form" action="modif_candidature.php?id=<?php echo $idCandidature; ?>" method="post">
                    <!-- Champs du formulaire -->
                    <label for="applydate">Date de candidature :</label>
                    <input type="date" name="applydate" id="applydate" value="<?php echo $candidature['applydate']; ?>"><br><br>
                    
                    <label for="position">Localisation :</label>
                    <input type="text" name="position" id="position" value="<?php echo $candidature['position']; ?>"><br><br>
                    
                    <label for="entreprise">Entreprise :</label>
                    <input type="text" name="entreprise" id="entreprise" value="<?php echo $candidature['entreprise']; ?>"><br><br>

                    <label for="poste">Poste :</label>
                    <input type="text" name="poste" id="poste" value="<?php echo $candidature['poste']; ?>"><br><br>

                    <label for="lien">Lien du poste :</label>
                    <input type="text" name="lien" id="lien" value="<?php echo $candidature['lien']; ?>"><br><br>

                    <label for="contrat">Type de contrat :</label>
                    <select name="contrat" id="contrat">
                        <option value="CDI" <?php echo ($candidature['contrat'] === 'CDI') ? 'selected' : ''; ?>>CDI</option>
                        <option value="CDD" <?php echo ($candidature['contrat'] === 'CDD') ? 'selected' : ''; ?>>CDD</option>
                        <option value="Intérim" <?php echo ($candidature['contrat'] === 'Intérim') ? 'selected' : ''; ?>>Intérim</option>
                        <option value="Stage" <?php echo ($candidature['contrat'] === 'Stage') ? 'selected' : ''; ?>>Stage</option>
                        <option value="Alternance" <?php echo ($candidature['contrat'] === 'Alternance') ? 'selected' : ''; ?>>Alternance</option>
                        <option value="CTT" <?php echo ($candidature['contrat'] === 'CTT') ? 'selected' : ''; ?>>CTT</option>
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
                    <textarea name="infostatut" id="infostatut" rows="4"><?php echo $candidature['infostatut']; ?></textarea><br><br>
                    
                    <label for="salaire">Salaire :</label>
                    <input type="number" name="salaire" inputmode="numeric" id="salaire" value="<?php echo $candidature['salaire']; ?>"><br><br>

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
            <input type="submit" value="Exporter en PDF" class="btn">
        </form>
                <?php
            }
        }
        ?>
    </div>
</body>
</html>
