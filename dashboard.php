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

// Script pour vérifier la date de candidature
$candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);
function estCandidatureAncienne($candidature) {
    $dateCandidature = strtotime($candidature['applydate']);
    $dateLimite = strtotime('-4 week'); // Candidature déposée il y a 4 semaines.
    return $dateCandidature < $dateLimite;
}

// Script pour vérifier la date d'entretien
function estCandidaturePasseeAncienne($candidature) {
    // Date limite : il y a 5 jours
    $dateLimite = strtotime('-5 jours');

    // Convertir la date de la candidature en timestamp
    $dateCandidature = strtotime($candidature['date_entretien']);

    // Comparer la date de la candidature avec la date limite
    return $dateCandidature < $dateLimite;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Suivi des Candidatures</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('service-worker.js').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
<div class="container">
    <h1>Tableau de bord</h1>
    <?php $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true); ?>
    <?php if ($_COOKIE[$cookieName] === 'administrateur' || $_COOKIE[$cookieName] === 'modification' || $_COOKIE[$cookieName] === 'lecture') : ?>
        <?php
        // Vérification s'il y a un enregistrement de date limite de recherche
        $candidaturesEnCours = array_filter($candidatures, function($candidature) {
            return $candidature['statut'] === 'Config';
        });

        if (empty($candidaturesEnCours)) {
            echo "<p style='text-align:center;'>Aucune date limite de recherche d'emploi n'a été déterminée.</p>";
        } else {
            echo "<p style='text-align:center; color:red; text-decoration:bold; font-size:25px;';><strong>";
            foreach ($candidaturesEnCours as $candidature) {
                echo "<a href='modif_candidature.php?id={$candidature['id']}'>{$candidature['entreprise']} {$candidature['poste']}</a>";
                echo "</strong></p>";
            }
        }
        ?>
    <?php endif; ?>
    <div class="dashboard">
        <div class="card">
            <h2><i class="fas fa-hourglass-start"></i> Candidatures en attente</h2>
            <?php // Lecture du contenu du fichier JSON
            $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);
            ?>
            <?php
            // Vérification s'il y a des candidatures en attente
            $candidaturesEnAttente = array_filter($candidatures, function($candidature) {
                return $candidature['statut'] === 'En attente';
            });

            if (!empty($candidaturesEnAttente)) :
                foreach ($candidaturesEnAttente as $candidature) :
                    // Vérifier si la candidature
                    // est ancienne
                    $estAncienne = estCandidatureAncienne($candidature);
                    ?>
                    <div class="card-dashboard">
                        <a href='<?php echo ($_COOKIE[$cookieName] === 'administrateur' || $_COOKIE[$cookieName] === 'modification') ? "modif_candidature.php?id={$candidature['id']}" : "lecture_candidature.php?id={$candidature['id']}"; ?>'>
                            <h3><?php
                                // Afficher l'icône d'horloge en fonction de la date de candidature if ($estAncienne) { echo '<i class="fas fa-clock clock"></i>'; // Calcul de la durée entre la date de candidature et la date actuelle $applyDate = new DateTime($candidature['applydate']); $currentDate = new DateTime(); $diff = $currentDate->diff($applyDate); // Affichage de la durée echo $diff->format('  %a jours | <br>'); } echo $candidature['entreprise'] . ' - ' . $candidature['poste']; ?></h3> </a> </div> <?php
endforeach;
else :
echo "<p>Aucune candidature en attente.</p>";
endif;
?> </div>    

<!-- Autres sections -->

<div class="card">
    <h2><i class="fas fa-hourglass-half"></i> Candidatures en cours</h2>
    <?php // Lecture du contenu du fichier JSON
    $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);
    ?>
    <?php
    // Vérification s'il y a des candidatures en cours
    $candidaturesEnCours = array_filter($candidatures, function($candidature) {
        return $candidature['statut'] === 'En cours';
    });

    if (!empty($candidaturesEnCours)) :
        foreach ($candidaturesEnCours as $candidature) :
            // Vérifier si la candidature est ancienne
            $estAncienne = estCandidatureAncienne($candidature);
            ?>
            <div class="card-dashboard">
                <a href='<?php echo ($_COOKIE[$cookieName] === 'administrateur' || $_COOKIE[$cookieName] === 'modification') ? "modif_candidature.php?id={$candidature['id']}" : "lecture_candidature.php?id={$candidature['id']}"; ?>'>
                    <h3><?php
                        // Afficher l'icône d'horloge en fonction de la date de candidature
                        if ($estAncienne) {
                            echo '<i class="fas fa-clock clock"></i>';
                            // Calcul de la durée entre la date de candidature et la date actuelle
                            $applyDate = new DateTime($candidature['applydate']);
                            $currentDate = new DateTime();
                            $diff = $currentDate->diff($applyDate);
                            // Affichage de la durée
                            echo $diff->format('  %a jours | <br>');
                        }
                        echo $candidature['entreprise'] . ' - ' . $candidature['poste'];
                        ?></h3>
                </a>
            </div>
        <?php
        endforeach;
    else :
        echo "<p>Aucune candidature en cours.</p>";
    endif;
    ?>
</div>

<!-- Autres sections -->

