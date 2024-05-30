<?php

/**
 * Classe _model : classe générique des objets du modèle de données
 */

class _model {

    /**
     * Attributs
     */

    //Nom de la table dans la BDD
     protected $table = "";
     //Clé de la table
     protected $champ_id = "";
     //Lien avec les autres tables
     protected $links = [];
     //Liste des champs
     // Chaque champ doit être sous la forme ["nom_champ" => ["type"=>"type_champ","libelle"=>"libelle_champ",...]]
     protected $fields = [];
    /**
     * "type"=>"text" - Type du champ
     * "type_input"=>"text" - Type du champ en HTML
     * "nom_objet" => "produit" - Nom de la classe de l'objet lié (dans le cas d'un type object)
     * "libelle"=>"Numéro de série" - Libellé à afficher du champ
     * "unique" => "N" - Est-ce que la valeur du champ doit être unique ?
     * "max" => 100 - Valeur maximale possible
     * "min" => 10 - Valeur minimale possible
     * "max_length" => 15 - Longueur maximale du champ
     * "min_length" => 15 - Longueur minimale du champ
     * "case" => "UPPER" - Casse à respecter pour le champ UPPER (Majuscules) LOWER (Minuscules) sinon n'existe pas
     * "format" => "d/m/Y H:i:s" - Format à respecter (date, regex... )
     * "liste_cle_valeur" => [ "cle" => "valeur"] - Liste des valeurs à afficher pour les clés du champs définis
     */

    //Nom des controllers d'action sur l'objet
    protected $actions = [];
    
    //Identifiant de l'objet
    protected $id = 0;
    //Valeurs des champs
    protected $values = [];
    
    //Base de données ouverte
    protected static $bdd;


    /**
     * Constructeur
     */
    
    /**
     * Constructeur de l'objet
     *
     * @param  integer $id Identifiant de l'objet à charger
     * @return void
     */
    function __construct($id = null) {
        // Si l'identifiant n'est pas null
        if ( ! is_null($id) && $id != 0) {
            //On charge l'objet
            $this->load($id);
        }
    }

    /**
     * Méthodes
     */
    
