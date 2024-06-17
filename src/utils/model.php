<?php

/**
 * 
 * Attributs disponibles
 * @property $table Tableau des champs
 * @property $champ_id Clé de la table
 * @property $links Lien avec les autres tables
 * @property $fields Liste des champs
 * @property $actions Nom des controllers d'action sur l'objet
 * @property $id Identifiant de l'objet
 * 
 * Méthodes disponibles
 * @method is() Retourne l'information si l'objet est chargé
 * @method get() Retourne l'objet du champ passé en paramètre
 * @method getFields() Retourne le tableau des objets fields
 * @method getToArray() Retourne la valeur pour tous les champs, sous forme d'un tableau
 * @method id() Retourne l'identifiant de l'objet courant
 * @method champ_id() Retourne le champ identifiant de l'objet courant
 * @method set() Définit la valeur d'un champ
 * @method loadFromTab() Charge l'objet à partir d'un tableau
 * @method load() Charge un objet à partir d'un identifiant
 * @method insert() Insertion de l'objet dans la base de données
 * @method update() Mise à jour de l'objet dans la base de données
 * @method delete() Suppression de l'objet dans la base de données
 * @method list() Retourne un tableau d'objet selon les critères fournis, tous les éléménts si aucun critère
 * @method getFormulaire() Renvoi le code HTML du formulaire pour l'objet et l'action demandée
 * @method verifParamsFormulaire() Vérifie que les paramètres passés correspondent à l'objet et les chargent
 * 
 */

/**
 * Classe _model : classe générique des objets du modèle de données
 */

class _model {

    /**
     * Attributs
     */

    // Nom de la table dans la BDD
    protected $table = "";
    // Clé de la table
    protected $champ_id = "";
    // Soumis au partitionnement
    protected $partitionement = false;
    // Lien avec les autres tables
    protected $links = []; // ["nom_table" => "champ_lien_vers_table"]
    // Liste des champs
    protected $fields = []; // ["nom_champ1" => objet_champ1,"nom_champ2" => objet_champ2]

    // Nom des controllers d'action sur l'objet
    protected $actions = []; // ["action" => "nom_controller"]
    
    // Identifiant de l'objet
    protected $id = 0;

    /**
     * Constructeur
     */
    
    /**
     * Constructeur de l'objet
     *
     * @param  integer $id Identifiant de l'objet à charger
     * @param  boolean $partitionement Si l'objet est soumis au partitionnement de données
     * @return void
     */
    function __construct($id = null, $partitionement = false) {
        // On définit les champs de l'objet
        $this->define();
        // Si l'identifiant n'est pas null
        if ( ! is_null($id) && $id != 0) {
            //On charge l'objet
            $this->load($id);
        }
        // On définit le partitionnement
        $this->partitionement = $partitionement;
    }
    
    /**
     * Méthodes
     */

    /**
     * Ajoute le champ à l'objet
     *
     * @param array $arrayInfoChamp Tableau des informations du champ
     * @return void
     */
    protected function addField($infosChamp) {
        // On instancie un objet _field du champ
        $this->fields[$infosChamp->name] = new _field($infosChamp,$this->table);
    }
    
