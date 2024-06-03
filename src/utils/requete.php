<?php

/**
 * 
 * Attributs disponibles
 * @property $fields Tableau des champs
 * @property $mainTable Nom de la table principale
 * @property $tables Tableau des tables nécessaires pour la requête
 * @property $requete Texte de la requête
 * @property $filtres Tableau des filtres à appliquer à la requête
 * @property $ordre Tableau des champs de tri
 * @property $limit Tableau des informations de pagination
 * @property $params Tableau des paramètres de la requête
 * @property $partitionnement Information si le partitionnement s'applique TRUE/FALSE
 * 
 * Méthodes disponibles
 * @method is() Retourne l'information si l'objet est chargé
 * @method set() Définit la valeur d'un attribut
 * @method get() Retourne la valeur pour l'attribut passé en paramètre
 * @method verify() Vérifie que la requête est correctement paramétrée
 * @method bdd() Retourne la connexion à la base de données
 * @method select() Construit et exécute une requête SELECT pour l'objet courant
 * @method insert() Construit et exécute une requête INSERT avec l'objet courant
 * @method update() Construit et exécute une requête UPDATE avec l'objet courant
 * @method delete() Construit et exécute une requête DELETE avec l'objet courant
 * @method execution() Execute la requête de l'objet courant
 * @method makeSelect() Construit la partie SELECT de la requête
 * @method makeFrom() Construit la partie FROM de la requête
 * @method makeTri() Construit la clause ORDER BY de la requête
 * @method makeLimit() Construit la clause LIMIT de la requête
 * @method makeFiltres() Construit la clause des filtres pour la clause WHERE
 * @method makeUnFiltre() Construit la clause d'un filtre pour la clause WHERE
 * @method makeUnFiltreValeur() Met en forme la valeur pour le filtre
 * @method makeSet() Construit la partie SET de la requête d'INSERT ou d'UPDATE
 * @method makeParamForSet() Construit le tableau de paramètres à passer pour une requête INSERT ou UPDATE
 * @method makeFiltrePartitionnement() Ajoute les conditions de filtre pour le partitionnement de données de l'utilisateur connecté
 * 
 */

/**
 * Classe _requete : classe générique des requêtes
 */

class _requete {

    /**
     * Attributs
     */

    // Champs de la requêtes
    protected array $fields = []; // ["nom_champ1" => "objet_champ1","nom_champ2" => "objet_champ2"]
    // Nom de la table principale (de laquelle part la requête)
    protected string $mainTable = "";
    // Tables de la requête
    protected array $tables = []; // ["nom_table1" => "objet_table1","nom_table1" => "objet_table1"] /!\ Pour l'instant on ne gère qu'une table pour les INSERT/UPDATE/DELETE
    // Texte de la requête
    protected string $requete = "";
    // Filtres pour la clause WHERE
    protected array $filtres = []; // [["champ"=>"nom_champ","valeur"=>"test","operateur"=>"LIKE","table"=>"nom_table_champ"],["champ" => "bloc", "valeur" => [["champ"=>"nom","valeur"=>"test","operateur"=>"LIKE","table"=>"nom_table_champ"],["champ"=>"nom","valeur"=>"test","operateur"=>"LIKE","table"=>"nom_table_champ"]], "operateur" => "OR"]
    // Ordres de tri
    protected array $order = []; // [["champ" => "sens"],["champ" => "sens"]]
    // Limit du nombre de résultat à appliquer
    protected array $limit = []; // ["limit" => 10,"offset" => 0]
    // Tableau des paramètres pour l'execution
    protected array $params = []; // [":nom_champ1" => "valeur_champ1",":nom_champ2" => "valeur_champ2"]
    // Est-ce que l'on veut que le partitionnement des données s'applique
    protected bool $partionnement = true;

    // Objet PDO de la requête
    protected object $objet;

    //Base de données ouverte
    protected static object $bdd;

    /**
     * Méthodes
     */
    