    /**
     * Retourne l'information si l'objet est chargé
     *
     * @return boolean True si l'objet est chargé, sinon False
     */
    function is() {
        return ! empty($this->id);      
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
    function get($fieldName) {
        //On vérifie si une méthode get_fieldname existe dans la classe, dans ce cas on l'appelle
        if(method_exists($this,"get_$fieldName"))
            return call_user_func([$this,"get_$fieldName"]);

        // Si la valeur existe (isset(....)) retourne la valeur sinon on retourne une valeur par défaut en fonction du type du champ
        if (isset($this->values[$fieldName])) {
            //On regarde si le type du champ est un objet(lien)
            if($this->fields[$fieldName]["type"] === "object") {
                //On vérifie si on a stocké un objet pour ce champ dans le tableau values avec la clé name_object
                if(!isset($this->values[$fieldName."_object"])) {
                    //Si name_object n'existe pas, on créé l'objet et on le stocke à cet emplacement
                    $obj = new $this->fields[$fieldName]["nom_objet"]();
                    $obj->load($this->values[$fieldName]);
                    $this->values[$fieldName."_object"] = $obj;
                }

                return $this->values[$fieldName."_object"];
            }
            else {
                return htmlentities($this->values[$fieldName]);
            }
        } else {
            switch ($this->fields[$fieldName]["type"]) {
                case 'text':
                    return "";
                case 'number':
                    return 0;
                case 'object':
                    return new $this->fields[$fieldName]["nom_objet"]();
                default:
                    return "";
            }
        }
    }

    /**
     * Retourne la valeur affichable pour l'attribut passé en paramètre dnas le cas d'une liste clé valeur
     *
     * @param  string $fieldName - Nom de l'attribut
     * @return mixed Valeur de l'attribut
     */
    function getLibelle($fieldName) {
        if(isSet($this->fields[$fieldName]["liste_cle_valeur"]))
            return $this->fields[$fieldName]["liste_cle_valeur"][$this->values[$fieldName]];
        else
            return $this->values[$fieldName];
    }

    /**
     * Retourne la valeur pour tous les attributs sous forme d'un tableau
     *
     * @return array Ensemble des champs dans un tableau associatif
     */
    function getToTab() {
        //Initialisation du tableau
        $arrayFields = [];
        //On parcourt tous les champs
        foreach ($this->fields as $cle => $champ) {
            if($this->fields[$cle]["type"] === "object") {
                //On vérifie si on a stocké un objet pour ce champ dans le tableau values avec la clé name_object
                if(!isset($this->values[$cle."_object"])) {
                    //Si name_object n'existe pas, on créé l'objet et on le stocke à cet emplacement
                    $obj = new $this->fields[$cle]["nom_objet"]();
                    $obj->load($this->values[$cle]);
                }
                else {
                    $obj = $this->values[$cle."_object"];
                }

                $arrayFields[$cle] = $this->values[$cle];
                $arrayFields[$cle."_object"] = $obj->getToTab();
            }
            else {
                if(isSet($this->fields[$cle]["liste_cle_valeur"])) {
                    $arrayFields[$cle] = $this->values[$cle];
                    $arrayFields[$cle."_libelle"] = $this->fields[$cle]["liste_cle_valeur"][$this->values[$cle]];
                }
                else {
                    $arrayFields[$cle] = htmlentities($this->values[$cle]);
                }
            }
        }
        $arrayFields["id"] = $this->id();

        return $arrayFields;
    }
    
    /**
     * S'execute lorsque l'on utilise $obj->name
     * Permet de retourner la valeur d'un attribut
     *
     * @param  string $name Attribut concerné
     * @return mixed Valeur de l'attribut $name
     */
    function __get($name){
        if(array_key_exists($name,$this->fields)){
            return $this->values[$name];
        }
        else if($name === "id") {
            return $this->id;
        }
    }
    
    /**
     * Retourne l'identifiant de l'objet courant
     *
     * @return integer - Identifiant de l'objet courant
     */
    function id() {
        return $this->id;
    }

    /**
     * Retourne le champ identifiant de l'objet courant
     *
     * @return integer - Champ identifiant de l'objet courant
     */
    function champ_id() {
        return $this->champ_id;
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
    function set($fieldName, $value) {
        //On vérifie si une méthode set_fieldname existe dans la classe, dans ce cas on l'appelle
        if(method_exists($this,"set_$fieldName"))
            return call_user_func([$this,"set_$fieldName"],$value);

        if(array_key_exists("max",$this->fields[$fieldName])) {
            if($value > $this->fields[$fieldName]["max"])                
                return false;
        }

        if(array_key_exists("min",$this->fields[$fieldName])) {
            if($value < $this->fields[$fieldName]["min"])
                return false;
        }

        if(array_key_exists("unique",$this->fields[$fieldName])) {
            if($this->fields[$fieldName]["unique"] === "O"){
                if(!$this->verifUnicite($fieldName, $value)) {
                    return false;
                }
            }
        }

        if(array_key_exists("format",$this->fields[$fieldName])) {
            if(!preg_match($this->fields[$fieldName]["format"],$value)){
                return false;
            }
        }

        if(array_key_exists("password",$this->fields[$fieldName])) {
            $value = password_hash($value,PASSWORD_BCRYPT);
        }

        if(array_key_exists("text",$this->fields[$fieldName])) {
            $value = html_entity_decode($value);
        }
        
        $this->values[$fieldName] = $value;

        if($this->fields[$fieldName]["type"] === "object") {
            // On instancie un nouvel objet à jour
            $obj = new $this->fields[$fieldName]["nom_objet"]();
            $obj->load($value);
            $this->values[$fieldName."_object"] = $obj;
        }

        return true;
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
        if(array_key_exists($name,$this->fields)){
            $this->values[$name] = $value;
        }
    }
    
    /**
     * Charge l'objet à partir d'un tableau
     *
     * @param  array $data Informations à charger dans l'objet
     * @return boolean - True si le chargement s'est bien passé sinon False
     */
    function loadFromTab($data) {
        //On parcourt tous les champs
        foreach($this->fields as $fieldName => $field){
            //Pour chaque champ on indique la valeur dans l'attribut values
            $this->values[$fieldName] = $data[$fieldName];

            if($field["type"] === "object") {
                //On vérifie si on a stocké un objet pour ce champ dans le tableau values avec la clé name_object
                //Si name_object n'existe pas, on créé l'objet et on le stocke à cet emplacement
                $objNew = new $field["nom_objet"]();
                $objNew->load($this->values[$fieldName]);
                $this->values[$fieldName."_object"] = $objNew;
            }
        }

        //Puis on enregistre l'id dans son attribut dédié
        $this->id = $data[$this->champ_id];

        return true;
    }
    
    /**
     * Vérifie que le champ est unique dans la base de données
     *
     * @param  string $champ Nom du champ à tester
     * @param  mixed $valeur Valeur du champ à tester
     * @return boolean True si la valeur est bien unique, False si elle est déjà utilisé
     */
    function verifUnicite($champ,$valeur){
         //On construit la requête SELECT
         $strRequete = "SELECT `$champ` FROM `$this->table` WHERE `$champ` = :valeur ";
         $arrayParam = [
             ":valeur" => $valeur
         ];
 
         //On prépare la requête
         $bdd = static::bdd();
         $objRequete = $bdd->prepare($strRequete);
 
         //On exécute la requête avec les parmaètres
         if ( ! $objRequete->execute($arrayParam)) {
             return false;
         }
 
         //On récupère les résultats
         $arrayResultats = $objRequete->fetchAll(PDO::FETCH_ASSOC);
         //Si le tableau est vide, on retourne une erreur (false)
         if(empty($arrayResultats)) {
             return true;
         }
    }

    /**
     * Méthodes de gestion avec la BDD
     */
     
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
    
    /**
     * Charge un objet à partir d'un identifiant
     *
     * @param  integer $id Identifiant de l'objet à charger
     * @return boolean - True si le chargement s'est bien passé sinon False
     */
    function load($id) {
        //On débute la requête avec le SELECT
        $strRequete = "SELECT " ;

        //On génère un tableau composés des noms des champs encadrés par ` ` 
        $arrayFields = [];
        foreach($this->fields as $fieldName => $field) {
            $arrayFields[] = "`$fieldName`";
        }
        $strRequete .= implode(", ", $arrayFields);
        
        //On construit le FROM avec le nom de la table
        $strRequete .= " FROM `$this->table` ";

        //On construit le WHERE avec l'id que l'on passe en tableau de paramètre
        $strRequete .= " WHERE `$this->champ_id` = :id";
        $arrayParam = [ ":id" => $id];

        //On prépare la requête
        $bdd = static::bdd();
        $objRequete = $bdd->prepare($strRequete);

        //On execute la requête avec ses paramètres
        if ( ! $objRequete->execute($arrayParam)) {
            // On a une erreur de requête (on peut afficher des messages en phase de debug)
            return false;
        }

        //On récupère les résultats
        $arrayResultats = $objRequete->fetchAll(PDO::FETCH_ASSOC);
        //Si le tableau est vide, on retourne une erreur (false)
        if (empty($arrayResultats)) {
            return false;
        }

        //On récupère la ligne de résultat dans une variable
        $arrayInfos = $arrayResultats[0];

        // Pour chaque champ de l'objet, on valorise $this->values[champ];
        foreach($this->fields as $fieldName => $field) {
            $this->values[$fieldName] = $arrayInfos[$fieldName];
        }

        // On renseigne l'id :
        $this->id = $id;

        return true;
    }
    
    /**
     * Vérifie la cohérence des données de l'objet
     *
     * @return boolean True si tout est OK sinon False
     */
    function verify(){
        /* A surchargé dans la classe fille si on veut l'utilisé */
        return true;
    }
    
    /**
     * Insertion de l'objet dans la base de données
     *
     * @return boolean - True si le chargement s'est bien passé sinon False
     */
    function insert() {
        // On vérifie que l'objet est cohérent avant de l'insérer
        if($this->verify() === false)
            return false;

        //On construit la requête INSERT
        $strRequete = "INSERT INTO `$this->table` SET " . $this->makeRequestSet();
        $arrayParam  = $this->makeRequestParamForSet();

        //On prépare la requête
        $bdd = static::bdd();
        $objRequete = $bdd->prepare($strRequete);

        //On execute la requête et on gère les erreurs
        if ( ! $objRequete->execute($arrayParam)) {
            // Erreur sur la requête
            return false;
        }

        //On récupère l'identifiant qui a été créé par l'INSERT
        $this->id = $bdd->lastInsertId();

        return true;
    }

    
    /**
     * Mise à jour de l'objet dans la base de données
     *
     * @return boolean - True si le chargement s'est bien passé sinon False
     */
    function update() {
        // On vérifie que l'objet est cohérent avant de l'insérer
        if($this->verify() === false)
            return false;
        
        //On construit la requête d'UPDATE
        $strRequete = "UPDATE  `$this->table` SET " . $this->makeRequestSet() . " WHERE `$this->champ_id` = :id ";
        $arrayParam = $this->makeRequestParamForSet();
        $arrayParam[":id"] = $this->id;
           

        //On prépare la requête
        $bdd = static::bdd();
        $objRequete = $bdd->prepare($strRequete);

        //On exécute la requête et on gère les erreurs
        if ( ! $objRequete->execute($arrayParam)) {
            // Erreur sur la requête
            return false;
        }

        return true;
    }

    /**
     * Suppression de l'objet dans la base de données
     *
     * @return boolean - True si le chargement s'est bien passé sinon False
     */
    function delete() {
        //On construit la requête du DELETE
        $strRequete = "DELETE FROM `$this->table` WHERE `$this->champ_id` = :id";
        $arrayParam = [":id" => $this->id];
    
        //On prépare la requête
        $bdd = static::bdd();
        $req = $bdd->prepare($strRequete);

        //On exécute la requête avec les parmaètres
        if ( ! $req->execute($arrayParam)) {
            //Erreur sur la requête
            return false;
        }

        //On remet l'id de l'objet à 0
        $this->id = 0;

        return true;
    }
    
    /**
     * Liste tous les éléments de la base de données
     *
     * @param  array $arrayCriteresTri Tableau des critère de tri (facultatif)
     * @return mixed - Tableau d'objets indexé sur l'id, s'il y a une erreur False
     */
    function listAll($arrayCriteresTri = []) {
        //On construit la requête SELECT
        $arrayFields = [];
        // Pour chaque champ, on ajoute un elt `nomChamp` dans le tableau
        foreach ($this->fields as $fieldName => $field) {
            $arrayFields[] = "`$fieldName`";
        }

        $strRequete = "SELECT `$this->champ_id`, " . implode(",", $arrayFields) . " FROM `$this->table` ";
        
        //Si des crtières de tri sont présents
        $arrayTri = [];
        $strRequete .= "ORDER BY ";
        if(!empty($arrayCriteresTri)) {
            $arrayTri = [];
            foreach ($arrayCriteresTri as $critere => $sens) {
                if(array_key_exists($critere,$this->fields))
                    $arrayTri[] = "$critere $sens";
            }
        }
        $arrayTri[] = "`$this->champ_id` desc";
        $strRequete .= implode(",",$arrayTri). " ";

        //On prépare la requête SQL
        $bdd = static::bdd();
        $objRequete = $bdd->prepare($strRequete);

        //On exécute la requête et on gère les erreurs
        if ( ! $objRequete->execute()) { 
            return false;
        }

        //On récupère les enregistrements et on gère les erreurs si le tableau est vide
        $arrayResultats = $objRequete->fetchAll(PDO::FETCH_ASSOC);
        if (empty($arrayResultats)) {
            return false;
        }

        //On construit le tableau à retourner
        $arrayObjResultat = [];
        foreach ($arrayResultats as $unResultat) {
            $newObj = new $this->table();
            $newObj->loadFromTab($unResultat);
            $arrayObjResultat[$unResultat["id"]] = $newObj;
        }
  
        return $arrayObjResultat;
    }
    
    /**
     * Retourne un tableau d'objet selon les critères fournis
     *
     * @param  array $arrayFiltres Tableau des critères de filtre au format [["champ"=>"nom","valeur"=>"test","operateur"=>"LIKE"]] (facultatif)
     * @param  boolean $partitionnement - True si un partitionement doit être appliqué sinon False (facultatif)
     * @param  array $arrayCriteresTri Tableau des crtières de tri au format [["champ" => "sens"]] (facultatif)
     * @param  array $arrayLimit Tableau des crtières pagination ["limit" => 10,"offset" => 0] (facultatif)
     * @return mixed - Tableau d'objets indexé sur l'id, s'il y a une erreur False
     */
    function list($arrayFiltres = [], $partitionement = false,$arrayCriteresTri = [], $arrayLimit = []) {
        //On construit la requête SELECT
        $arrayFields = [];
        $arrayParam = [];
        foreach ($this->fields as $fieldName => $field) {
            $arrayFields[] = "`$fieldName`";
        }
        $strRequeteSelect = "SELECT `$this->champ_id`, " . implode(",", $arrayFields) . " ";

        //On initialise un tableau pour la FROM
        $arrayFROM[$this->table] = "`" . $this->table . "`";

        //On initialise la clause WHERE
        $strRequeteWhere = "";
        $arrayReqFiltres = [];
        //Si des crtières de filtre sont présents
        if(!empty($arrayFiltres)) {
            foreach ($arrayFiltres as $index => $filtre) {
                //Si on est sur un filtre qui est dans une autre table
                if(array_key_exists("lien",$filtre)){
                    //On instancie un objet du lien
                    $objFiltre = new $filtre["lien"]();
                    //On ajoute le lien au FORM
                    if(!array_key_exists($filtre["lien"],$arrayFROM)) {
                        $arrayFROM[$filtre["lien"]] = "LEFT JOIN `".$filtre["lien"]."` ON `".$objFiltre->champ_id()."` = `".$this->links[$filtre["lien"]]."`";
                        //On ajoute les champs du lien au SELECT
                        $fieldsLien = $objFiltre->makeTableauSimpleSelect();
                        $strRequeteSelect .= ", " . implode(",",$fieldsLien) . " ";
                    }
                    //On ajoute le filtre
                    $arrayReqFiltres[] = $objFiltre->makeFiltre($filtre,$index);
                    //On ajoute la valeur du filtre au tableau des paramètres
                    $arrayParam[":".$filtre["champ"].$index] = $objFiltre->makeFiltreValeur($filtre,$index);
                }
                //Si on a pas de nom de champ dans un filtre, c'est un bloc de filtre OR
                else if(!array_key_exists("champ",$filtre)){
                    $arrayOrFiltres = [];
                    foreach ($filtre as $key => $unFiltre) {
                        if(array_key_exists("lien",$unFiltre)){
                            //On instancie un objet du lien
                            $objFiltre = new $unFiltre["lien"]();
                            //On ajoute le lien au FORM
                            if(!array_key_exists($unFiltre["lien"],$arrayFROM)){
                                $arrayFROM[$unFiltre["lien"]] = "LEFT JOIN `".$unFiltre["lien"]."` ON `".$objFiltre->champ_id()."` = `".$this->links[$unFiltre["lien"]]."`";
                                //On ajoute les champs du lien au SELECT
                                $fieldsLien = $objFiltre->makeTableauSimpleSelect();
                                $strRequeteSelect .= ", " . implode(",",$fieldsLien) . " ";
                            }
                            //On ajoute le filtre
                            $arrayOrFiltres[] = $objFiltre->makeFiltre($unFiltre,$index);
                            //On ajoute la valeur du filtre au tableau des paramètres
                            $arrayParam[":".$unFiltre["champ"].$index] = $objFiltre->makeFiltreValeur($unFiltre,$index);
                        }
                        else if(array_key_exists($unFiltre["champ"],$this->fields) || $unFiltre["champ"]===$this->champ_id) {
                            //On ajoute le filtre
                            $arrayOrFiltres[] = $this->makeFiltre($unFiltre,$index);
                            //On ajoute la valeur du filtre au tableau des paramètres
                            $arrayParam[":".$unFiltre["champ"].$index] = $this->makeFiltreValeur($unFiltre,$index);
                        }
                    }
                    $arrayReqFiltres[] = " (". implode(" OR ", $arrayOrFiltres) . ") ";
                }
                //Sinon on vérifie que le champ est bien dans notre table
                else {
                    if(array_key_exists($filtre["champ"],$this->fields) || $filtre["champ"]===$this->champ_id) {
                        //On ajoute le filtre
                        $arrayReqFiltres[] = $this->makeFiltre($filtre,$index);
                        //On ajoute la valeur du filtre au tableau des paramètres
                        $arrayParam[":".$filtre["champ"].$index] = $this->makeFiltreValeur($filtre,$index);
                    }
                }
            }
        }

        //Gestion du FROM
        $strRequeteFrom = "FROM " . implode(" ",$arrayFROM);

        //Si on a du partitionnement de données
        if($partitionement === true){
            $arrayReqFiltres[] =  $this->setFiltrePartitionnement();
        }

        if(!empty($arrayReqFiltres))
            $strRequeteWhere .= "WHERE ". implode(" AND ", $arrayReqFiltres) . " ";

        $strRequete = $strRequeteSelect . $strRequeteFrom . $strRequeteWhere;

        //Si des crtières de tri sont présents
        $arrayTri = [];
        $strRequete .= "ORDER BY ";
        if(!empty($arrayCriteresTri)) {
            $arrayTri = [];
            foreach ($arrayCriteresTri as $critere => $sens) {
                if(array_key_exists($critere,$this->fields))
                    $arrayTri[] = "`$critere` $sens";
            }
        }
        $arrayTri[] = "`$this->champ_id` desc";
        $strRequete .= implode(",",$arrayTri). " ";

        //Si un critère de pagination est présent
        if(!empty($arrayLimit)) {
            $strRequete .= "LIMIT ".$arrayLimit["offset"].", ".$arrayLimit["limit"];
        }

        //On prépare la requête
        $bdd = static::bdd();
        $req = $bdd->prepare($strRequete);

        //On exécute la requête avec ses paramètres et on gère les erreurs
        if ( ! $req->execute($arrayParam)) { 
            var_dump($strRequete);
            var_dump($arrayParam);
            return false;
        }

        //On récupère les résultats et on gère les erreurs
        $arrayResultats = $req->fetchAll(PDO::FETCH_ASSOC);
        /* Un résultat vide est un résultat, je met en commentaire
        if (empty($arrayResultats)) {
            return false;
        }
        */

        // construire le tableau à retourner :
        // Pour chaque élément de $liste, fabriquer un objet contact que l'on met dans le tableau final
        $arrayObjResultat = [];
        foreach ($arrayResultats as $unResultat) {
            $newObj = new $this->table();
            $newObj->loadFromTab($unResultat);

            $arrayObjResultat[$unResultat[$this->champ_id]] = $newObj;
        }
  
        return $arrayObjResultat;
    }
    
    /**
     * Génère la partie SET d'une requête INSERT/UPDATE
     *
     * @return string - Chaîne de caractères de la partie SET de la requête
     */
    function makeRequestSet() {
        //On va chercher le tableau des champs
        $tableau = $this->makeTableauSimpleSet();

        // Générer le texte final grâce à implode
        return implode(", ", $tableau);
    }
    
    /**
     * Construit la clause du filtre pour la clause WHERE
     *
     * @param  array $unFiltre Tableau du filtre concerné
     * @param  integer $index Index du filtre dans la requête
     * @return string - Chaîne de caractère correspondant au filtre
     */
    function makeFiltre($unFiltre,$index) {
        if($unFiltre["champ"] === $this->champ_id()){
            return "`" . $unFiltre["champ"] . "` " . $unFiltre["operateur"] . " :".$unFiltre["champ"].$index;
        }
        else if($this->fields[$unFiltre["champ"]]["type"] === "text") {
            return "UPPER(".$unFiltre["champ"].") " . $unFiltre["operateur"] . " :".$unFiltre["champ"].$index;
        }
        else {
            return "`" . $unFiltre["champ"] . "` " . $unFiltre["operateur"] . " :".$unFiltre["champ"].$index;
        }
    }

    /**
     * Met en forme la valeur pour le filtre
     *
     * @param  array $unFiltre Tableau du filtre concerné
     * @param  integer $index Index du filtre dans la requête
     * @return string - Chaîne de caractère mise en forme
     */
    function makeFiltreValeur($unFiltre,$index) {
        if($unFiltre["champ"] === $this->champ_id()){
            return $unFiltre["valeur"];
        }
        else if($this->fields[$unFiltre["champ"]]["type"] === "text") {
            if($unFiltre["operateur"] === "LIKE"){
                return "%".strtoupper($unFiltre["valeur"])."%";
            }
            else {
                return strtoupper($unFiltre["valeur"]);
            }
        }
        else {
            return $unFiltre["valeur"];
        }
    }

    /**
     * Construit un tableau des champs de l'objet
     *
     * @return array - Tableau des champs
     */
    function makeTableauSimpleSelect() {
        // Faire un tableau : on part d'un tableau vide
        $arrayResultat = [];

        // Pour chaque champ : ajouter dans $result un élément `nomChamp` = :nomChamp
        foreach($this->fields as $fieldName => $field) {
            // On a le nom du champ dans $nomchamp
            $arrayResultat[] = "`$fieldName`";
        }

        $arrayResultat[] = "`$this->champ_id`";

        return $arrayResultat;
    }

    /**
     * Construit un tableau des champs de l'objet au format requête " `champ` = :champ "
     *
     * @return array - Tableau des champs au format requête " `champ` = :champ "
     */
    function makeTableauSimpleSet() {
        // Faire un tableau : on part d'un tableau vide
        $arrayResultat = [];

        // Pour chaque champ : ajouter dans $result un élément `nomChamp` = :nomChamp
        foreach($this->fields as $fieldName => $field) {
            // On a le nom du champ dans $nomchamp
            $arrayResultat[] = "`$fieldName` = :$fieldName";
        }

        return $arrayResultat;
    }
    
    /**
     * Retourne un tableau de paramètre à passer en pramètre d'un requête INSERT/UPDATE
     *
     * @return array - Tableau des paramètres de la requête au format ":champ" => valeurchamp
     */
    function makeRequestParamForSet() {
        //On initialise un tableau vide
        $arrayResult = [];
        
        //On parcourt tous les champs
        foreach($this->fields as $fieldName => $field) {
            $strCle = ":$fieldName";          
            // Valeur : elle est dans le tableau des valeurs, l'attribut values ($this->values)
            // Si on a une valeur pour $nomChamp, on crée l'élément de tableau avec cette valeur,
            // Sinon, on crée avec null
            if (isset($this->values[$fieldName])) {
                $arrayResult[$fieldName] = $this->values[$fieldName];
            } else {
                $arrayResult[$fieldName] = null;
            }
        }

        return $arrayResult;
    }

    /**
     * Méthodes de formulaire
     */

        
    /**
     * Retourne le code HTML de l'input du champ
     *
     * @param  string $champ Champ pour lequel on veut l'input
     * @param  string $readonly Passer la valeur "readonly" pour que le formulaire ne soit pas modifiable
     * @param  array $infosChamp Tableau de paramètres spécifiques pour le champ
     * @return mixed Code HTML sinon false en cas d'erreur
     */
    function getInputFormulaire($champ,$readonly="",$infosChamp=[]) {
        //On vérifie si on a un readonly spécifique pour le champ
        if(isSet($infosChamp["readonly"]))
            $readonly = $infosChamp["readonly"];
        //On initialise le template HTML
        $templateHTML = "";
        if(isSet($this->fields[$champ]["type_input"])){
            //On met en place le label correspondant au champ
            $templateHTML .= '<div id="div_'.$champ.'" class="div_input_form"><label for="'.$champ.'">'.$this->fields[$champ]["libelle"].' : </label>';
            //On prépare la valeur si on en a une
            $valeurChamp = (isSet($this->values[$champ])) ? $this->values[$champ] : "";
            //On recupère le type d'input du champ et on réalise le traitement adéquat
            switch ($this->fields[$champ]["type_input"]) {
                //Si on est sur un select
                case 'select':
                    $templateHTML .= '<select name="'.$champ.'" id="'.$champ.'">';
                    //Si on est en readonly, on disable les options sauf la valeur actuelle
                    if($readonly != "")
                        $templateHTML .= '<option value="" disabled>Choisissez une valeur</option>';
                    else
                        $templateHTML .= '<option value="">Choisissez une valeur</option>';
                    
                    foreach ($this->fields[$champ]["liste_cle_valeur"] as $key => $value) {
                        //On préselectionne par défaut la bonne valeur
                        if(isSet($this->values[$champ])) {
                            $selected = ($this->values[$champ] === strval($key)) ? "selected" : "";
                        }
                        else {
                            $selected = "";
                        }
                        //Si on est en readonly, on disable les options sauf la valeur actuelle
                        if($readonly != "" && $selected != "selected"){
                            $selectReadonly = "disabled";
                        }
                        else {
                            $selectReadonly = "";
                        }

                        if(isSet($infosChamp["autorised_value"])) {
                            if(in_array($key,$infosChamp["autorised_value"]))
                                $templateHTML .= ' <option value="'.$key.'" '.$selected.' '.$selectReadonly.'>'.$value.'</option>';
                        }
                        else {
                            $templateHTML .= ' <option value="'.$key.'" '.$selected.' '.$selectReadonly.'>'.$value.'</option>';
                        }
                        
                    }
                    $templateHTML .= '</select>';
                    break;
                //Si on est sur un textarea
                case 'textarea':
                    $templateHTML .= '<textarea name="'.$champ.'" id="'.$champ.'"';
                    if(isSet($this->fields[$champ]["max_length"])){
                        $templateHTML .= 'maxlenght='.$this->fields[$champ]["max_length"].' ';
                    }
                    if(isSet($this->fields[$champ]["min_length"])){
                        $templateHTML .= 'minlenght='.$this->fields[$champ]["max_length"].' ';
                    }
                    $templateHTML .= $readonly.'>'.$valeurChamp.'</textarea>';
                    break;
                //Si on est sur un checkbox
                case 'checkbox':
                    # code...
                    break;
                //Si on est sur un radiobox
                case 'radiobox':
                    # code...
                    break;
                //Sinon on est sur un input text classique
                default:
                    if($this->fields[$champ]["type_input"] === "password") {
                        //Champ initial
                        $templateHTML .= '<input type="'.$this->fields[$champ]["type_input"].'" name="'.$champ.'" id="'.$champ.'" ';
                        if(isSet($this->fields[$champ]["max_length"])){
                            $templateHTML .= 'maxlenght='.$this->fields[$champ]["max_length"].' ';
                        }
                        if(isSet($this->fields[$champ]["min_length"])){
                            $templateHTML .= 'minlenght='.$this->fields[$champ]["min_length"].' ';
                        }
                        $templateHTML .= $readonly.'>';
                        //Champ de confirmation
                        $templateHTML .= '<label for="'.$champ.'Confirm">Confirmation : </label>';
                        $templateHTML .= '<input type="'.$this->fields[$champ]["type_input"].'" name="'.$champ.'Confirm" id="'.$champ.'Confirm" ';
                        if(isSet($this->fields[$champ]["max_length"])){
                            $templateHTML .= 'maxlenght='.$this->fields[$champ]["max_length"].' ';
                        }
                        if(isSet($this->fields[$champ]["min_length"])){
                            $templateHTML .= 'minlenght='.$this->fields[$champ]["min_length"].' ';
                        }
                        $templateHTML .= $readonly.'>';
                    }
                    else {
                        $templateHTML .= '<input type="'.$this->fields[$champ]["type_input"].'" name="'.$champ.'" id="'.$champ.'" ';
                        if(isSet($this->fields[$champ]["max_length"])){
                            $templateHTML .= 'maxlenght='.$this->fields[$champ]["max_length"].' ';
                        }
                        if(isSet($this->fields[$champ]["min_length"])){
                            $templateHTML .= 'minlenght='.$this->fields[$champ]["min_length"].' ';
                        }
                        if(isSet($this->fields[$champ]["max"])){
                            $templateHTML .= 'max='.$this->fields[$champ]["max"].' ';
                        }
                        if(isSet($this->fields[$champ]["min"])){
                            $templateHTML .= 'min='.$this->fields[$champ]["min"].' ';
                        }
                        $templateHTML .= $readonly.' value="'.$valeurChamp.'">';
                    }
                    break;
            }

            return $templateHTML.'</div>';    
        }

        return $templateHTML;  
    }
    
    /**
     * Renvoi le code HTML du formulaire pour l'objet et l'action demandée
     *
     * @param  string $action Action qui sera derrière le formulaire (create,read,update,delete)
     * @param  boolean $json Traitement du formulaire en ajax (True ou False - Valeur par défaut)
     * @param  array $listInput Liste de champs spécifiques attendus (Tableau vide - Valeur par défaut)
     * @return mixed Code HTML ou false s'il y a une erreur
     */
    function getFormulaire($action,$json=false,$listInput=[]){
        //On initialise le template HTML
        $templateHTML = '';

        //On construit les éléments dépendant l'action
        $paramURL = [];
        switch ($action) {
            case 'create':
                $urlAction = $this->actions[$action].".php";
                $buttonSubmit = '<input type="submit" value="Créer">';
                //Bouton annuler
                $buttonAnnuler = '<input type="button" value="Annuler">';
                $readonly = "";
                break;
            case 'update':
                $urlAction = $this->actions[$action].".php";
                $paramURL["id"] = $this->id();
                $buttonSubmit = '<input type="submit" value="Modifier">';
                //Bouton annuler
                $buttonAnnuler = '<input type="button" value="Annuler">';
                $readonly = "";
                break;
            case 'read':
                $urlAction = $this->actions["list"].".php";
                $paramURL["id"] = $this->id();
                $buttonAnnuler = '';
                $buttonSubmit = '<input type="button" value="Retour à la liste" id="btn-back-list">';
                $readonly = "readonly";
                break;
            case 'delete':
                $urlAction = $this->actions[$action].".php";
                $paramURL["id"] = $this->id();
                $buttonSubmit = '<input type="submit" value="Supprimer">';
                $buttonAnnuler = '<input type="button" value="Retour à la liste" id="btn-back-list">';
                $readonly = "disabled";
                break;
            default:
                $urlAction = "";
                $buttonSubmit = '';
                break;
        }

        //Si on attend un retour JSON
        if($json===true)
            $paramURL["json"] = true;

        //Si on a des paramètres d'URL, on les mets en forme
        if(!empty($paramURL)){
            $urlAction .= "?". http_build_query($paramURL);
        }
            
        //On commence le formulaire
        $templateHTML .= '<form action="'.$urlAction.'" method="post" id="form_'.$this->table.'">';

        //On parcourt tous les champs et on demande le code HTML de chacun
        foreach ($this->fields as $key => $value) {
            if(!empty($listInput)){
                if(array_key_exists($key,$listInput)) {
                    $templateHTML .= $this->getInputFormulaire($key,$readonly,$listInput[$key]);
                }
            }
            else {
                $templateHTML .= $this->getInputFormulaire($key,$readonly);
            }
        }

        $templateHTML .= '<div class="buttonForm">';
        $templateHTML .= $buttonAnnuler;
        $templateHTML .= $buttonSubmit;
        $templateHTML .= '</div>';

        //On termine le formulaire
        $templateHTML .= '</form>';

        return $templateHTML;
    }
    
    /**
     * Vérifie que les paramètres passés correspondent à l'objet et les chargent
     *
     * @param  array $arrayPost Paramètres qui ont été récupérés
     * @return boolean True si tout est OK sinon False
     */
    function verifParamFormulaire($arrayPost){
        //On parcourt les champs de l'objet
        foreach ($this->fields as $key => $value) {
            if(isSet($arrayPost[$key])){
                //On vérifie qu'un input HTML correspond à ce champ
                if(isSet($this->fields[$key]["type_input"])){
                    //Si on est sur un mot de passe, on vérifie qu'il y a une confirmation
                    if($this->fields[$key]["type_input"] === "password" && !isSet($arrayPost[$key."Confirm"])){
                        return false;
                    }
                }
            }
        }

        return $this->setFromPost($arrayPost);
    }

    
    /**
     * setFromPost
     *
     * @param  array $arrayPost Paramètres qui ont été récupérés
     * @return boolean True si tout est OK sinon False
     */
    function setFromPost($arrayPost){
        //On parcourt les champs de l'objet
        foreach ($this->fields as $key => $value) {
            if(isSet($arrayPost[$key])){
                //On vérifie qu'un input HTML correspond à ce champ
                if(isSet($this->fields[$key]["type_input"])){
                    if($this->fields[$key]["type_input"] === "password"){
                        if($arrayPost[$key] != "") {
                            if(!$this->set($key,$arrayPost[$key])){
                                return false;
                            }
                        }
                    }
                    else {
                        if(!$this->set($key,$arrayPost[$key])){
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
    
    /**
     * Vérifie si l'utilisateur a le droit de consulter l'élément
     *
     * @return boolean True si tout est OK sinon False
     */
    function verifPartitionnement(){
        $objSession = _session::getSession();
        //On parcourt tous les champs
        foreach ($this->fields as $cleChamp => $infosChamp) {
            //Si c'est un lien (objet)
            if($infosChamp["type"] === "object") {
                //Si le lien correspond à la table des utilisateurs
                if($infosChamp["nom_objet"] === $objSession->getTableUser()) {
                    //Si l'id correspond à l'id de l'utilisateur, c'est bon
                    if($this->values[$cleChamp] === $objSession->id())
                        return true;
                }
            }
        }

        //Cas si on agit sur le compte
        if($this->table === $objSession->getTableUser()){
            if($this->id() === $objSession->id())
                return true;
        }

        return false;
    }

    /**
     * Ajoute les conditions de filtre pour le partitionnement de données de l'utilisateur connecté
     *
     * @return mixed Clause du filtre de la requête, False s'il y a une erreur
     */
    function setFiltrePartitionnement(){
        //On initialise le champ de retour et le filtre
        $strRequete = "";
        $arrayFiltre = [];

        //On parcourt tous les champs
        foreach ($this->fields as $cleChamp => $infosChamp) {
            //Si c'est un lien (objet)
            if($infosChamp["type"] === "object") {
                $objSession = _session::getSession();
                //Si le lien correspond à la table des utilisateurs
                if($infosChamp["nom_objet"] === $objSession->getTableUser()) {
                     $arrayFiltre[] = " `$cleChamp` = ".$objSession->id(). " ";
                }
            }
        }

        if(!empty($arrayFiltre))
            $strRequete .= "( ".implode(" OR ",$arrayFiltre). " ) ";

        return $strRequete;
    }
}