    /**
     * Définit les champs de l'objet
     *
     * @return void
     */
    protected function define() {
        // On récupère les informations dans un fichier json
        $json = file_get_contents("src/modeles/json/" . $this->table . ".json");
        $infosModele = json_decode($json);
        
        // On récupère le nom de la table
        $this->table = $infosModele->table;
        // On récupère le champ qui correspond à l'id
        $this->champ_id = $infosModele->champ_id;
        // On parcourt les liens pour en construire le tableau
        if(isSet($infosModele->links)) {
            foreach ($infosModele->links as $value) {
                $this->links[] = [$value->table => $value->cle];
            }
        }
        // On parcourt les actions pour en construire le tableau
        if(isSet($infosModele->actions)) {
            foreach ($infosModele->actions as $value) {
                $this->actions[$value->action] = $value->url;
            }
        }
        
        // On parcourt tous les champs présent dans le fichier
        foreach ($infosModele->fields as $infosChamp) {
            // On appelle la méthode pour ajouter le champ
            $this->addField($infosChamp);
        }
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
     * Retourne l'objet du champ passé en paramètre
     *
     * @param  string $fieldName - Nom de l'attribut
     * @return mixed Valeur de l'attribut
     */
    function get($fieldName) {
        //On vérifie si une méthode get_fieldname existe dans la classe, dans ce cas on l'appelle
        if(method_exists($this,"get_$fieldName"))
            return call_user_func([$this,"get_$fieldName"]);

        if(isSet($this->fields[$fieldName]))
            return $this->fields[$fieldName];
        else
            return new _field();
    }

    /**
     * Retourne la valeur du champ
     *
     * @return mixed Valeur du champ
     */
    function getValue($fieldName) {
        return $this->fields[$fieldName]->getValue();
    }

    /**
     * Retourne le tableau des objets fields
     *
     * @return array Tableau des objets fields
     */
    function getFields() {
        return $this->fields;
    }

    /**
     * Retourne la valeur pour tous les attributs sous forme d'un tableau
     *
     * @return array Ensemble des champs dans un tableau associatif
     */
    function getToArray() {
        // Initialisation du tableau
        $arrayFields = [];

        // On parcourt tous les champs
        foreach ($this->fields as $cle => $objField) {
            $arrayFields[$cle] = $objField->getToArray();
        }

        // On ajoute l'identifiant
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

        // On valorise la valeur de l'objet field du champ concerné
        return $this->fields[$fieldName]->setValue($value);
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
            $this->fields[$name]->setValue($value);
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
            //Pour chaque champ on indique la valeur dans l'attribut de l'objet correspondant
            $this->fields[$fieldName]->setValue($data[$fieldName]);
        }

        //Puis on enregistre l'id dans son attribut dédié
        $this->id = $data[$this->champ_id];

        return true;
    }

    /**
     * Méthodes de gestion avec la BDD
     */
    
