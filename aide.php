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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide - Mon Entretien Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <script>
        // JavaScript pour le pliage des sections
        document.addEventListener("DOMContentLoaded", function() {
            const headers = document.querySelectorAll('.section-header');
            headers.forEach(header => {
                header.addEventListener('click', function() {
                    const content = this.nextElementSibling;
                    content.style.display = (content.style.display === 'block') ? 'none' : 'block';
                });
            });
            
            // Initialisation des sections pour être fermées par défaut
            const contents = document.querySelectorAll('.section-content');
            contents.forEach(content => {
                content.style.display = 'none';
            });
        });
    </script>
    <style>
        /* Styles pour l'image et les sections */
        .mobile-app-img {
            width: 150px; /* Ajustez la taille de l'image ici */
            display: block;
            margin: 10px auto; /* Centrer l'image */
        }
        .section-header {
            cursor: pointer;
            background-color: #f0f0f0;
            padding: 10px;
            margin: 5px 0;
            border: 1px solid #ccc; /* Optionnel, pour l'apparence */
            border-radius: 5px;
        }
        .section-content {
            padding: 10px;
            border-left: 1px solid #ccc; /* Optionnel, pour l'apparence */
            border-right: 1px solid #ccc; /* Optionnel, pour l'apparence */
            border-bottom: 1px solid #ccc; /* Optionnel, pour l'apparence */
            background-color: #fafafa; /* Optionnel, pour l'apparence */
        }
    </style>
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
    <div class="container">
    <!-- Contenu de la page d'aide -->
    <h1>Aide - Mon Entretien Pro</h1>

    <h2>FAQ (Foire Aux Questions)</h2>

    <div class="section-header"><h3>Y a-t-il une application mobile disponible ?</h3></div>
    <div class="section-content">
        <p>Oui, une application mobile est disponible pour les appareils iOS. Toutefois, elle n’est pas accessible via l’App Store et nécessite d'être compilée à l'aide du logiciel Xcode.</p>
        <p>Une version pour Android sera bientôt disponible.</p>
        <a href="https://github.com/yukadmz/CandidatureTracker">
            <img src="https://axeld.yn.lu/monentretienpro/assets/img/fr_badge_web_generic.png" class="mobile-app-img" alt="Télécharger l'application mobile"/>
        </a>
    </div>

    <div class="section-header"><h3>Pourquoi apparaît le message "Aucune date limite de recherche d'emploi n'a été déterminée." ?</h3></div>
    <div class="section-content">
        <p>Ce message s'affiche lorsque le formulaire d'ajout de candidature n'a pas été complété conformément aux indications suivantes :</p>
        <ol>
            <li>La localisation peut être renseignée avec n'importe quel texte.</li>
            <li>Dans le champ "Entreprise", commencez par indiquer un libellé tel que "Date limite pour trouver un emploi :", suivi de votre message ou de vos encouragements.</li>
            <li>Le champ "Poste" peut contenir la suite de votre message.</li>
            <li>Le "Statut de la candidature" doit être défini sur "Config".</li>
            <li>Veuillez enregistrer le formulaire.</li>
        </ol>
    </div>

    <div class="section-header"><h3>Comment puis-je ajouter une nouvelle candidature ?</h3></div>
    <div class="section-content">
        <p>Pour ajouter une nouvelle candidature, cliquez sur l'icône <i class="fas fa-plus-circle"></i> située dans la barre de navigation. Remplissez ensuite le formulaire avec les informations pertinentes et soumettez-le.</p>
    </div>

    <div class="section-header"><h3>Comment modifier ou supprimer une candidature existante ?</h3></div>
    <div class="section-content">
        <p>Pour modifier ou supprimer une candidature existante, accédez à la liste des candidatures sur la page d'accueil. Cliquez sur la candidature concernée. Si vous disposez des autorisations nécessaires, vous serez redirigé vers la page de modification où vous trouverez le formulaire approprié ainsi qu'un bouton "Supprimer" pour effectuer la suppression définitive.</p>
    </div>

    <div class="section-header"><h3>Comment exporter une candidature au format PDF ?</h3></div>
    <div class="section-content">
        <p>Pour exporter une candidature au format PDF, ouvrez la candidature que vous souhaitez exporter, puis cliquez sur le bouton "Exporter en PDF" situé en bas de la page. Le PDF sera généré et vous pourrez le télécharger.</p>
    </div>

    <div class="section-header"><h3>Est-il possible de filtrer les entretiens en attente ?</h3></div>
    <div class="section-content">
        <p>Pour accéder à votre agenda, cliquez sur l'icône <i class="fas fa-calendar-alt"></i> dans la barre de navigation. Vous y trouverez la liste des prochains entretiens, que vous pourrez marquer comme terminés une fois qu'ils auront eu lieu.</p>
    </div>

    <div class="section-header"><h3>Comment gérer l'ensemble de mon espace ?</h3></div>
    <div class="section-content">
        <p>Pour accéder à l'interface d'administration, cliquez sur l'icône <i class="fas fa-cog"></i> dans la barre de navigation. Vous pourrez alors :</p>
        <ul>
            <li>Importer et exporter des bases de données.</li>
            <li>Supprimer définitivement des bases de données.</li>
            <li>Gérer les types de contrats.</li>
        </ul>
    </div>

    <h4><i class="fas fa-users-cog"></i> Gestion des utilisateurs :</h4>
    <div class="section-content">
        <ul>
            <li>Modification de mot de passe.</li>
            <li>Changement de rôle.</li>
            <li>Création de nouveaux utilisateurs.</li>
        </ul>
    </div>

    <div class="section-header"><h3>Pourquoi une icône d'horloge <i class="fas fa-clock clock"></i> et un nombre de jours apparaissent-ils sur certaines candidatures dans mon tableau de bord ?</h3></div>
    <div class="section-content">
        <p>Ces indicateurs vous permettent d'évaluer rapidement le statut de vos candidatures :</p>
        <ul>
            <li><strong>En attente / En cours :</strong> Plus d'un mois s'est écoulé depuis l'envoi de la candidature sans proposition d'entretien.</li>
            <li><strong>Entretien passé :</strong> Plus de cinq jours se sont écoulés depuis l'entretien (maximum de deux entretiens).</li>
        </ul>
        <p><i>Cela vous aide à suivre l'évolution de vos candidatures et à contacter les entreprises le cas échéant.</i></p>
    </div>

    <div class="section-header"><h3>Sur la page d'administration, que signifie le bouton "Initialiser les clés de signature PDF" ?</h3></div>
    <div class="section-content">
        <p>Ce bouton est nécessaire lors de votre première utilisation de l'application. Il initialise vos clés de signature pour les fichiers PDF, garantissant ainsi leur authenticité et leur intégrité.</p>
        <p>En l'absence de signature numérique sur un fichier PDF, cela signifie qu'il a été modifié et n'est plus fiable. Vous avez alors la possibilité de renouveler vos clés.</p>
        <b><i>En cas de suspicion d'usurpation de clés, veuillez les renouveler en cliquant sur le bouton correspondant.</i></b>
    </div>

    </div>
    
<?php include 'assets/include/footer.php'; ?>
</body>
</html>