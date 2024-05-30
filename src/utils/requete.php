<?php

/**
 * Classe _requete : classe générique des requêtes
 */

class _requete {

    /**
     * Attributs
     */

    // Champs de la requêtes
    protected $fields;
    // Tables de la requête
    protected $tables; // ["nom_table1" => "objet_table1","nom_table1" => "objet_table1"] /!\ Pour l'instant on ne gère qu'une table pour les INSERT/UPDATE/DELETE
    // Texte de la requête
    protected $requete;
    // Filtres pour la clause WHERE
    protected $filtres;
    // Ordres de tri
    protected $order;
    // Limit du nombre de résultat à appliquer
    protected $limit;
    // Tableau des paramètres pour l'execution
    protected $params;

    // Objet PDO de la requête
    protected $objet;

    //Base de données ouverte
    protected static $bdd;

    /**
     * Méthodes
     */
    
    /**
     * Constructeur de l'objet
     *
     * @return void
     */
    function __construct($fields, $tables, $filtres = [], $order = [], $limit = []) {
        $this->fields = $fields;
        $this->tables = $tables;
        $this->filtres = $filtres;
        $this->order = $order;
        $this->limit = $limit;
    }


    /**
      * Retourne la connexion à la base de données ou crée la connexion si elle n'est pas existante
      *
      * @return object Objet PDO de la base de données
      */
    static function bdd() {
        if(empty(static::$bdd)) {
            static::$bdd = new PDO("mysql:host=localhost;dbname=projets_tickets_mdurand;charset=UTF8","mdurand","ac2dmTM8q?M");;
            static::$bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            return static::$bdd;
        }
        else {
            return static::$bdd;
        }
    }

    function select(){

    }

    function insert(){

    }

    function update(){

    }

    function delete(){

    }

    function execution(){

    }

    /**
     * Construit la partie SELECT de la requête
     *
     * @return string - Texte du SELECT de la requête
     */
    function makeSelect() {
        // On initialise un tableau vide
        $arrayResultat = [];

        // On parcourt tous les champs qui ont été fournis
        foreach ($this->fields as $field) {
            $arrayResultat[] = "`".$field->get("name")."`";
        }

        // On parcourt toutes les tables pour récupérer le champ id de chacune
        foreach($this->tables as $table) {
            // On a le nom du champ dans $nomchamp
            $arrayResultat[] = "`".$table->champ_id()."`";
        }

        return "SELECT " . implode(",",$arrayResultat);
    }

    /**
     * Construit la partie SET de la requête d'INSERT ou d'UPDATE
     *
     * @return string - Texte du SET de la requête
     */
    function makeSet() {
        // On initialise le tableau à vide
        $arrayResultat = [];

        //On parcourt les tables, on s'arrête sur la première
        foreach($this->tables as $table) {
            //On parcourt les champs de la table
            foreach ($table->getFields() as $field) {
                // On construit la clé du paramètre
                $arrayResultat[] = "`" . $field->get("name") . "` = :" . $field->get("name");         
                // On appelle la fonction pour valoriser le paramètre
                $this->makeParamForSet($field);
            }
        }

        return implode(",",$arrayResultat);
    }

    /**
     * Retourne un tableau de paramètres à passer en pramètre d'un requête INSERT/UPDATE
     *
     * @param object $field Objet du champ à mettre dans le tableau des paramètres
     * @return array - Tableau des paramètres de la requête au format ":champ" => valeurchamp
     */
    function makeParamForSet($field) {
        // On construit la clé du paramètre
        $strCle = ":" . $field->get("name");          
        // Si le champ à une valeur, on la stocke dans le tableau des paramètres
        if ($field->get("value") != "") {
            $arrayResult[$strCle] = $field->get("value");
        } 
        // Sinon la valeur du paramètre est définie à null
        else {
            $arrayResult[$strCle] = null;
        }

        // On stocke les paramètres dans le tableau correspondant de l'objet de la requête
        $this->params[] = $arrayResult;
    }

}