    /**
     * Charge un objet à partir d'un identifiant
     *
     * @param  integer $id Identifiant de l'objet à charger
     * @return boolean - True si le chargement s'est bien passé sinon False
     */
    function load($id) {
        // On instancie un objet de la classe _requete avec les informations nécessaires
        $objRequete = new _requete(
            $this->fields,
            $this->table,
            [$this->table => $this],
            $this->partitionement,
            [
                [
                    "champ" => $this->champ_id,
                    "valeur" => $id,
                    "operateur" => "=",
                    "table" => $this->table
                ]
            ]
        );

        //On récupère les résultats
        $arrayResultats = $objRequete->select();

        //Si le tableau est vide, on retourne une erreur (false)
        if (empty($arrayResultats)) {
            return false;
        }

        //On récupère la ligne de résultat dans une variable
        $arrayInfos = $arrayResultats[0];

        // On appelle la méthode de chargement de l'objet depuis un tableau
        $this->loadFromTab($arrayInfos);

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

        // On instancie un objet de la classe _requete avec les informations nécessaires
        $objRequete = new _requete(
            $this->fields,
            $this->table,
            [$this->table => $this],
            $this->partitionement
        );

        // On exécute la requête d'insertion
        $this->id = $objRequete->insert();

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
        
        // On instancie un objet de la classe _requete avec les informations nécessaires
        $objRequete = new _requete(
            $this->fields,
            $this->table,
            [$this->table => $this],
            $this->partitionement,
            [
                [
                    "champ" => $this->champ_id,
                    "valeur" => $this->id,
                    "operateur" => "=",
                    "table" => $this->table
                ]
            ]
        );
           
        //On prépare la requête
        if(!$objRequete->update()){
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
        // On instancie un objet de la classe _requete avec les informations nécessaires
        $objRequete = new _requete(
            $this->fields,
            $this->table,
            [$this->table => $this],
            $this->partitionement,[
                [
                    "champ" => $this->champ_id,
                    "valeur" => $this->id,
                    "operateur" => "=",
                    "table" => $this->table
                ]
            ]
        );

        //On exécute la requête avec les parmaètres
        if ( ! $objRequete->delete()) {
            return false;
        }

        //On remet l'id de l'objet à 0
        $this->id = 0;

        return true;
    }
        
    /**
     * Retourne un tableau d'objet selon les critères fournis, tous les éléménts si aucun critère
     *
     * @param  array $arrayTables Tableau des tables à interoger (en plus de la table de l'objet) ["nom_table1" => "objet_table1","nom_table1" => "objet_table1"] (facultatif)
     * @param  array $arrayFiltres Tableau des critères de filtre au format [["champ"=>"nom_champ","valeur"=>"test","operateur"=>"LIKE","table"=>"nom_table_champ"],["champ" => "bloc", "valeur" => [["champ"=>"nom","valeur"=>"test","operateur"=>"LIKE","table"=>"nom_table_champ"],["champ"=>"nom","valeur"=>"test","operateur"=>"LIKE","table"=>"nom_table_champ"]], "operateur" => "OR"]] (facultatif)
     * @param  array $arrayCriteresTri Tableau des crtières de tri au format [["champ" => "sens"],["champ" => "sens"]] (facultatif)
     * @param  array $arrayLimit Tableau des crtières pagination ["limit" => 10,"offset" => 0] (facultatif)
     * @return mixed Tableau d'objets indexé sur l'id, s'il y a une erreur False
     */
    function list($arrayTables = [], $arrayFiltres = [],$arrayCriteresTri = [], $arrayLimit = []) {
        // On instancie un objet de la classe _requete avec les informations nécessaires
        $objRequete = new _requete(
            $this->fields,
            $this->table,
            array_merge([$this->table => $this],$arrayTables),
            $this->partitionement,
            $arrayFiltres,
            $arrayCriteresTri,
            $arrayLimit
        );

        //On récupère les résultats
        $arrayResultats = $objRequete->select();

        // On construit le tableau à retourner :
        // Pour chaque élément on instance un objet que l'on met dans le tableau final
        $arrayObjResultat = [];
        foreach ($arrayResultats as $unResultat) {
            $newObj = new $this->table();
            $newObj->loadFromTab($unResultat);

            $arrayObjResultat[$unResultat[$this->champ_id]] = $newObj;
        }
  
        return $arrayObjResultat;
    }

    /**
     * Méthodes de formulaire
     */

    /**
     * Renvoi le code HTML du formulaire pour l'objet et l'action demandée
     *
     * @param  string $action Action qui sera derrière le formulaire (create,read,update,delete)
     * @param  boolean $json Traitement du formulaire en ajax (True ou False - Valeur par défaut)
     * @param  array $listInput Liste de champs spécifiques attendus (Tableau vide - Valeur par défaut)
     * @param  array $others Autres paramètres supplémentaires à utiliser
     * @return mixed Code HTML ou false s'il y a une erreur
     */
    function getFormulaire($action, $json=false, $withButton=true, $paramAction =[], $listInput=[], $others = []){
        // On initialise le template HTML
        $templateHTML = '';
        // Quelle vision des champ (vide, readonly, disabled)
        $acces = '';
        // Tableau des paramètres de l'action
        $paramURL = [];

        // On construit les éléments dépendant l'action
        switch ($action) {
            // Formulaire de création 
            case 'create':
                // Action qui sera executé à la soumission du formulaire
                $urlAction = $this->actions[$action].".php";
                // Bouton principale qui lancera l'action
                $buttonSubmit = '<input type="submit" value="Créer">';
                // Bouton secondaire, pas d'action déclencher de base
                $buttonAnnuler = '<input type="button" value="Annuler">';
                // Paramètre de vision des champs dans le formulaire
                $acces = '';
                break;
            // Formulaire de mise à jour 
            case 'update':
                // Action qui sera executé à la soumission du formulaire
                $urlAction = $this->actions[$action].".php";
                // On ajoute en paramètre de l'action, l'identifiant de l'élément (nécessaire pour les traitements suivants)
                $paramURL["id"] = $this->id();
                // Bouton principale qui lancera l'action
                $buttonSubmit = '<input type="submit" value="Modifier">';
                // Bouton secondaire, pas d'action déclencher de base
                $buttonAnnuler = '<input type="button" value="Annuler">';
                // Paramètre de vision des champs dans le formulaire
                $acces = '';
                break;
            // Formulaire de lecture
            case 'read':
                // Action qui sera executé à la soumission du formulaire
                $urlAction = $this->actions["list"].".php";
                // Bouton principale qui lancera l'action (pas d'action en lecture)
                $buttonSubmit = '';
                // Bouton secondaire, pas d'action déclencher de base
                $buttonAnnuler = '<input type="button" value="Retour à la liste" id="btn-back-list">';
                // Paramètre de vision des champs dans le formulaire
                $acces = 'readonly';
                break;
            // Formulaire de suppression
            case 'delete':
                // Action qui sera executé à la soumission du formulaire
                $urlAction = $this->actions[$action].".php";
                // On ajoute en paramètre de l'action, l'identifiant de l'élément (nécessaire pour les traitements suivants)
                $paramURL["id"] = $this->id();
                // Bouton principale qui lancera l'action
                $buttonSubmit = '<input type="submit" value="Supprimer">';
                // Bouton secondaire, pas d'action déclencher de base
                $buttonAnnuler = '<input type="button" value="Retour à la liste" id="btn-back-list">';
                // Paramètre de vision des champs dans le formulaire
                $acces = 'disabled';
                break;
            // Comportement par défaut
            default:
                // Action qui sera executé à la soumission du formulaire
                $urlAction = '';
                // Bouton principale qui lancera l'action
                $buttonSubmit = '';
                // Bouton secondaire, pas d'action déclencher de base
                $buttonAnnuler = '';
                // Paramètre de vision des champs dans le formulaire
                $acces = '';
                break;
        }

        // Si on attend un retour JSON, on ajouter le paramètre à l'URL
        if($json === true) $paramURL["json"] = true;

        // Si on a des paramètres d'URL, on les mets en forme et on les ajoute à l'URL 
        if(!empty($paramURL)) $urlAction .= "?". http_build_query(array_merge($paramURL,$paramAction));

        // On prépare un template pour les inputs
        $templateInputHTML = "";
        // On prépare le enctype
        $enctype = "";
        //On parcourt tous les champs et on demande le code HTML de chacun
        foreach ($this->fields as $keyField => $field) {
            $input = (isSet($listInput[$keyField])) ? $listInput[$keyField] : [];
            // On test si on a besoin de mettre l'encodage pour les pièce jointes dans le cas où un input file serait présent
            if(!empty($field->get("input"))) {
                if($field->get("input")["type"] === "file") {
                    $enctype = 'enctype="multipart/form-data"';
                }
            }
            
            $templateInputHTML .= $field->getElementFormulaire($input, $acces, $others);
        }

        // On construit le formulaire
        // En-tête
        $templateHTML .= '<form action="' . $urlAction . '" method="post" id="form_' . $this->table . '" ' . $enctype . '>';
        // Inputs
        $templateHTML .= $templateInputHTML;
        // Boutons
        if($withButton === true) {
            $templateHTML .= '<div class="buttonForm">';
            $templateHTML .= $buttonAnnuler;
            $templateHTML .= $buttonSubmit;
            $templateHTML .= '</div>';
        }

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
    function verifParamsFormulaire($arrayPost){
        //On parcourt les champs de l'objet
        foreach ($this->fields as $keyField => $field) {
            // Si le champ est bien dans les paramètres fournis
            if(isSet($arrayPost[$keyField])){
                if(!$field->setValueForm($arrayPost[$keyField],$arrayPost)) {
                    return false;
                }
            }
        }

        return true;
    }
}