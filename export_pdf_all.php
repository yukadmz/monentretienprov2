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
require_once('tcpdf/tcpdf.php');

// Lecture du contenu du fichier JSON
$candidatures = json_decode(file_get_contents('assets/data/candidatures.json'), true);

// Fonction de comparaison pour trier par date de candidature
function compareByApplyDate($a, $b) {
    $dateA = strtotime($a['applydate']);
    $dateB = strtotime($b['applydate']);
    return $dateA - $dateB;
}

// Trier les candidatures par date de candidature
usort($candidatures, 'compareByApplyDate');

// Création d'une nouvelle instance TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Paramètres du document PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('MonEntretienPro - Simplifiez votre recherche d\'emploi, un entretien à la fois.' );
$pdf->SetTitle('Export des candidatures');
$pdf->SetSubject('Toutes les candidatures');
$pdf->SetKeywords('Candidature, PDF, Export');

// Début du document PDF
$pdf->AddPage();

// Contenu du PDF
$pdf->SetFont('Helvetica', '', 12);

// Tableau pour afficher les candidatures
$html = '<h2>Liste des candidatures</h2>';
$html .= '<table cellpadding="5">';
$html .= '<strong><tr><th>Date de candidature</th><th>Entreprise</th><th>Poste</th><th>Contrat</th><th>Statut</th></tr></strong>';

foreach ($candidatures as $candidature) {
    // Exclure les candidatures ayant le statut "config"
    if (strtolower($candidature['statut']) !== "config") {
        // Formater la date de candidature
        $dateCandidature = date('d/m/Y', strtotime($candidature['applydate']));

        // Ajouter une ligne pour la candidature
        $html .= '<tr>';
        $html .= '<td>' . $dateCandidature . '</td>';
        $html .= '<td>' . $candidature['entreprise'] . '</td>';
        $html .= '<td>' . $candidature['poste'] . '</td>';
        $html .= '<td>' . $candidature['contrat'] . '</td>';
        $html .= '<td>' . $candidature['statut'] . '</td>';
        $html .= '</tr>';
    }
}

$html .= '</table>';

// Ajout du contenu HTML
$pdf->writeHTML($html);

//Ajout du numéro de page
//$numberOfPages = $pdf->getNumPages();
//for ($i = 1; $i <= $numberOfPages; $i++) {
//    $pdf->setPage($i);
//    $pdf->Text(15, 285, "Page $i de $numberOfPages");
//}

// Nom du fichier PDF à télécharger
$pdfFileName = 'export_candidatures.pdf';

// Générer une signature numérique
$privateKey = 'assets/keys/private.pem';
$certificate = 'assets/keys/public.pem';
$signatureDetails = array(
    'Name' => 'Mon Entretien Pro',
    'Location' => 'Paris, FR',
    'Reason' => 'Certification de l\'ensemble des candidatures',
    'ContactInfo' => 'axel@dumez.cloud', // Ajoutez ici les informations de contact si nécessaire
    'M' => time() // Moment de la signature
);

// Paramètres supplémentaires pour la signature numérique
$additionalParams = array(
    'Location' => 'Paris, FR', // Emplacement de la signature
    'ContactInfo' => 'axel@dumez.cloud', // Informations de contact pour la personne ou l'organisation ayant signé le document
    'M' => time() // Moment de la signature (ici, actuellement)
);

// Configuration de l'algorithme de hachage et de l'algorithme de signature
$hashAlgorithm = 'sha256';
$signatureAlgorithm = OPENSSL_ALGO_SHA256;

// Générer une signature numérique
$pdf->setSignature($privateKey, $certificate, $hashAlgorithm, $signatureAlgorithm, 1, $signatureDetails, $additionalParams); // Pas de mot de passe pour la clé privée

// Téléchargement du PDF avec la signature numérique
$pdf->Output($pdfFileName, 'D');
?>
