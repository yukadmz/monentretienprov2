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

// Inclusion de la bibliothèque TCPDF
require_once('tcpdf/tcpdf.php');

// Récupération de l'identifiant de la candidature depuis l'URL
$idCandidature = $_GET['id'] ?? null;

// Vérification de la présence de l'identifiant
if ($idCandidature === null) {
    echo "Aucune candidature sélectionnée.";
    exit;
}

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
    echo "Cette candidature n'existe pas.";
    exit;
}

// Création d'une nouvelle instance TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Paramètres du document PDF
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('MonEntretienPro - Simplifiez votre recherche d\'emploi, un entretien à la fois.' );
$pdf->SetTitle('Candidature - ' . $candidature['entreprise']);
$pdf->SetSubject('Détails de la candidature');
$pdf->SetKeywords('Candidature, PDF, Export');

// Début du document PDF
$pdf->AddPage();

// Contenu du PDF
$pdf->SetFont('Helvetica', '', 12);

// Concaténation de la date et de l'heure pour le champ Date d'entretien
$dateEntretien = date('d/m/Y H:i', strtotime($candidature['date_entretien']));

// Formater la date de candidature
$dateCandidature = date('d/m/Y', strtotime($candidature['applydate']));

// Convertir les sauts de ligne en balises HTML <br> pour le champ infostatut
$infostatut = nl2br($candidature['infostatut']);

// Convertir les sauts de ligne en balises HTML <br> pour le champ contact
$contact = nl2br($candidature['contact']);

// Tableau pour afficher les détails de la candidature
$html = '<h2>Mon Entretien Pro | Suivi de la candidature</h2>';
$html .= '<table cellpadding="5">';
$html .= '<tr><td><strong>Date de candidature</strong></td><td>' . $dateCandidature . '</td><td><strong>Numéro de candidature</strong></td><td>' . $candidature['id'] . '</td></tr>';
$html .= '<tr><td><strong>Entreprise</strong></td><td><strong>' . $candidature['entreprise'] . '</strong></td><td><strong>Localisation</strong></td><td>' . $candidature['position'] . '</td></tr>';
$html .= '<tr><td><strong>Poste</strong></td><td colspan="3"><strong>' . $candidature['poste'] . '</strong></td></tr>';
$html .= '<tr><td><strong>Lien du poste</strong></td><td colspan="3">' . $candidature['lien'] . '</td></tr>';
$html .= '<tr><td><strong>Type de contrat</strong></td><td><strong>' . $candidature['contrat'] . '</strong></td><td><strong>Disponibilité</strong></td><td>' . $candidature['disponibilite'] . '</td></tr>';
$html .= '<tr><td><strong>Statut de l\'offre</strong></td><td><strong>' . $candidature['statut'] . '</strong></td><td><strong>Salaire</strong></td><td>' . $candidature['salaire'] . '€</td></tr>';
$html .= '<tr><td><strong>Date d\'entretien</strong></td><td colspan="3">' . $dateEntretien . '</td></tr>';
$html .= '<tr><td><span style="color: #007bff;"><strong>Méthode de candidature</strong></span></td><td><span style="color: #007bff;">' .  $candidature['applymethod'] . '</span></td><td><span style="color: #007bff;"><strong>Contact</strong></span></td><td style="text-align: justify;"><span style="color: #007bff;">' .  $contact . '</span></td></tr>';
$html .= '<tr><td colspan="4"><strong>Observations</strong></td></tr>';
$html .= '<tr><td colspan="4" style="text-align:justify;">' . $infostatut . '</td></tr>';
$html .= '<h2>Historique des changements de statut</h2>';
$html .= '<table cellpadding="5">';
$html .= '<tr><th>Date</th><th>Statut</th></tr>';
foreach ($candidature['historique_statut'] as $statut) {
    $dateStatut = date('d/m/Y H:i', strtotime($statut['date_changement'])); // Formatage de la date
    $html .= '<tr><td>' . $dateStatut . '</td><td>' . $statut['statut'] . '</td></tr>';
}
$html .= '</table>';

$pdf->writeHTML($html);

// Nom du fichier PDF à télécharger
$pdfFileName = 'candidature_' . $candidature['entreprise'] . '_' . $candidature['contrat'] . '_' . $candidature['poste'] . '.pdf';

// Générer une signature numérique
$privateKey = 'assets/keys/private.pem';
$certificate = 'assets/keys/public.pem';
$signatureDetails = array(
    'Name' => 'Mon Entretien Pro',
    'Location' => 'Paris, FR',
    'Reason' => 'Certification du détail de la candidature',
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

// Vérification du hachage
$signatureDetails = $pdf->getSignatureData();
$embeddedSignature = $signatureDetails['signature'];

// Calcul du hachage du contenu du document
$contentHash = hash('sha256', $pdf->getBuffer());

// Vérification du hachage
if ($contentHash === $embeddedSignature) {
    echo "Le hachage du contenu du document correspond à l'empreinte numérique de la signature. Le document n'a pas été modifié après avoir été signé.";
} else {
    echo "Le hachage du contenu du document ne correspond pas à l'empreinte numérique de la signature. Le document a été modifié après avoir été signé.";
}

// Téléchargement du PDF avec la signature numérique
$pdf->Output($pdfFileName, 'D');
?>
