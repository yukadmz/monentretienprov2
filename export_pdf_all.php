<?php
// Vérifie si l'utilisateur est connecté, sinon redirige vers la page d'authentification
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
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

// Téléchargement du PDF
$pdf->Output($pdfFileName, 'D');
?>
