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

?>
<?php
// Inclure la page option_contrat.php
include_once("assets/include/option_contrat.php");
?>
<?php
// Lecture du fichier JSON des candidatures
$candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);

// Filtrer les candidatures pour exclure celles avec le statut "Config"
$candidatures = array_filter($candidatures, function($candidature) {
    return $candidature['statut'] !== 'Config';
});

// Lecture du fichier JSON des villes
$villes = json_decode(file_get_contents('assets/data/villes.json'), true);

// Fonction pour obtenir le département à partir de la localisation
function getDepartementFromPosition($position) {
    global $villes;
    foreach ($villes as $ville) {
        if ($ville['nom_commune_complet'] === $position && $ville['nom_region'] === 'Île-de-France') {
            return $ville['nom_departement'] . ' (' . $ville['code_departement'] . ')';
        }
    }
    return "Inconnu";
}

// Fonction pour obtenir le nombre de candidatures par département
function getCandidaturesByDepartement() {
    global $candidatures;
    $candidaturesByDepartement = array();
    foreach ($candidatures as $candidature) {
        $position = $candidature['position'];
        $departement = getDepartementFromPosition($position);
        if (array_key_exists($departement, $candidaturesByDepartement)) {
            $candidaturesByDepartement[$departement]++;
        } else {
            $candidaturesByDepartement[$departement] = 1;
        }
    }
    return $candidaturesByDepartement;
}

// Obtenir le nombre de candidatures par département
$candidaturesByDepartement = getCandidaturesByDepartement();
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques des candidatures - Mon Entretien Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
</head>

<body>
    <?php include 'assets/include/navbar.php'; ?>
    <div class="container">
        <h1>Statistiques des candidatures par département</h1>
        <table>
            <thead>
                <tr>
                    <th>Département</th>
                    <th>Nombre de candidatures</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidaturesByDepartement as $departement => $nombreCandidatures) : ?>
                    <tr>
                        <td><?php echo $departement; ?></td>
                        <td><?php echo $nombreCandidatures; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="container">
        <h1>Statistiques des candidatures par type de contrat</h1>
        <table>
            <thead>
                <tr>
                    <th>Type de contrat</th>
                    <th>Nombre de candidatures</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $contratsCount = array_count_values(array_column($candidatures, 'contrat'));
                foreach ($contratsCount as $contrat => $count) : ?>
                    <tr>
                        <td><?php echo $contrat; ?></td>
                        <td><?php echo $count; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="container">
        <h1>Statistiques des candidatures par méthode de candidature</h1>
        <table>
            <thead>
                <tr>
                    <th>Méthode de candidature</th>
                    <th>Nombre de candidatures</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $methodsCount = array_count_values(array_column($candidatures, 'applymethod'));
                foreach ($methodsCount as $method => $count) : ?>
                    <tr>
                        <td><?php echo $method; ?></td>
                        <td><?php echo $count; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="container">
        <h1>Refus par départemement</h1>
        <table>
            <thead>
                <tr>
                    <th>Département</th>
                    <th>Nombre de candidatures refusées</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Filtrer les candidatures refusées
                $candidaturesRefusees = array_filter($candidatures, function($candidature) {
                    return $candidature['statut'] === 'Refusée';
                });

                // Compter le nombre de candidatures refusées par département
                $departementsRefuses = array_count_values(array_map('getDepartementFromPosition', array_column($candidaturesRefusees, 'position')));

                // Afficher le nombre de candidatures refusées par département
                foreach ($departementsRefuses as $departement => $count) {
                    echo "<tr>";
                    echo "<td>$departement</td>";
                    echo "<td>$count</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="container">
        <?php
// Fonction pour décoder les caractères Unicode
function decodeUnicode($string) {
    return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE');
    }, $string);
}

// Fonction pour calculer le délai entre deux dates
function calculateTimeDifference($date1, $date2) {
    $diff = abs(strtotime($date2) - strtotime($date1));
    return floor($diff / (60 * 60 * 24)); // Convertir en jours
}

// Récupérer les délais les plus courts et les plus longs
$shortestDelay = PHP_INT_MAX;
$longestDelay = 0;
$shortestDelayPoste = '';
$shortestDelayEntreprise = '';
$longestDelayPoste = '';
$longestDelayEntreprise = '';

foreach ($candidaturesRefusees as $candidature) {
    $refusDate = $candidature['historique_statut'][count($candidature['historique_statut']) - 1]['date_changement'];
    
    // Recherche du dernier entretien dans l'historique
    $lastEntretienDate = null;
    foreach ($candidature['historique_statut'] as $statut) {
        if ($statut['statut'] === 'Entretien passée') {
            $lastEntretienDate = $statut['date_changement'];
        }
    }

    if ($lastEntretienDate !== null) {
        // Calculer le délai entre le dernier entretien et le refus
        $delay = calculateTimeDifference($lastEntretienDate, $refusDate);
        
        // Mettre à jour les délais les plus courts et les plus longs
        if ($delay < $shortestDelay) {
            $shortestDelay = $delay;
            $shortestDelayPoste = decodeUnicode($candidature['poste']);
            $shortestDelayEntreprise = decodeUnicode($candidature['entreprise']);
        }
        
        if ($delay > $longestDelay) {
            $longestDelay = $delay;
            $longestDelayPoste = decodeUnicode($candidature['poste']);
            $longestDelayEntreprise = decodeUnicode($candidature['entreprise']);
        }
    } else {
        // Si aucun entretien passé n'est trouvé, prendre la date où le statut est passé à "En cours"
        $enCoursDate = null;
        foreach ($candidature['historique_statut'] as $statut) {
            if ($statut['statut'] === 'En cours') {
                $enCoursDate = $statut['date_changement'];
                break;
            }
        }

        if ($enCoursDate !== null) {
            // Calculer le délai entre le statut "En cours" et le refus
            $delay = calculateTimeDifference($enCoursDate, $refusDate);
            
            // Mettre à jour les délais les plus courts et les plus longs
            if ($delay < $shortestDelay) {
                $shortestDelay = $delay;
                $shortestDelayPoste = decodeUnicode($candidature['poste']);
                $shortestDelayEntreprise = decodeUnicode($candidature['entreprise']);
            }
            
            if ($delay > $longestDelay) {
                $longestDelay = $delay;
                $longestDelayPoste = decodeUnicode($candidature['poste']);
                $longestDelayEntreprise = decodeUnicode($candidature['entreprise']);
            }
        }
    }
}

// Afficher les délais les plus courts et les plus longs
if ($shortestDelay !== PHP_INT_MAX && $longestDelay !== 0) {
    echo "Le délai le plus court entre l'envoi de la candidature (en cours) et le refus est de $shortestDelay jours.<br>";
    echo "Poste : $shortestDelayPoste<br>";
    echo "Entreprise : $shortestDelayEntreprise<br><br>";
    echo "Le délai le plus long entre l'envoi de la candidature (en cours) et le refus est de $longestDelay jours.<br>";
    echo "Poste : $longestDelayPoste<br>";
    echo "Entreprise : $longestDelayEntreprise<br>";
} else {
    echo "Aucun délai trouvé entre l'envoi de la candidature (en cours) et le refus.<br>";
}
?>
    </div>

    <?php include 'assets/include/footer.php'; ?>
</body>

</html>