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


// Fonction pour récupérer les candidatures
function getCandidatures() {
    return json_decode(file_get_contents('assets/data/candidatures.json'), true);
}

// Fonction pour mettre à jour les candidatures
function updateCandidatures($candidatures) {
    file_put_contents('assets/data/candidatures.json', json_encode($candidatures, JSON_PRETTY_PRINT));
}

// Code pour l'importation de candidatures
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["import"])) {
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $file_info = pathinfo($_FILES['file']['name']);
        if ($file_info['extension'] === 'json') {
            $json_data = file_get_contents($_FILES['file']['tmp_name']);
            $candidatures = json_decode($json_data, true);
            if ($candidatures !== null) {
                updateCandidatures($candidatures);
                echo "<div class='success-message'>Importation réussie.</div>";
            } else {
                echo "<div class='error-message'>Erreur lors du décodage du fichier JSON.</div>";
            }
        } else {
            echo "<div class='error-message'>Le fichier doit être au format JSON.</div>";
        }
    } else {
        echo "<div class='error-message'>Erreur lors de l'importation du fichier.</div>";
    }
}

// Code pour l'exportation de candidatures
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["export"])) {
    $candidatures = getCandidatures();
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="candidatures.json"');
    echo json_encode($candidatures);
    exit();
}

// Code pour la suppression de la base de données
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["confirm"])) {
    if ($_POST["confirm"] === "SUPPRIMER") {
        if (unlink('assets/data/candidatures.json')) {
            $uploadDir = 'uploads/';
            $files = glob($uploadDir . 'motivation_*.pdf');
            foreach ($files as $file) {
                unlink($file);
            }
            echo "Base de données et fichiers PDF de lettres de motivation supprimés avec succès.";
        } else {
            echo "Une erreur s'est produite lors de la suppression de la base de données.";
        }
    } else {
        echo "Mot de confirmation incorrect. La base de données n'a pas été supprimée.";
    }
}

// Fonctions pour charger et sauvegarder les options de contrat
function chargerOptionsContrat() {
    return json_decode(file_get_contents('assets/data/contrats.json'), true);
}

function sauvegarderOptionsContrat($options_contrat) {
    file_put_contents('assets/data/contrats.json', json_encode($options_contrat, JSON_PRETTY_PRINT));
}

$options_contrat = chargerOptionsContrat();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_contrat'])) {
    $action_contrat = $_POST['action_contrat'];
    
    if ($action_contrat === 'ajouter_contrat') {
        $nouveau_contrat = $_POST['nouveau_contrat'];
        $options_contrat[] = $nouveau_contrat;
        sauvegarderOptionsContrat($options_contrat);
    } elseif ($action_contrat === 'supprimer_contrat') {
        $contrat_index = $_POST['contrat_index'];
        unset($options_contrat[$contrat_index]);
        $options_contrat = array_values($options_contrat); // Réindexer le tableau
        sauvegarderOptionsContrat($options_contrat);
    }
}

// Fonctions pour charger et sauvegarder les utilisateurs
function chargerUtilisateurs() {
    return json_decode(file_get_contents('assets/data/utilisateurs.json'), true);
}

function sauvegarderUtilisateurs($utilisateurs) {
    file_put_contents('assets/data/utilisateurs.json', json_encode($utilisateurs, JSON_PRETTY_PRINT));
}

$utilisateurs = chargerUtilisateurs();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'ajouter') {
        $nouveau_nom_utilisateur = $_POST['nouveau_nom_utilisateur'];
        $nouveau_mot_de_passe = password_hash($_POST['nouveau_mot_de_passe'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $utilisateurs[$nouveau_nom_utilisateur] = array('mot_de_passe' => $nouveau_mot_de_passe, 'role' => $role);
        sauvegarderUtilisateurs($utilisateurs);
    } elseif ($action === 'modifier_mot_de_passe') {
        $utilisateur = $_POST['utilisateur'];
        $nouveau_mot_de_passe = password_hash($_POST['nouveau_mot_de_passe'], PASSWORD_DEFAULT);
        $utilisateurs[$utilisateur]['mot_de_passe'] = $nouveau_mot_de_passe;
        sauvegarderUtilisateurs($utilisateurs);
    } elseif ($action === 'modifier_role') {
        $utilisateur = $_POST['utilisateur'];
        $nouveau_role = $_POST['nouveau_role'];
        $utilisateurs[$utilisateur]['role'] = $nouveau_role;
        sauvegarderUtilisateurs($utilisateurs);
    } elseif ($action === 'supprimer') {
        $utilisateur = $_POST['utilisateur'];
        unset($utilisateurs[$utilisateur]);
        sauvegarderUtilisateurs($utilisateurs);
    }
}

// Génération de la clé API sécurisée
function generateApiKey() {
    return bin2hex(random_bytes(16)); // Génère une clé API de 32 caractères
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["generate_api_key"])) {
    $apiKey = generateApiKey();
    file_put_contents('api/cfapi.txt', $apiKey);
    echo "<div class='success-message'>Clé API générée et enregistrée avec succès.</div>";
}