<div class="card">
    <h2><i class="fas fa-calendar"></i> Entretiens en attente</h2>
    <?php // Lecture du contenu du fichier JSON
    $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);
    ?>
    <?php
    // Vérification s'il y a des entretiens en attente
    $entretiensEnAttente = array_filter($candidatures, function($candidature) {
        return $candidature['statut'] === 'Entretien';
    });

    if (!empty($entretiensEnAttente)) :
        foreach ($entretiensEnAttente as $entretien) :
            // Vérifier si la date de l'entretien est ancienne
            $estAncienne = estCandidatureAncienne($entretien);
            ?>
            <div class="card-dashboard">
                <a href='<?php echo ($_COOKIE[$cookieName] === 'administrateur' || $_COOKIE[$cookieName] === 'modification') ? "modif_candidature.php?id={$entretien['id']}" : "lecture_candidature.php?id={$entretien['id']}"; ?>'>
                    <h3><?php echo $entretien['entreprise'] . ' - ' . $entretien['poste'] . ' - ' . date("d/m/Y", strtotime($entretien['date_entretien'])); ?></h3>
                </a>
            </div>
        <?php
        endforeach;
    else :
        echo "<p>Aucun entretien programmé.</p>";
    endif;
    ?>
</div>

<!-- Autres sections -->

<div class="card">
    <h2><i class="fas fa-calendar-check"></i> Entretiens passés</h2>
    <?php // Lecture du contenu du fichier JSON
    $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);
    ?>
    <?php
    // Vérification s'il y a des entretiens passés
    $entretiensPasses = array_filter($candidatures, function($candidature) {
        return $candidature['statut'] === 'Entretien passé';
    });

    if (!empty($entretiensPasses)) :
        foreach ($entretiensPasses as $entretien) :
            // Vérifier si la candidature est ancienne
            $estAncienne = estCandidatureAncienne($entretien);
            ?>
            <div class="card-dashboard">
                <a href='<?php echo ($_COOKIE[$cookieName] === 'administrateur' || $_COOKIE[$cookieName] === 'modification') ? "modif_candidature.php?id={$entretien['id']}" : "lecture_candidature.php?id={$entretien['id']}"; ?>'>
                    <h3><?php
                        // Afficher l'icône d'horloge en fonction de la date d'entretien
                        if (estCandidaturePasseeAncienne($entretien)) {
                            echo '<i class="fas fa-clock clock"></i>';
                            // Calcul de la durée entre ladate d'entretien et la date actuelle 
                            $interviewDate = new DateTime($entretien['date_entretien']); $currentDate = new DateTime(); $diff = $currentDate->diff($interviewDate); 
                            // Affichage de la durée 
                            echo $diff->format('  %a jours | <br>'); } echo $entretien['entreprise'] . ' - ' . $entretien['poste'] . ' - ' . date("d/m/Y", strtotime($entretien['date_entretien'])); ?></h3> </a> </div> <?php endforeach; else :
echo "<p>Aucun entretien passé.</p>";
endif;
?></div>
<!-- Autres sections -->
<div class="card">
    <h2><i class="fas fa-check-circle"></i> Candidatures acceptées</h2>
    <?php // Lecture du contenu du fichier JSON
    $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);
    ?>
    <?php
    // Vérification s'il y a des candidatures acceptées
    $candidaturesAcceptees = array_filter($candidatures, function($candidature) {
        return $candidature['statut'] === 'Acceptée';
    });
if (!empty($candidaturesAcceptees)) :
    foreach ($candidaturesAcceptees as $candidature) :
        ?>
        <div class="card-dashboard">
            <a href='<?php echo ($_COOKIE[$cookieName] === 'administrateur' || $_COOKIE[$cookieName] === 'modification') ? "modif_candidature.php?id={$candidature['id']}" : "lecture_candidature.php?id={$candidature['id']}"; ?>'>
                <h3><?php echo $candidature['entreprise'] . ' - ' . $candidature['poste']; ?></h3>
            </a>
        </div>
    <?php
    endforeach;
else :
    echo "<p>Aucune proposition d'embauche.</p>";
endif;
?>
</div>
<!-- Autres sections -->
<div class="card">
    <h2><i class="fas fa-times-circle"></i> Candidatures refusées</h2>
    <?php // Lecture du contenu du fichier JSON
    $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);
    ?>
    <?php
    // Vérification s'il y a des candidatures refusées
    $candidaturesRefusees = array_filter($candidatures, function($candidature) {
        return $candidature['statut'] === 'Refusée';
    });
if (!empty($candidaturesRefusees)) :
    foreach ($candidaturesRefusees as $candidature) :
        ?>
        <div class="card-dashboard">
            <a href='<?php echo ($_COOKIE[$cookieName] === 'administrateur' || $_COOKIE[$cookieName] === 'modification') ? "modif_candidature.php?id={$candidature['id']}" : "lecture_candidature.php?id={$candidature['id']}"; ?>'>
                <h3><?php echo $candidature['entreprise'] . ' - ' . $candidature['poste']; ?></h3>
            </a>
        </div>
    <?php
    endforeach;
else :
    echo "<p>Aucune candidature refusée.</p>";
endif;
?>
</div>
<!-- Autres sections -->


</div>
<!-- Bouton pour exporter vers un fichier PDF -->
<form action="export_pdf_all.php" method="post">
    <input type="submit" value="Tout exporter en PDF" class="btn">
</form>
</div>
</body>
</html>
