<?php
session_start();

// Fonction pour ajouter un utilisateur avec mot de passe chiffré au fichier JSON
function ajouterUtilisateur($nom_utilisateur, $mot_de_passe) {
    // Lecture du contenu actuel du fichier JSON
    $utilisateurs = json_decode(file_get_contents('assets/data/utilisateurs.json'), true);
    
    // Chiffrement du mot de passe
    $mot_de_passe_chiffre = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    // Ajout de l'utilisateur avec le mot de passe chiffré au tableau
    $utilisateurs[$nom_utilisateur] = array('mot_de_passe' => $mot_de_passe_chiffre);
    
    // Écriture du tableau mis à jour dans le fichier JSON
    file_put_contents('assets/data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
}

// Vérifie si l'utilisateur est déjà connecté, si oui, redirige vers le tableau de bord
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Vérifie si le formulaire de connexion a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lecture du contenu du fichier JSON contenant les informations d'authentification
    $utilisateurs = json_decode(file_get_contents('assets/data/utilisateurs.json'), true);

    // Vérification des informations d'identification
    if (isset($_POST['nom_utilisateur'], $_POST['mot_de_passe']) && isset($utilisateurs[$_POST['nom_utilisateur']])) {
        $utilisateur = $utilisateurs[$_POST['nom_utilisateur']];
        if (password_verify($_POST['mot_de_passe'], $utilisateur['mot_de_passe'])) {
            // Authentification réussie, démarre la session
            session_regenerate_id();
            $_SESSION['loggedin'] = true;
            $_SESSION['nom_utilisateur'] = $_POST['nom_utilisateur'];
            
            // Génération d'un nom unique de cookie basé sur l'identifiant de l'utilisateur
            $cookieName = 'role_' . $_SESSION['nom_utilisateur'];
            
            // Enregistrement du rôle dans un cookie avec l'option HTTP Only
            $role = $utilisateur['role']; // Supposant que le rôle soit déjà enregistré dans le fichier JSON
            setcookie($cookieName, $role, time() + (86400 * 30), "/", "", false, true); // Cookie valide pendant 30 jours
            
            header('Location: dashboard.php');
            exit;
        }
    }
    
    // Authentification échouée, affiche un message d'erreur
    $erreur = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification - Mon Entretien Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth-regist.css">
    <link rel="manifest" href="manifest.json">
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
    <div class="container">
        <h1>Authentification</h1>
        <?php if (isset($erreur) && $erreur === true) : ?>
            <p>Identifiants incorrects. Veuillez réessayer.</p>
        <?php endif; ?>
        <form method="post">
            <div>
                <label for="nom_utilisateur">Nom d'utilisateur:</label>
                <input type="text" id="nom_utilisateur" name="nom_utilisateur" required>
            </div>
            <div>
                <label for="mot_de_passe">Mot de passe:</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required>
            </div>
            <div>
                <button type="submit">Se connecter</button>
            </div>
        </form>
    </div>
</body>
</html>