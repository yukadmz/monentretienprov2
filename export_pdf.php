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
?>
<?php
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
$html = '<h2>Suivi de la candidature</h2>';
$html .= '<table cellpadding="5">';
$html .= '<tr><td><strong>Date de candidature</strong></td><td>' . $dateCandidature . '</td><td><strong>Localisation</strong></td><td>' . $candidature['position'] . '</td></tr>';
$html .= '<tr><td><strong>Entreprise</strong></td><td colspan="3"><strong>' . $candidature['entreprise'] . '</strong></td></tr>';
$html .= '<tr><td><strong>Poste</strong></td><td colspan="3"><strong>' . $candidature['poste'] . '</strong></td></tr>';
$html .= '<tr><td><strong>Lien du poste</strong></td><td colspan="3">' . $candidature['lien'] . '</td></tr>';
$html .= '<tr><td><strong>Type de contrat</strong></td><td><strong>' . $candidature['contrat'] . '</strong></td><td><strong>Disponibilité</strong></td><td>' . $candidature['disponibilite'] . '</td></tr>';
$html .= '<tr><td><strong>Statut de l\'offre</strong></td><td><strong>' . $candidature['statut'] . '</strong></td><td><strong>Salaire</strong></td><td>' . $candidature['salaire'] . '</td></tr>';
$html .= '<tr><td><strong>Date d\'entretien</strong></td><td colspan="3">' . $dateEntretien . '</td></tr>';
$html .= '<tr><td><span style="color: #007bff;"><strong>Méthode de candidature</strong></span></td><td><span style="color: #007bff;">' .  $candidature['applymethod'] . '</span></td><td><span style="color: #007bff;"><strong>Contact</strong></span></td><td style="text-align: justify;"><span style="color: #007bff;">' .  $contact . '</span></td></tr>';
$html .= '<tr><td colspan="4"><strong>Observations</strong></td></tr>';
$html .= '<tr><td colspan="4" style="text-align:justify;">' . $infostatut . '</td></tr>';
$html .= '</table>';

$pdf->writeHTML($html);

// Nom du fichier PDF à télécharger
$pdfFileName = 'candidature_' . $candidature['entreprise'] . '_' . $candidature['contrat'] . '_' . $candidature['poste'] . '.pdf';

// Téléchargement du PDF
$pdf->Output($pdfFileName, 'D');

