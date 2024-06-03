<?php

/**
 * Classe index : classe du controller index
 */

class index extends _controller {

    /**
     * Attributs
     */

    // Nom du controller
    protected $name = "Index";
    // Liste des objets manipulés par le controller
    protected $objects = []; // ["objet1" => ["action"1,"action2"...],"objet2" => ["action"1,"action2"...]]
    // Paramètres du controller attendus en entrée
    protected $paramEntree = []; // ["nom_param1"=>["method"=>"POST","required"=>true],"nom_param2"=>["method"=>"POST","required"=>false]]
    // Paramètres du controller
    protected $parametres = [];
    // Type de retour
    protected $typeRetour = "template"; // json, fragment ou template (défaut)
    // Nom du template
    protected $template = "";
    // Retour du controller
    protected $retour = "";
    // Paramètres en sortie du controller
    protected $paramSortie = []; // ["nom_param1"=>["required"=>true],"nom_param2"=>["required"=>false]]
    // Besoin d'être connecté
    protected $connected = false; // True par défaut

    /**
     * Vérifie que les paramètres du controller sont bien présents et/ou leur cohérence
     *
     * @return boolean True si tout s'est bien passé, False si une erreur est survenu
     */
    function verifParams(){
        //Fonction à surchargée dans la classe fille
        return true;
    }

    /**
     * Exécution du rôle du controller
     *
     * @return boolean True si tout s'est bien passé, False si une erreur est survenu
     */
    function execute(){
        //Fonction à surchargée dans la classe fille
        $objUser = new utilisateur();

        var_dump($objUser);
        
        // Code à executer après les traitements du controller si on est en template ou fragment
        $objTemplate = new _template("index");
        $objTemplate->getHtmlContent("pages");

        return true;
    }

}