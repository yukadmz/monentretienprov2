<?php
// Fonction pour générer les options de contrat
function genererOptionsContrats($options_contrats, $candidature) {
    $options_html = '';
    foreach ($options_contrats as $option) {
        $selected = ($candidature['contrat'] === $option) ? 'selected' : '';
        $options_html .= "<option value='$option' $selected>$option</option>";
    }
    return $options_html;
}

// Charger les options de contrat à partir du fichier JSON
$options_contrats_json = file_get_contents('assets/data/contrats.json');
$options_contrats = json_decode($options_contrats_json, true);
?>
