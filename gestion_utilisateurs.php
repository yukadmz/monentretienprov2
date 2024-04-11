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

// Vérifie si l'utilisateur a le rôle d'administrateur ou de modification
if ($_COOKIE[$cookieName] !== 'administrateur') {
    header('Location: index.php'); // Remplacez "autre_page.php" par l'URL de la page vers laquelle vous souhaitez rediriger les utilisateurs non autorisés
    exit;
}


// Reste du code de votre page...
?>
<?php
// Fonction pour charger les utilisateurs depuis le fichier JSON
function chargerUtilisateurs() {
    return json_decode(file_get_contents('assets/data/utilisateurs.json'), true);
}

// Fonction pour sauvegarder les utilisateurs dans le fichier JSON
function sauvegarderUtilisateurs($utilisateurs) {
    file_put_contents('assets/data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
}

// Chargement des utilisateurs
$utilisateurs = chargerUtilisateurs();

// Traitement des actions (ajouter, modifier, supprimer)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        // Action d'ajouter un utilisateur
        if ($action === 'ajouter') {
            $nouveau_nom_utilisateur = $_POST['nouveau_nom_utilisateur'];
            $nouveau_mot_de_passe = password_hash($_POST['nouveau_mot_de_passe'], PASSWORD_DEFAULT);
            $role = $_POST['role'];
            $utilisateurs[$nouveau_nom_utilisateur] = array('mot_de_passe' => $nouveau_mot_de_passe, 'role' => $role);
            sauvegarderUtilisateurs($utilisateurs);
        }
        
        // Action de modifier le mot de passe d'un utilisateur
        elseif ($action === 'modifier_mot_de_passe') {
            $utilisateur = $_POST['utilisateur'];
            $nouveau_mot_de_passe = password_hash($_POST['nouveau_mot_de_passe'], PASSWORD_DEFAULT);
            $utilisateurs[$utilisateur]['mot_de_passe'] = $nouveau_mot_de_passe;
            sauvegarderUtilisateurs($utilisateurs);
        }
        
        // Action de modifier le rôle d'un utilisateur
        elseif ($action === 'modifier_role') {
            $utilisateur = $_POST['utilisateur'];
            $nouveau_role = $_POST['nouveau_role'];
            $utilisateurs[$utilisateur]['role'] = $nouveau_role;
            sauvegarderUtilisateurs($utilisateurs);
        }
        
        // Action de supprimer un utilisateur
        elseif ($action === 'supprimer') {
            $utilisateur = $_POST['utilisateur'];
            unset($utilisateurs[$utilisateur]);
            sauvegarderUtilisateurs($utilisateurs);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Assurez-vous d'avoir un fichier style.css -->
    <link rel="manifest" href="manifest.json">
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
    <div class="container">
        <h1>Gestion des utilisateurs</h1>

        <!-- Affichage de la liste des utilisateurs -->
        <h2>Liste des utilisateurs</h2>
        <ul>
            <?php foreach ($utilisateurs as $utilisateur => $infos) : ?>
                <li>
                    <?php echo $utilisateur . " (" . $infos['role'] . ")"; ?>
                    <form method="post">
                        <input type="hidden" name="utilisateur" value="<?php echo $utilisateur; ?>">
                        <input type="password" name="nouveau_mot_de_passe" placeholder="Nouveau mot de passe">
                        <select name="nouveau_role">
                            <option value="administrateur" <?php if($infos['role'] === 'administrateur') echo 'selected'; ?>>Administrateur</option>
                            <option value="lecture" <?php if($infos['role'] === 'lecture') echo 'selected'; ?>>Lecture</option>
                            <option value="modification" <?php if($infos['role'] === 'modification') echo 'selected'; ?>>Modification</option>
                        </select>
                        <button type="submit" name="action" value="modifier_mot_de_passe">Modifier mot de passe</button><br></br>
                        <button type="submit" name="action" value="modifier_role">Modifier rôle</button><br></br>
                        <button type="submit" name="action" value="supprimer">Supprimer</button><br></br>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Formulaire pour ajouter un nouvel utilisateur -->
        <h2>Ajouter un nouvel utilisateur</h2>
        <form method="post">
            <input type="text" name="nouveau_nom_utilisateur" placeholder="Nom d'utilisateur" required>
            <input type="password" name="nouveau_mot_de_passe" placeholder="Mot de passe" required>
            <select name="role">
                <option value="administrateur">Administrateur</option>
                <option value="lecture">Lecture</option>
                <option value="modification">Modification</option>
            </select>
            <button type="submit" name="action" value="ajouter">Ajouter</button>
        </form>
    </div>
</body>
</html>
