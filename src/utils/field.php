<?php

/**
 * 
 * Attributs disponibles
 * @property $table Nom de la table du champ
 * @property $name Nom du champ
 * @property $type Type du champ
 * @property $libelle Libellé du champ
 * @property $nomObjet Nom de l'objet lié
 * @property $value Valeur brut du champ
 * @property $objet Objet lié
 * @property $input Paramètres de l'input
 * @property $contraintes Contraintes du champ
 * @property $formats Formats du champ
 * @property $listCleValeur Liste clés <=> valeur
 * @property $unicite Valeur de champ unique
 * 
 * Méthodes disponibles
 * @method is() Retourne l'information si l'objet est chargé
 * @method set() Définit la valeur d'un attribut
 * @method setValue() Définit la valeur du champ
 * @method setValueForm() Définit la valeur du champ à partir des informations d'un formulaire
 * @method get() Retourne la valeur pour l'attribut passé en paramètre
 * @method getValue() Retourne la valeur brute du champ
 * @method getObjet() Retourne l'objet lié au champ
 * @method getFormat() Retourne la valeur du champ mise en forme au format demandé
 * @method getListLibelle() Retourne la valeur affichable pour l'attribut passé en paramètre dans le cas d'une liste clé valeur
 * @method getToArray() Retourne le champ sous forme de tableau des différents formats
 * @method verify() Vérifie que la requête est correctement paramétrée
 * @method getElementFormulaire() Retourne l'élément de formulaire correspondant au champ
 * @method verifUnicite() Vérifie que le champ est unique dans la base de données
 * 
 */

/**
 * Classe _field : classe générique des champs
 */

class _field {

    /**
     * Attributs
     */

    // Nom de la table du champ
    protected $table = "";
    // Nom du champ
    protected $name = "";
    // Type du champ
    protected $type = ""; // id, text, datetime, integer, float, password, email, object
    // Libellé du champ
    protected $libelle = "";
    // Nom de l'objet lié
    protected $nomObjet = "";
    // Valeur brut du champ
    protected $value = "";
    // Objet lié
    protected $objet;
    // Paramètres de l'input
    protected $input = []; // ["name" => "", "id" => "", "placeholder" => "", "type" => "", "step" => "1/0.1/0.01", "confirmationNeeded" => true]
    // Contraintes du champ
    protected $contraintes = []; // ["min" => "","max" => "","min_length" => "","max_length" => ""]
    // Formats du champ
    protected $formats = []; // ["affichage" => "", "bdd" => "", "hmtl" => ""]
    // Liste clés <=> valeur
    protected $listCleValeur = []; // ["cle1" => "valeur1","cle2" => "valeur2"]
    // Valeur de champ unique
    protected $unicite = false;

    /**
     * Méthodes
     */

