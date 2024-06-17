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

// On vérifie qu'une page est bien passé en paramètre
if(isSet($_GET["page"])) {
    // On récupère la page
    $page = $_GET["page"];
}
else {
    echo "Erreur dans l'application";
    exit();
}

/**
 * Traitements
 * @todo 
 */

// On instancie un objet controller pour la page concerné
$objController = new $page($_REQUEST);
// On lance l'execution du controller
$objController->execute();

/**
 * Affichage ou Retour
 * @todo 
 */
$objController->render();