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

// Vérifie si le formulaire d'inscription a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifie si les champs sont non vides
    if (!empty($_POST['nom_utilisateur']) && !empty($_POST['mot_de_passe'])) {
        // Appel de la fonction pour ajouter l'utilisateur
        ajouterUtilisateur($_POST['nom_utilisateur'], $_POST['mot_de_passe']);
        // Redirige vers la page de connexion après l'inscription réussie
        header('Location: index.php');
        exit;
    }
    else {
        $erreur = true; // Indique une erreur si les champs sont vides
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Mon Entretien Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth-regist.css">
    <link rel="manifest" href="manifest.json">
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>
        <?php if (isset($erreur) && $erreur === true) : ?>
            <p>Tous les champs sont obligatoires.</p>
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
                <button type="submit">S'inscrire</button>
            </div>
        </form>
    </div>
</body>
</html>