    /**
     * Constructeur de l'objet
     *
     * @param  object $champ Informations sur le champ à charger
     * @return void
     */
    function __construct($champ) {
        // On récupère le nom du champ
        $this->name = $champ->name;
        // On récupère le type du champ
        $this->type = $champ->type;
        // On récupère le libellé du champ
        $this->libelle = $champ->libelle;
        // On récupère le nom de l'objet lié s'il y en a un
        if(isSet($champ->nomObjet)) $this->nomObjet = $champ->nomObjet;
        // On récupère la notion d'unicité du champ
        $this->unicite = $champ->unicite;
        // On récupère les contraintes sur le champ s'il y en a
        if(isSet($champ->contraintes)) $this->contraintes = (array) $champ->contraintes;
        // On récupère les format du champ s'il y en a
        if(isSet($champ->formats)) $this->formats = (array) $champ->formats;
        // On récupère la liste des clés et des valeurs pour la champ s'il y en a 
        if(isSet($champ->listeCleValeur)) {
            foreach ($champ->listeCleValeur as $value) {
                $this->listCleValeur[$value->cle] = $value->valeur;
            }
        }
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
     * Définit la valeur d'un attribut
     *
     * @param  string $name Nom de l'attribut à modifier
     * @param  mixed $value Nouvelle valeur de l'attribut
     * @return boolean - True si la valeur est acceptée sinon False
     */
    function set($name,$value){
        // Si l'attribut est value, on appelle la méthode setValue
        if($name === "value") {
            return $this->setValue($value);
        }

        $this->$name = $value;

        return true;
    }

    /**
     * Définit la valeur du champ
     *
     * @param  mixed $value Nouvelle valeur du champ
     * @return boolean - True si la valeur est acceptée sinon False
     */
    function setValue($value){
        // Si une contrainte d'une valeur maximale est présente
        if(array_key_exists("max",$this->get("contraintes"))) {
            if($value > $this->get("contraintes")["max"])                
                return false;
        }

        // Si une contrainte d'une valeur minimale est présente
        if(array_key_exists("min",$this->get("contraintes"))) {
            if($value > $this->get("contraintes")["min"])                
                return false;
        }

        // Si une contrainte d'une longueur maximale est présente
        if(array_key_exists("max_lenght",$this->get("contraintes"))) {
            if(strlen($value) > $this->get("contraintes")["max_lenght"])               
                return false;
        }

        // Si une contrainte d'une longueur minimale est présente
        if(array_key_exists("min_lenght",$this->get("contraintes"))) {
            if($value > $this->get("contraintes")["min_lenght"])                
                return false;
        }

        // Si le champ doit être unique
        if($this->get("unicite") === true) {
            if(!$this->verifUnicite($value)) {
                return false;
            }
        }

        if(array_key_exists("bdd",$this->get("formats")) && $this->get("type") === "text") {
            if(!preg_match($this->get("formats")["bdd"],$value)){
                return false;
            }
        }

        if(array_key_exists("bdd",$this->get("formats")) && $this->get("type") === "datetime") {
            $tempDate = new DateTime($value);
            $value = $tempDate->format($this->get("formats")["bdd"]);
        }

        if($this->get("type") === "password") {
            $value = password_hash($value,PASSWORD_BCRYPT);
        }

        if($this->get("type") === "email") {
            if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
                return false;
            }
        }
        
        $this->value = $value;

        if($this->get("type") === "object") {
            // On instancie un nouvel objet à jour
            $objectName = $this->get("nomObjet");
            $this->objet = new $objectName();
            $this->objet->load($value);
        }

        return true;

    }