    /**
     * Constructeur de l'objet
     *
     * @return void
     */
    function __construct($fields = [], $mainTable = "", $tables= [], $partionnement = true, $filtres = [], $order = [], $limit = []) {
        $this->fields = $fields;
        $this->mainTable = $mainTable;
        $this->tables = $tables;
        $this->partitionnement = $partionnement;
        $this->filtres = $filtres;
        $this->order = $order;
        $this->limit = $limit;
    }

    /**
     * Retourne l'information si l'objet est chargé
     *
     * @return boolean True si l'objet est chargé, sinon False
     */
    function is() {
        return ! empty($this->mainTable);      
    }

    /**
     * Setters
     */
    
    /**
     * Définit la valeur d'un attribut
     *
     * @param  string $name Nom de l'attribut à modifier
     * @param  mixed $value Nouvelle valeur du champ
     * @return boolean - True si la valeur est acceptée sinon False
     */
    function set($name,$value){
        $this->$name = $value;

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
        $this->$name = $value;
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
     * Vérifie que la requête est correctement paramétrée
     *
     * @return boolean True si tout est ok sinon false
     */
    function verify(){
        return true;
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
    
    /**
     * Construit et exécute une requête SELECT pour l'objet courant
     *
     * @return mixed Tableau des résultat ou False s'il y a un échec de la requête
     */
    function select(){
        // Construction de la requête
        if($this->get("requete") === ""){
            // Clause SELECT
            $this->requete .= $this->makeSelect();
            // Clause FROM
            $this->requete .= $this->makeFrom();
            // Clause WHERE
            $this->requete .= " WHERE " . $this->makeFiltres();
            // Complément à la clause WHERE si partitionement
            if($this->get("partitionement") === true) {
                $this->requete .= $this->makeFiltrePartitionnement();
            }
            // Clause ORDER
            $this->requete .= $this->makeTri();
            // Clause LIMIT
            $this->requete .= $this->makeLimit();
        }

        //Execution de la requête
        if(!$this->execution()) {
            return false;
        }

        // On retourne un tableau des résultats
        return $this->objet->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Construit et exécute une requête INSERT avec l'objet courant
     *
     * @return mixed L'identifiant de la ligne insérée sinon False
     */
    function insert(){
        // Construction de la requête
        if($this->get("requete") === ""){
            // Clause INSERT
            $this->requete .= "INSERT ";
            // Clause FROM
            $this->requete .= $this->get("mainTable") . " ";
            // Clause SET
            $this->requete .= $this->makeSet();
        }

        //Execution de la requête
        if(!$this->execution()) {
            return false;
        }

        return $this->objet->lastInsertId();
    }

    /**
     * Construit et exécute une requête UPDATE avec l'objet courant
     *
     * @return boolean True si tout s'est bien passé sinon False
     */
    function update(){
        // Construction de la requête
        if($this->get("requete") === ""){
            // Clause UPDATE
            $this->requete .= "UPDATE ";
            // Clause FROM
            $this->requete .= $this->makeFrom();
            // Clause SET
            $this->requete .= $this->makeSet();
            // Clause WHERE
            $this->requete .= " WHERE " . $this->makeFiltres();
            // Complément à la clause WHERE si partitionement
            if($this->get("partitionement") === true) {
                $this->requete .= $this->makeFiltrePartitionnement();
            }
        }

        //Execution de la requête
        if(!$this->execution()) {
            return false;
        }

        return true;
    }
    
    /**
     * Construit et exécute une requête DELETE avec l'objet courant
     *
     * @return boolean True si tout s'est bien passé sinon False
     */
    function delete(){
        // Construction de la requête
        if($this->get("requete") === ""){
            // Clause DELETE
            $this->requete .= "DELETE ";
            // Clause FROM
            $this->requete .= $this->makeFrom();
            // Clause WHERE
            $this->requete .= " WHERE " . $this->makeFiltres();
            // Complément à la clause WHERE si partitionement
            if($this->get("partitionement") === true) {
                $this->requete .= $this->makeFiltrePartitionnement();
            }
        }

        //Execution de la requête
        if(!$this->execution()) {
            return false;
        }

        return true;
    }
    
    /**
     * Execute la requête de l'objet courant
     *
     * @return boolean True si tou s'est bien passé sinon False
     */
    function execution(){
        //On prépare la requête
        $bdd = static::bdd();
        $this->objet = $bdd->prepare($this->get("requete"));

        //On exécute la requête avec ses paramètres et on gère les erreurs
        if ( ! $this->objet->execute($this->get("params"))) { 
            //var_dump($strRequete);
            //var_dump($arrayParam);
            return false;
        }
  
        return true;
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
     * Construit la partie FROM de la requête
     * 
     * @todo Gérer les jointures autres que depuis la table principale
     * 
     * @return string - Texte du FROM de la requête
     */
    function makeFrom() {
        // On initialise un tableau vide
        $arrayResultat = [];
        
        // On récupère l'objet de la table principale
        $objMain = $this->get("tables")[$this->get("mainTable")];

        // On parcourt la liste des tables de la requête
        foreach ($this->get("tables") as $tableName => $table) {
            // Si on est sur la table principale, pas besoin de jointure
            if($tableName === $this->get("mainTable")) {
                $arrayResultat[] = "`" . $tableName . "`";
            }
            else {
                // Dans les autres cas, on a besoin de faire la jointure
                $arrayResultat[] = "LEFT JOIN `".$tableName."` ON `".$table->champ_id()."` = `".$objMain->get("links")[$tableName]."`";
            }
        }

        return "FROM " . implode(",",$arrayResultat);
    }
    
    /**
     * Construit la clause ORDER BY de la requête
     *
     * @return string Chaîne de caractère correspondant à la clause
     */
    function makeTri(){
        // On initialise un tableau des tris
        $arrayTempTri = [];

        // On parcourt les tris
        foreach ($this->get("order") as $cleTri => $sens) {
            $arrayTempTri[] = "`" . $cleTri . "` " . $sens;
        }

        if(!empty($arrayTempTri)) {
            return " ORDER BY " . implode(",",$arrayTempTri);
        }
        else {
            return "";
        }   
    }

    /**
     * Construit la clause LIMIT de la requête
     * 
     * @todo Tendre vers une vraie pagination
     * 
     * @return string Chaîne de caractère correspondant à la clause
     */
    function makeLimit(){
        // On regarde si un paramètre de LIMIT est présent
        if(!empty($this->get("limit"))) {
            return "LIMIT " . $this->get("limit")["offset"] . ", " . $this->get("limit")["limit"];
        }
        else {
            return "";
        } 
    }

    /**
     * Construit la clause des filtres pour la clause WHERE
     *
     * @return mixed - Chaîne construite de la clause WHERE sinon False
     */
    function makeFiltres($filtres = [], $liaisonContraintes = " AND ") {
        // On prépare le retour
        $arrayTempFiltres = [];

        // Si aucun filtre n'est passé en paramètre, on récupére les filtres de l'objet
        if(empty($filtres)) {
            $filtres = $this->get("filtres");
        }

        // On parcourt la liste des filtres de l'objet
        foreach ($filtres as $indexFiltre => $unFiltre) {
            // On gère si le filtre est un nouveau bloc de condition
            if($unFiltre["champ"] === "bloc") {
                // Dans le cas d'un bloc, on rappelle la fabrication de filtres
                $arrayTempFiltres[] = "(" . $this->makeFiltres($unFiltre["valeur"]," ".$unFiltre["operateur"." "]) . ")";
            }
            else {
                // Sinon on appelle la fonction pour contruire le filtre
                $arrayTempFiltres[] = $this->makeUnFiltre($unFiltre,$indexFiltre);
            }
        }

        // On retour la chaîne de caractère assemblée avec l'opérateur de liaison de contrainte
        return implode($liaisonContraintes,$arrayTempFiltres);
    }

    /**
     * Construit la clause d'un filtre pour la clause WHERE
     *
     * @param  array $unFiltre Tableau du filtre concerné : ["champ"=>"nom_champ","valeur"=>"test","operateur"=>"LIKE","table"=>"nom_table_champ"]
     * @param  integer $index Index du filtre dans la requête
     * @return mixed - Chaîne de caractère correspondant au filtre sinon False
     */
    function makeUnFiltre($unFiltre,$index) {
        // On initialise le filtre
        $strFiltre = "";
        // On récupère les objets table et field correspondant à notre champ
        $table = $this->get("tables")[$unFiltre["table"]];
        $field = $table->get("fields")["champ"];

        if($field === $table->champ_id()){
            $strFiltre .= "`" . $unFiltre["champ"] . "` " . $unFiltre["operateur"] . " :".$unFiltre["champ"].$index;
        }
        else if($field->get("type") === "text") {
            $strFiltre .= "UPPER(".$unFiltre["champ"].") " . $unFiltre["operateur"] . " :".$unFiltre["champ"].$index;
        }
        else {
            $strFiltre .= "`" . $unFiltre["champ"] . "` " . $unFiltre["operateur"] . " :".$unFiltre["champ"].$index;
        }

        $this->makeUnFiltreValeur($unFiltre,$index,$field);

        return $strFiltre;
    }

    /**
     * Met en forme la valeur pour le filtre
     *
     * @param  array $unFiltre Tableau du filtre concerné
     * @param  object $field Objet correspondant au champ du filtre
     * @return boolean - True si tout s'est bien passé sinon False
     */
    function makeUnFiltreValeur($unFiltre,$index,$field) {
        // On initialise une valeur à vide
        $valeur = "";

        // Selon le type, on va agir différement
        if($field->get("type") === "text") {
            //Dans le cas d'un type text
            if($unFiltre["operateur"] === "LIKE"){
                // Si l'opérateur est LIKE, on ajoute le caractère joker et on passe en majuscule
                $valeur = "%".strtoupper($unFiltre["valeur"])."%";
            }
            else {
                // Sinon on passe juste en majuscule
                $valeur = strtoupper($unFiltre["valeur"]);
            }
        }
        else {
            // Pour les autres types, pas de traitement particulier
            $valeur =  $unFiltre["valeur"];
        }

        $this->params[":".$unFiltre["champ"].$index] = $valeur;

        return true;
    }

    /**
     * Construit la partie SET de la requête d'INSERT ou d'UPDATE
     *
     * @return string - Texte du SET de la requête
     */
    function makeSet() {
        // On initialise le tableau à vide
        $arrayResultat = [];

        //On parcourt les tables, on s'arrête après la première
        foreach($this->get("tables") as $table) {
            //On parcourt les champs de la table
            foreach ($table->getFields() as $field) {
                // On construit la clé du paramètre
                $arrayResultat[] = "`" . $field->get("name") . "` = :" . $field->get("name");         
                // On appelle la fonction pour valoriser le paramètre
                $this->makeParamForSet($field);
            }

            return "SET " . implode(",",$arrayResultat);
        }
    }

    /**
     * Construit le tableau de paramètres à passer pour une requête INSERT ou UPDATE
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

    /**
     * Ajoute les conditions de filtre pour le partitionnement de données de l'utilisateur connecté
     *
     * @return mixed Clause du filtre de la requête, False s'il y a une erreur
     */
    function makeFiltrePartitionnement() {
        //On initialise le champ de retour et le filtre
        $strClausePartitionement = "";
        $arrayFiltrePartitionement = [];

        //On parcourt tous les champs
        foreach ($this->fields as $cleChamp => $field) {
            //Si c'est un lien (objet)
            if($field->get("type") === "object") {
                $objSession = _session::getSession();
                //Si le lien correspond à la table des utilisateurs
                if($field->get("nomObjet") === $objSession->getTableUser()) {
                     $arrayFiltrePartitionement[] = " `$cleChamp` = ".$objSession->id(). " ";
                }
            }
        }

        if(!empty($arrayFiltrePartitionement))
            $strClausePartitionement .= "( ".implode(" OR ",$arrayFiltrePartitionement). " ) ";

        return $strClausePartitionement;
    }
}