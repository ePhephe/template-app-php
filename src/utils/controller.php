<?php

/**
 * Classe _controller : classe générique des objets des controllers
 */

class _controller {

    /**
     * Attributs
     */

    // Nom du controller
    protected $name = "";
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
    protected $connected = true; // True par défaut
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
     * @param array $parametres - Tableau des paramètres passées au controller
     * @return void
     */
    function __construct($parametres = []) {
        // On récupère l'objet de la session
        $this->session = _session::getSession();
        // On récupère l'objet des permissions de l'application
        $this->permission = _permission::getPermission();
        // On récupère les paramètres
        $this->parametres = $parametres;

        if($this->get("connected") === true) {
            // On lance la vérification de la session
            $this->verifSession();
            // On lance la vérification des permissions
            if(!$this->verifPermissions()){
                if($this->typeRetour === "template"){ 
                    $this->permission->redirect("non-autorised");
                }
            }
        }
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
        /*
        // Code à executer après les traitements du controller si on est en template ou fragment
        $objTemplate = new _template();
        $objTemplate->getHtmlContent("template");

        // Code à executer après les traitements du controller si on est en json
        echo json_encode($this->retour);
        */

        return true;
    }

    /**
     * Vérifie si on a bien une session en cours, pour les controllers nécessitant d'être connecté
     *
     * @return boolean True si tout est OK sinon False
     */
    function makeRetour($succes,$raison,$message){
        // On construit le retour dans un tableau
        $retour["succes"] = $succes;
        $retour["raison"] = $raison;
        $retour["message"] = $message;
        
        $this->retour = $retour;

        return true;
    }
    
    /**
     * Vérifie si on a bien une session en cours, pour les controllers nécessitant d'être connecté
     *
     * @return boolean True si tout est OK sinon False
     */
    function verifSession(){
        // On vérifie que la session est connecté
        if( ! $this->session->isConnected() && $this->connected === true) {
            // Cas pour un controller en retour json
            if($this->typeRetour === "json" || $this->typeRetour === "fragment"){ 
                // On construit le retour
                $this->makeRetour(false,"deconnect","Vous n'êtes pas connecté !");
                return false;
            }
            else {
                //Cas pour un controller d'affichage
                $this->session->redirect("notconnected");
            }
        }

        return true;
    }
    
    /**
     * Vérifie que l'utilisateur à les permissions nécessaires sur les objets à manipuler
     *
     * @return boolean True si tout est OK sinon False
     */
    function verifPermissions(){
        // On parcourt les objets nécessaires au déroulement du controller
        foreach ($this->objects as $nomObjet => $listActions) {
            // Pour chaque objet, on parcourt les actions
            foreach ($listActions as $nomAction) {
                // On appelle la fonction de vérification de la permission
                if($this->permission->verifPermission($nomObjet,$nomAction)) {
                    // On instancie un objet
                    $objet = new $nomObjet ();
                    // On récupère le partitionnement pour cette permission
                    $partitionnement = $this->permission->getPartitionnement($nomObjet,$nomAction);
                    if (isset($this->params[$objet->champ_id()]) && $partitionnement===true) {
                        $objet->load($this->params[$objet->champ_id()]);
                        if(!$objet->verifPartitionnement()){
                            $this->makeRetour(false,"non-autorised","Vous n'êtes pas autorisé !");
                            return false;
                        }
                    }
                }
                else {
                    $this->makeRetour(false,"non-autorised","Vous n'êtes pas autorisé !");
                    return false;
                }
            }
        }

        return true;
    }
}