<?php

class _field {

    /**
     * Attributs
     */

    // Nom du champ
    protected $table = "";
    // Nom du champ
    protected $name = "";
    // Type du champ
    protected $type = "";
    // Libellé
    protected $libelle = "";
    // Objet lié
    protected $nomObjet = "";
    // Valeur du champ
    protected $value = "";
    // Valeur du champ
    protected $objet = "";
    // Paramètres de l'input
    protected $input = [];
    // Formats du champ
    protected $formats = [];
    // Liste clés <=> valeur
    protected $listCleValeur = [];

    /**
     * Méthodes
     */

    /**
     * Constructeur de l'objet
     *
     * @param  integer $id Identifiant de l'objet à charger
     * @return void
     */
    function __construct() {

    }

    /**
     * Retourne l'information si l'objet est chargé
     *
     * @return boolean True si l'objet est chargé, sinon False
     */
    function is() {
        return ! empty($this->name);      
    }

    /**
     * Setters
     */
    
    /**
     * Définit la valeur d'un champ
     *
     * @param  string $fieldName Nom du champ à modifier
     * @param  mixed $value Nouvelle valeur du champ
     * @return boolean - True si la valeur est acceptée sinon False
     */
    function set($value){

    }

    /**
     * S'execute lorsque l'on utilise $obj->name = valeur
     * Permet de mettre à jour la valeur d'un attribut
     *
     * @param  string $name Attribut concerné
     * @param  mixed $value Valeur de l'attribut concerné
     * @return void
     */
    function __set($name,$value){

    }

    /**
     * Getters
     */
    
    /**
     * Retourne la valeur pour l'attribut passé en paramètre
     *
     * @param  string $fieldName - Nom de l'attribut
     * @return mixed Valeur de l'attribut
     */
    function get($name){

    }

    /**
     * S'execute lorsque l'on utilise $obj->name
     * Permet de retourner la valeur d'un attribut
     *
     * @param  string $name Attribut concerné
     * @return mixed Valeur de l'attribut $name
     */
    function __get($name){

    }
    
    /**
     * Vérifie que le champ est correctement paramétré
     *
     * @return boolean True si tout est ok sinon false
     */
    function verify(){

    }
    
    /**
     * Retourne l'élément de formulaire correspondant au champ
     *
     * @return void
     */
    function getElementFormulaire(){

    }

}