<?php

/**
 * Classe de gestion des sessions
 */

class _session {

    protected $tableUser = "utilisateur";
    protected $objUser;
    protected $id = 0;
    protected $urlRedirect = "index";

    protected static $objSession; //Objet unique sur la classe session

    //Méthode pour travailler avec une instance unique sur cette classe
    public static function getSession(){
        if(empty(static::$objSession))
            static::$objSession = new _session();

        return static::$objSession;
    }

    private function __construct(){
        session_start();
        //Vérifier si l'utilisateur est connecté et initialise les valeurs
        if($this->isConnected()){
            $this->id = $_SESSION["id"];
            $this->objUser = new $this->tableUser($_SESSION["id"]);
        }
    }
    
    /**
     * Retourne le nom de la table des utilisateurs
     *
     * @return string Nom de la table des utilisateurs
     */
    function getTableUser(){
        return $this->tableUser;
    }


    /**
     * Retourne s'il y a une connexion active ou non
     *
     * @return boolean - True si l'utilisateur est connecté sinon false
     */
    function isConnected(){
        return ! empty($_SESSION["id"]);
    }


    /**
     * Retourne l'id de l'utilisateur connecté
     *
     * @return integer - Retour l'id de l'utilisateur ou 0 s'il n'y a pas de connexion active
     */
    function id(){
        return $this->id;
    }

    /**
     * Redirige l'utilisateuur vers l'URL défini dans l'objet
     *
     * @param string $raison Raison de la redirection
     * @return void
     */
    function redirect($raison = ""){
        header("Location: " . $this->urlRedirect . "?redirect=" . $raison);
        exit();
    }

    /**
     * Retourne l'objet correspondant à l'utilisateur connecté ou un objet vide si aucun utilisateur n'est connecté
     *
     * @return mixed - Objet de la classe qui gère les utilisateurs de l'application
     */
    function userConnected(){
        //Si on est connecté on retourne l'objet de l'utilisateur courant
        if($this->isConnected()){
            return $this->objUser;
        }
        //Sinon on retourne un objet utilisateur vide
        else {
            return new $this->tableUser();
        }
    }

    /**
     * Gère la déconnexion de l'application
     *
     * @return boolean True si la déconnexion s'est bien déroulée sinon False
     */
    function deconnect(){
        //On passe l'id stocké en session à 0
        $_SESSION["id"]= 0;
        //On remet à zéro les attributs de l'objet
        $this->id = 0;
        $this->objUser = new $this->tableUser();

        return true;
    }

    /**
     * Gère la connexion de l'application
     *
     * @return boolean True si la déconnexion s'est bien déroulée sinon False
     */
    function connect($id){
        //On affecte la valeur à l'id stocké en session
        $_SESSION["id"] = $id;
        //On met les attributs de l'objet session à la valeur correspondante
        $this->id = $id;
        $this->objUser = new $this->tableUser($id);

        return true;
    }
}