// Génération du QR Code
require_once 'phpqrcode/qrlib.php';
$baseUrl = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$apiKey = trim(file_get_contents('api/cfapi.txt'));
$apiUrl = $baseUrl . '/api';
$configuration = '{"apiUrl":"' . $apiUrl . '","apiKey":"' . $apiKey . '"}';

ob_start();
QRcode::png($configuration, null, QR_ECLEVEL_L, 4);
$qrImage = ob_get_contents();
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Mon Entretien Pro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="manifest" href="manifest.json">
    <script>
        function afficherFichier(cheminFichier) {
            window.location.href = cheminFichier;
        }
    </script>
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
<div class="container">
    <h1><i class="fas fa-cog"></i> Administration de la base de données</h1>
    
    <div class="admin-section">
        <h2>Configuration de l'application mobile</h2>
        <div class="qr-code-container">
            <div class="qr-code">
                <img src="data:image/png;base64,<?php echo base64_encode($qrImage); ?>" alt="QR Code">
            </div>
            <button type="button" class="btn btn-green" onclick="afficherFichier('keys.php')">Initialiser les clés de signature PDF</button>
        </div>
    </div>

    <div class="admin-section">
        <h2>Générer une clé API</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <button type="submit" name="generate_api_key">Générer une clé API</button>
        </form>
    </div>

    <div class="admin-section candidatures-section">
        <h2>Gérer les candidatures</h2>
        <div class="candidature-actions">
            <div class="import-export">
                <h3>Importer des candidatures</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <label for="file">Sélectionnez un fichier JSON à importer :</label>
                    <input type="file" id="file" name="file" accept=".json" required>
                    <button type="submit" name="import" class="btn btn-green">Importer</button>
                </form>
            </div>
            <div class="import-export">
                <h3>Exporter des candidatures</h3>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <button type="submit" name="export" class="btn btn-blue">Exporter en JSON</button>
                </form>
            </div>
        </div>
    </div>

    <div class="admin-section">
        <h2>Supprimer la base de données</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <p>Pour supprimer la base de données, tapez <strong>SUPPRIMER</strong> ci-dessous :</p>
            <input type="text" name="confirm" required>
            <button type="submit" class="btn btn-red">Supprimer</button>
        </form>
    </div>

    <div class="admin-section">
    <h2>Options de contrat</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="contract-form">
        <input type="hidden" name="action_contrat" value="ajouter_contrat">
        <input type="text" name="nouveau_contrat" placeholder="Nouveau contrat" required>
        <button type="submit" class="btn btn-green">Ajouter</button>
    </form>
    <table class="contract-table">
        <thead>
            <tr>
                <th>Contrat</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($options_contrat as $index => $contrat) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($contrat); ?></td>
                    <td>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" style="display:inline;">
                            <input type="hidden" name="action_contrat" value="supprimer_contrat">
                            <input type="hidden" name="contrat_index" value="<?php echo $index; ?>">
                            <button type="submit" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contrat ?');" class="btn btn-red">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="admin-section">
    <h2>Gestion des utilisateurs</h2>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="user-form">
        <input type="hidden" name="action" value="ajouter">
        <input type="text" name="nouveau_nom_utilisateur" placeholder="Nom d'utilisateur" required>
        <input type="password" name="nouveau_mot_de_passe" placeholder="Mot de passe" required>
        <select name="role">
            <option value="administrateur">Administrateur</option>
            <option value="utilisateur">Utilisateur</option>
        </select>
        <button type="submit" class="btn btn-green">Ajouter</button>
    </form>
    <table class="user-table">
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($utilisateurs as $nom_utilisateur => $info) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($nom_utilisateur); ?></td>
                    <td><?php echo htmlspecialchars($info['role']); ?></td>
                    <td>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="inline-form" style="display:inline;">
                            <input type="hidden" name="action" value="modifier_mot_de_passe">
                            <input type="hidden" name="utilisateur" value="<?php echo $nom_utilisateur; ?>">
                            <input type="password" name="nouveau_mot_de_passe" placeholder="Nouveau mot de passe" style="width: 120px;">
                            <button type="submit" class="btn btn-blue">Modifier</button>
                        </form>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="inline-form" style="display:inline;">
                            <input type="hidden" name="action" value="modifier_role">
                            <input type="hidden" name="utilisateur" value="<?php echo $nom_utilisateur; ?>">
                            <select name="nouveau_role" style="width: auto;">
                                <option value="administrateur" <?php echo ($info['role'] === 'administrateur') ? 'selected' : ''; ?>>Administrateur</option>
                                <option value="utilisateur" <?php echo ($info['role'] === 'utilisateur') ? 'selected' : ''; ?>>Utilisateur</option>
                            </select>
                            <button type="submit" class="btn btn-blue">Modifier</button>
                        </form>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="inline-form" style="display:inline;">
                            <input type="hidden" name="action" value="supprimer">
                            <input type="hidden" name="utilisateur" value="<?php echo $nom_utilisateur; ?>">
                            <button type="submit" class="btn btn-red" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>