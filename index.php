<?php

/**
 * Contrôleur : Controleur principale
 * @todo 
 * Paramètres :
 *  @param variable GET - Page à afficher
 */

/**
 * Initialisation
 * @todo 
 */
// On inclut le fichier init
require_once "src/utils/init.php";

/**
 * Récupération des paramètres
 * @todo 
 */
if(isSet($_GET["page"])) {
    $page = $_GET["page"];
}
else {
    echo "Erreur dans l'application";
    exit;
}

/**
 * Traitements
 * @todo 
 */
$objController = new $page();
$objController->execute();