<?php
// Vérifie si l'utilisateur est connecté, sinon redirige vers la page d'authentification
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Vérifie si l'utilisateur a le rôle d'administrateur ou de modification
if ($_COOKIE['role'] !== 'administrateur' && $_COOKIE['role'] !== 'modification') {
    header('Location: index.php'); // Remplacez "autre_page.php" par l'URL de la page vers laquelle vous souhaitez rediriger les utilisateurs non autorisés
    exit;
}

// Reste du code de votre page...
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une candidature - Suivi des Candidatures</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <link rel="manifest" href="manifest.json">
</head>
<body>
<?php include 'assets/include/navbar.php'; ?>
    <div class="container">
        <h1>Ajouter une candidature</h1>
        <?php
        // Vérification si le formulaire a été soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Génération de l'identifiant unique
            $id = uniqid();

            // Récupération des données du formulaire et ajout de l'identifiant unique
            $nouvelleCandidature = array(
                "id" => $id,
                "applydate" => $_POST['applydate'],
                "entreprise" => $_POST['entreprise'],
                "position" => $_POST['position'],
                "poste" => $_POST['poste'],
                "lien" => $_POST['lien'],
                "contrat" => $_POST['contrat'],
                "disponibilite" => $_POST['disponibilite'],
                "contact" => $_POST['contact'],
                "statut" => $_POST['statut'],
                "applymethod" => $_POST['applymethod'],
                "date_entretien" => $_POST['date_entretien'],
                "deuxieme_entretien" => $_POST['deuxieme_entretien'],
                "infostatut" => $_POST['infostatut'],
                "salaire" =>
                    $_POST['salaire']
            );

            // Lecture du contenu du fichier JSON existant
            $candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);

            // Ajout de la nouvelle candidature au tableau existant
            $candidatures[] = $nouvelleCandidature;

            // Écriture du contenu mis à jour dans le fichier JSON
            file_put_contents('assets/data/candidatures.json', json_encode($candidatures));

            echo "Nouvelle candidature ajoutée avec succès.";
        }
        ?>
        <form class="add-form" action="ajouter_candidature.php" method="post">
            <div class="form-group">
                <label for="applydate">Date de candidature :</label>
                <input type="date" id="applydate" name="applydate">
            </div>
            <div class="form-group">
                <label for="position">Localisation :</label>
                <input type="text" id="position" name="position">
            </div>
            <div class="form-group">
                <label for="entreprise">Entreprise :</label>
                <input type="text" id="entreprise" name="entreprise" required>
            </div>
            <div class="form-group">
                <label for="poste">Poste :</label>
                <input type="text" id="poste" name="poste" required>
            </div>
            <div class="form-group">
                <label for="lien">Lien du poste :</label>
                <input type="text" id="lien" name="lien">
            </div>
            <div class="form-group">
                <label for="contrat">Type de contrat :</label>
                <select id="contrat" name="contrat">
                    <option value="CDI">CDI</option>
                    <option value="CDD">CDD</option>
                    <option value="Intérim">Intérim</option>
                    <option value="Stage">Stage</option>
                    <option value="Alternance">Alternance</option>
                    <option value="CTT">CTT</option>
                    <!-- Ajoutez d'autres options si nécessaire -->
                </select>
            </div>
            <div class="form-group">
                <label for="disponibilite">Disponibilité :</label>
                <input type="text" id="disponibilite" name="disponibilite">
            </div>
            <div class="form-group">
                <label for="contact">Personne à contacter :</label>
                <textarea name="contact" id="contact" rows="4" placeholder="Identité
Adresse mail
Téléphone"></textarea>
            </div>
            <div class="form-group">
                <label for="statut">Statut de la candidature :</label>
                <select id="statut" name="statut">
                    <option value="En attente">En attente</option>
                    <option value="En cours">En cours</option>
                    <option value="Entretien">Entretien</option>
                    <option value="Entretien passé">Entretien passé</option>
                    <option value="Acceptée">Acceptée</option>
                    <option value="Refusée">Refusée</option>
                    <option value="Config">Config</option>
                    <!-- Ajoutez d'autres options si nécessaire -->
                </select>
            </div>
            <div class="form-group">
                <label for="applymethod">Méthode de candidature :</label>
                <select id="applymethod" name="applymethod">
                    <option value="Candidature en ligne">Candidature en ligne</option>
                    <option value="Candidature papier">Candidature papier</option>
                    <option value="Envoyée par mail">Envoyée par mail</option>
                    <option value="Transfert de profil">Transfert de profil</option>
                    <option value="Intéressée par le profil">Intéressée par le profil</option>
                </select>
            </div>
            <div class="form-group">
                <label for="date_entretien">Date d'entretien :</label>
                <input type="datetime-local" id="date_entretien" name="date_entretien">
            </div>
            <div class="form-group">
                <label for="date_entretien">Deuxième entretien :</label>
                <input type="checkbox" id="deuxieme_entretien" name="deuxieme_entretien">
            </div>
            <div class="form-group">
                <label for="infostatut">Observations :</label>
                <textarea name="infostatut" id="infostatut" rows="4"></textarea>
            </div>
            <div class="form-group">
                <label for="salaire">Salaire :</label>
                <input type="number" id="salaire" inputmode="numeric" name="salaire">
            </div>
            
            <button type="submit" class="btn">Ajouter</button>
        </form>
    </div>
</body>
</html>