    /**
     * Définit la valeur du champ à partir des informations d'un formulaire
     *
     * @param  mixed $value Nouvelle valeur du champ
     * @param array $arrayPOST Tableau des informations en POST
     * @return boolean - True si la valeur est acceptée sinon False
     */
    function setValueForm($value,$arrayPOST){
        //On vérifie qu'un input HTML correspond à ce champ
        if(!empty($this->get("input"))) {
            // Si on demande une confirmation du champ, il faut avoir le paramètre
            if($this->get("input")["confirmationNeeded"] === true && !isSet($arrayPOST[$this->get("name")."Confirm"])){
                return false;
            }
            else if ($this->get("input")["confirmationNeeded"] === true && isSet($arrayPOST[$this->get("name")."Confirm"])) {
                if($value != $arrayPOST[$this->get("name")."Confirm"]){
                    return false;
                }
            }
            else if(isSet($arrayPOST[$this->get("name")."Hidden"])){
                return $this->setValue($arrayPOST[$this->get("name")."Hidden"]);
            }
            else {
                return $this->setValue($arrayPOST[$this->get("name")]);
            }
        }

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
     * @param  string $fieldName - Nom de l'attribut
     * @return mixed Valeur de l'attribut
     */
    function get($name){
        // On vérifie si une méthode get_fieldname existe dans la classe fille, dans ce cas on l'appelle
        if(method_exists($this,"get_$name"))
            return call_user_func([$this,"get_$name"]);

        return $this->$name;
    }

    /**
     * Retourne la valeur brute du champ
     *
     * @return mixed Valeur du champ brut
     */
    function getValue(){
        // Si le champ n'a pas de valeur
        if(empty($this->value)) {
            switch ($this->type) {
                case 'text':
                    return "";
                case 'integer':
                    return 0;
                case 'float':
                    return 0.00;
                case 'datetime':
                    return new DateTime();
                default:
                    return "";
            }
        }

        return $this->value;
    }

    /**
     * Retourne l'objet lié au champ
     *
     * @return object Objet lié au champ
     */
    function getObjet(){
        // On vérifie que l'objet n'est pas vide
        if(!empty($this->get("objet"))){
            // On retourne l'objet
            return $this->objet;
        }
        else { 
            // On retourne un nouvel objet (non chargé)
            $objectName = $this->nomObjet;
            return new $objectName();
        }
    }

    /**
     * Retourne la valeur du champ mise en forme au format demandé
     *
     * @param string $format Format que l'on souhaite affiché
     * @return mixed Valeur du champ formaté
     */
    function getFormat($format){
        // On récupère le format souhaité
        $format = $this->formats[$format];

        // On réalise le traitement nécessaire
        $value = $this->value;

        return $value;
    }

    /**
     * Retourne la valeur affichable pour l'attribut passé en paramètre dans le cas d'une liste clé valeur
     *
     * @return string Libellé affichable de l'attribut
     */
    function getListLibelle() {
        // On vérifie qu'en liste est bien renseigné pour ce champ
        if(!empty($this->listCleValeur))
            //Dans ce cas on retourne le libelle correspondant à la valeur du champ dans le tableau
            return $this->listCleValeur[$this->value];
        else
            //Sinon on retourne la valeur brute
            return $this->value;
    }
    
    /**
     * Retourne le champ sous forme de tableau des différents formats
     *
     * @return array Tablea des valeurs du champ sous toutes ses formes
     */
    function getToArray() {
        // On initialise le tableau de retour
        $arrayResultat = [];

        // On met place les valeurs en fonction de ce qui est disponible
        $arrayResultat["value"] = $this->getValue();
        $arrayResultat["object"] = $this->getObjet()->getToArray();
        $arrayResultat["html"] = $this->getFormat("html");
        $arrayResultat["affichage"] = $this->getFormat("affichage");
        $arrayResultat["list_libelle"] = $this->getListLibelle();

        return $arrayResultat;
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
     * Vérifie que le champ est correctement paramétré
     *
     * @return boolean True si tout est ok sinon false
     */
    function verify(){
        return true;
    }
    
    /**
     * Retourne l'élément de formulaire correspondant au champ
     *
     * @param array $infosChamp Tableau de paramètres qui viennent compléter le champ 
     * @param string $acces Définit l'accès au champ : readonly, disabled ou rien
     * @return mixed Code HTML du champ sinon False en cas d'erreur
     */
    function getElementFormulaire($infosChamp = [], $acces = ""){
        // On vérifie si on a un readonly spécifique pour le champ
        if(isSet($infosChamp["acces"]))
            $acces = $infosChamp["acces"];

        // On initialise le template HTML
        $templateHTML = "";

        // Si le tableau des paramètres de l'input est complété
        if(!empty($this->get("input"))) {
            // On met en place le label correspondant au champ
            $templateHTML .= '<div id="div_' . $this->get("name") . '" class="div_input_form">
                <label for="' . $this->get("name") . '">' . $this->get("libelle") . ' : </label>';

            // On recupère le type d'input du champ et on réalise le traitement adéquat
            switch ($this->get("input")["type"]) {
                // Si on est sur un select
                case 'select':
                    $templateHTML .= '<select name="' . $this->get("input")["name"] . '" id="' . $this->get("input")["id"] . '">';
                    
                    // Si on a un niveau d'accès au champ défini, on le spécifie sur le select (un peu différent)
                    $accesSelect = ((!empty($acces)) ? 'disabled' : "");
                    // On met en place l'option de choix vide du select
                    $templateHTML .= '<option value="" ' . $accesSelect . '>Choisissez une valeur</option>';

                    // On définit les options possibles du select
                    foreach ($this->get("listCleValeur") as $cle => $valeur) {
                        // On initialise quelques variables
                        // On indique le paramètre selected de l'option si elle correspond à la valeur du champ
                        $selected = ($this->get("value") === strval($cle)) ? "selected" : "";
                        // On indique le paramètre d'accès de l'option, selon le selected et le accesSelect
                        $accesOption = ((!empty($accesSelect) && empty($selected)) ? "disabled" : "" );

                        // Si jamais on a un paramètre autorised_value dans le infosChamp
                        if(isSet($infosChamp["autorised_value"])) {
                            // Si la clé est présente dans les valeurs autorisées
                            if(in_array($cle,$infosChamp["autorised_value"]))
                                $templateHTML .= ' <option value="'.$cle.'" '.$selected.' '.$accesOption.'>'.$valeur.'</option>';
                        }
                        else {
                            $templateHTML .= ' <option value="'.$cle.'" '.$selected.' '.$accesOption.'>'.$valeur.'</option>';
                        }
                        
                    }
                    $templateHTML .= '</select>';
                    break;
                //Si on est sur un textarea
                case 'textarea':
                    $templateHTML .= '<textarea name="' . $this->get("input")["name"] . '" id="' . $this->get("input")["id"] . '"';
                    // Si on a une longueur maximale de champ
                    if(isSet($this->get("contraintes")["max_length"])){
                        $templateHTML .= 'maxlenght="' . $this->get("contraintes")["max_length"] . '" ';
                    }
                    // Si on a une longueur minimale de champ
                    if(isSet($this->get("contraintes")["min_length"])){
                        $templateHTML .= 'minlenght="' . $this->get("contraintes")["min_length"] . '" ';
                    }
                    $templateHTML .= $acces . '>' . $this->get("value") . '</textarea>';
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
                    // On initialise les template HTML
                    $inputHTML = "";
                    $inputContraintes = "";
                    // On construit le champ
                    $inputHTML .= '<div><input type="' . $this->get("input")["type"] . '" name="' . $this->get("input")["name"] . '" id="' . $this->get("input")["id"] . '" ';

                    // Ajout des contrôles sur le champ
                    // Si on a une longueur maximale de champ
                    if(isSet($this->get("contraintes")["max_length"])){
                        $inputContraintes .= 'maxlenght="' . $this->get("contraintes")["max_length"] . '" ';
                    }
                    // Si on a une longueur minimale de champ
                    if(isSet($this->get("contraintes")["min_length"])){
                        $inputContraintes .= 'minlenght="' . $this->get("contraintes")["min_length"] . '" ';
                    }
                    // Si on a une valeur minimale de champ
                    if(isSet($this->get("contraintes")["max"])){
                        $inputContraintes .= 'max="' . $this->get("contraintes")["max"] . '" ';
                    }
                    // Si on a une valeur maximale de champ
                    if(isSet($this->get("contraintes")["min"])){
                        $inputContraintes .= 'min="' . $this->get("contraintes")["min"] . '" ';
                    }
                    // Si on a une valeur maximale de champ
                    if(isSet($this->get("input")["step"])){
                        $inputContraintes .= 'step="' . $this->get("contraintes")["min"] . '" ';
                    }
                    $inputContraintes .= $acces.'></div>';

                    // On ajoute l'input au template global
                    $templateHTML .= $inputHTML.$inputContraintes;

                    // Si le champ a besoin d'une confirmation
                    if(isSet($this->get("input")["confirmationNeeded"])){
                        $inputConfirm = '<div><input type="' . $this->get("input")["type"] . '" name="' . $this->get("input")["name"] . 'Confirm" id="' . $this->get("input")["id"] . 'Confirm" ';
                        $templateHTML .= $inputConfirm.$inputContraintes;
                    }
                    break;
            }

            return $templateHTML.'</div>';    
        }

        return $templateHTML;
    }

    /**
     * Vérifie que le champ est unique dans la base de données
     *
     * @param  mixed $valeur Valeur du champ à tester
     * @return boolean True si la valeur est bien unique, False si elle est déjà utilisé
     */
    function verifUnicite($valeur){
        // On instancie un objet de requête
        $objRequete = new _requete();
        // On construit la requête SELECT
        $strRequete = "SELECT `" . $this->get("name") . "` FROM `" . $this->get("table") . "` WHERE `" . $this->get("name") . "` = :valeur ";
        $arrayParam = [
            ":valeur" => $valeur
        ];

        // On set la requête sur l'objet
        $objRequete->set("requete",$strRequete);
        $objRequete->set("params",$arrayParam);
        
        //On exécute la requête avec les parmaètres
        if ( ! $objRequete->execution()) {
            return false;
        }

        //On récupère les résultats
        $arrayResultats = $objRequete->objet->fetchAll(PDO::FETCH_ASSOC);
        //Si le tableau est vide, le résultat est bon
        if(empty($arrayResultats)) {
            return true;
        }

        return false;
    }
}