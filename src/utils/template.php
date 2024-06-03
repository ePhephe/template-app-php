<?php

/**
 * Classe _template : classe générique des objets des template
 */

class _template {

    /**
     * Attributs
     */

    // Nom du template
    protected $name = "";
    // Paramètres attendus du template
    protected $parametres = [];
    // Objet de la session
    protected $session;
    // Objet des permissions
    protected $permission;

    /**
     * Méthodes
     */

     /**
     * Constructeur de l'objet
     *
     * @param array $parametres - Tableau des paramètres passées au template
     * @return void
     */
    function __construct($name = "",$parametres = []) {
        // On récupère l'objet de la session
        $this->session = _session::getSession();
        // On récupère l'objet des permissions de l'application
        $this->permission = _permission::getPermission();
        // On affecte le nom du template
        $this->name = $name;
        // On affecte les paramètres fournis
        $this->parametres = $parametres;
    }

    /**
     * Getters
     */
    
    /**
     * Retourne la valeur pour l'attribut passé en paramètre
     *
     * @param  string $name - Nom de l'attribut
     * @return mixed Valeur de l'attribut
     */
    function get($name){
        return $this->$name;
    }

    /**
     * S'execute lorsque l'on utilise $obj->name
     * Permet de retourner la valeur d'un attribut
     *
     * @param  string $name Attribut concerné
     * @return mixed Valeur de l'attribut $name
     */
    function __get($name){
        return $this->$name;
    }
    
    /**
     * Affiche la balise html HEAD
     *
     * @param  string $title Titre à donner à la page
     * @param  string $metadescription Metadescription de la page
     * @param  string $lang Langue de la page
     * @return void
     */
    function getHtmlHead($title = "", $metadescription = "", $lang = "fr"){
        // On inclut le fragment correspondant
        include_once("src/templates/fragments/head.php");
    }
    
    /**
     * Affiche la navigation de la page (header + nav)
     *
     * @return void
     */
    function getHtmlNav(){
        // On récupère la session et la permission pour construire la navigation
        $session = $this->session;
        $permission = $this->permission;
        // On inclut le fichier correspondant
        include_once("src/templates/fragments/nav.php");
    }

    
    /**
     * Affiche le contenu de la page
     *
     * @param  string $type Type de l'élément à affiche template ou fragment
     * @param  array $head Informations à mettre dans la balise HEAD
     * @param  boolean $is_nav Si on veut afficher la navigation
     * @param  boolean $is_footer Si on veut afficher le footer
     * @return void
     */
    function getHtmlContent($type, $head = [], $is_nav = true, $is_footer = true){
        // On vérifie que le nom du template ne soit pas vide
        if(!empty($this->name)) {
            // On récupère les paramètres dans une variable paramètre
            $parametres = $this->parametres;
            // Si on est dans un template, on ajoute le head
            if($type === "pages") {
                if(!empty($head)) $this->getHtmlHead($head["title"],$head["metadescription"],$head["lang"]);
                else $this->getHtmlHead();
            }
            // Si on est dans un template et qu'on veut la barre de navigation
            if($type === "pages" && $is_nav === true) {
                $this->getHtmlNav();
            }

            // On inclut notre template ou fragment
            include_once("src/templates/" . $type . "/" . $this->name . ".php");
            
            // Si on est dans un template et qu'on veut le footer
            if($type === "pages" && $is_footer === true) {
                $this->getHtmlFooter();
            }
        }
    }
    
    /**
     * Affiche le footer de la page
     *
     * @return void
     */
    function getHtmlFooter(){
        // On récupère la session et la permission pour construire la navigation
        $session = $this->session;
        $permission = $this->permission;
        include_once("src/templates/fragments/footer.php");
    }
}