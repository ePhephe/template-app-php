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
 * @property $unicite Indique si la valeur du champ doit être unique
 * @property $visibility Valeur de champ unique
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
    // Indique si la valeur du champ doit être unique
    protected $unicite = false;
    // Indique si le champ doit être visible ou non (retourner par un getter ou non)
    protected $visibility = true;

    /**
     * Méthodes
     */

    /**
     * Constructeur de l'objet
     *
     * @param  object $champ Informations sur le champ à charger
     * @return void
     */
    function __construct($champ = [], $table = "") {
        if(!empty($champ)) {
            // On récupère le nom du champ
            $this->name = $champ->name;
            // On récupère le nom de l'objet lié s'il y en a un
            if(!empty($table)) $this->table = $table;
            // On récupère le type du champ
            $this->type = $champ->type;
            // On récupère le libellé du champ
            $this->libelle = $champ->libelle;
            // On récupère le nom de l'objet lié s'il y en a un
            if(isSet($champ->nomObjet)) $this->nomObjet = $champ->nomObjet;
            // On récupère la notion d'unicité du champ
            $this->unicite = $champ->unicite;
            // On récupère la notion de visibilité du champ
            $this->visibility = $champ->visibilite;
            // On récupère les paramètres de l'input du champ s'il y en a
            if(isSet($champ->input)) $this->input = (array) $champ->input;
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
            if($value < $this->get("contraintes")["min"])                
                return false;
        }

        // Si une contrainte d'une longueur maximale est présente
        if(array_key_exists("max_lenght",$this->get("contraintes"))) {
            if(strlen($value) > $this->get("contraintes")["max_lenght"])               
                return false;
        }

        // Si une contrainte d'une longueur minimale est présente
        if(array_key_exists("min_lenght",$this->get("contraintes"))) {
            if(strlen($value) < $this->get("contraintes")["min_lenght"])                
                return false;
        }

        if(array_key_exists("bdd",$this->get("formats")) && $this->get("type") === "text") {
            if(!preg_match($this->get("formats")["bdd"],$value)){
                return false;
            }
        }

        if(array_key_exists("bdd",$this->get("formats")) && $this->get("type") === "datetime" && $value != "") {
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
            // Si on est sur un mot de passe
            if($this->get("input")["type"]==="password") {
                // Si le mot de passe est vide, on return true sans changer de valeur
                if(empty($value)) {
                    return true;
                }
            }

            // Si on demande une confirmation du champ, il faut avoir le paramètre
            if($this->get("input")["confirmationNeeded"] === true && !isSet($arrayPOST[$this->get("name")."Confirm"])){
                return false;
            }
            else if ($this->get("input")["confirmationNeeded"] === true && isSet($arrayPOST[$this->get("name")."Confirm"])) {
                if($value != $arrayPOST[$this->get("name")."Confirm"]){
                    return false;
                }
                else {
                    return $this->setValue($arrayPOST[$this->get("name")]);
                }
            }
            else if(isSet($arrayPOST[$this->get("name")."Hidden"])){
                return $this->setValue($arrayPOST[$this->get("name")."Hidden"]);
            }
            else if($this->get("input")["type"] === "file") {
                // Si il n'y a pas de fichier, on ne met pas à jour la valeur et on retourne true sans erreur
                if(!empty($arrayPOST[$this->get("name")]["name"])) {
                    // On instancie un objet de pièce jointe
                    $objetPJ = new piecejointe();
                    $resultatUpload = $objetPJ->addFile($this);
                    // Selon le résultat, on set la valeur ou on retourn false
                    if($resultatUpload === false){
                        return false;
                    }
                    else {
                        return $this->setValue($resultatUpload); 
                    }
                }
                else {
                    return true;
                }
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
     * @return mixed Valeur de l'attribut ou false
     */
    function get($name){
        // On vérifie si une méthode get_fieldname existe dans la classe fille, dans ce cas on l'appelle
        if(method_exists($this,"get_$name"))
            return call_user_func([$this,"get_$name"]);

        if($name === "value" && $this->visibility === true)
            return $this->value;
        else
            return $this->$name;
    }

    /**
     * Retourne la valeur brute du champ
     *
     * @return mixed Valeur du champ brut ou false
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
                    return null;
                default:
                    return "";
            }
        }
        
        if($this->visibility === true)
            return $this->value;
        else
            return false;
    }

    /**
     * Retourne l'objet lié au champ
     *
     * @return mixed Objet lié au champ ou false
     */
    function getObjet(){
        // On vérifie que l'objet n'est pas vide
        if(!empty($this->get("objet"))){
            // On retourne l'objet
            if($this->visibility === true)
                return $this->objet;
        }
        else { 
            // On retourne un nouvel objet (non chargé)
            $objectName = $this->nomObjet;
            if($this->visibility === true) {
                if(empty($this->value))
                    return new $objectName();
                else
                    return new $objectName(intval($this->value));
            }
        }
    }

    /**
     * Retourne la valeur du champ mise en forme au format demandé
     *
     * @param string $format Format que l'on souhaite affiché
     * @return mixed Valeur du champ formaté ou false
     */
    function getFormat($format){
        // On récupère le format souhaité
        $format = $this->formats[$format];

        // On réalise le traitement nécessaire
        $value = $this->value;

        if($this->visibility === true)
            return $value;
        else
            return false;
    }

    /**
     * Retourne la valeur affichable pour l'attribut passé en paramètre dans le cas d'une liste clé valeur
     *
     * @return mixed Libellé affichable de l'attribut  ou false
     */
    function getListLibelle() {
        // On vérifie qu'en liste est bien renseigné pour ce champ
        if(!empty($this->listCleValeur)) {
            //Dans ce cas on retourne le libelle correspondant à la valeur du champ dans le tableau
            if($this->visibility === true)
                return (isSet($this->listCleValeur[$this->value]))?$this->listCleValeur[$this->value]: "";
            else
                return false;
        }
        else {
            //Sinon on retourne la valeur brute
            if($this->visibility === true)
                return $this->value;
            else
                return false;
        }
    }
    
    /**
     * Retourne le champ sous forme de tableau des différents formats
     *
     * @return mixed Tablea des valeurs du champ sous toutes ses formes ou false
     */
    function getToArray() {
        // On initialise le tableau de retour
        $arrayResultat = [];

        if($this->visibility === true) {
            // On met place les valeurs en fonction de ce qui est disponible
            $arrayResultat["value"] = $this->getValue();
            $arrayResultat["object"] = $this->getObjet()->getToArray();
            $arrayResultat["html"] = $this->getFormat("html");
            $arrayResultat["affichage"] = $this->getFormat("affichage");
            $arrayResultat["list_libelle"] = $this->getListLibelle();
        
            return $arrayResultat;
        }
        else {
            return false;
        }
    }

    /**
     * S'execute lorsque l'on utilise $obj->name
     * Permet de retourner la valeur d'un attribut
     *
     * @param  string $name Attribut concerné
     * @return mixed Valeur de l'attribut $name ou false
     */
    function __get($name){
        if($this->visibility === true)
            return $this->$name;
        else
            return false;
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
     * @param array $others Autres paramètres du formulaire à prendre en compte 
     * @return mixed Code HTML du champ sinon False en cas d'erreur
     */
    function getElementFormulaire($infosChamp = [], $acces = "", $others = []){
        // On vérifie si une méthode get_element_form_fieldname existe dans la classe de l'objet de la table, dans ce cas on l'appelle
        //On instancie
        if(method_exists($this->table,"get_element_form_$this->name")) {
            $obj = new $this->table ();
            return call_user_func([$obj,"get_element_form_$this->name"], $infosChamp, $acces, $others);
        }

        if(isSet($infosChamp["display"])){
            if($infosChamp["display"] === "none")
                return "";
        }

        // On vérifie si on a un readonly spécifique pour le champ
        if(isSet($infosChamp["acces"]))
            $acces = $infosChamp["acces"];

        // On initialise le template HTML
        $templateHTML = "";

        // Si le tableau des paramètres de l'input est complété
        if(!empty($this->get("input"))) {

            // On prépare le nom de l'input en fonction de la présence d'éléments dans infoChamps
            if(isSet($infosChamp["prefix_name"])){
                $name = $infosChamp["prefix_name"]."_".$this->get("input")["name"];
            }
            else {
                $name = $this->get("input")["name"];
            }

            // On met en place le label correspondant au champ
            $divInputGlobal = '<div id="div_' . $name . '" class="div_input_form">';
            $labelPrincipal = '<label for="' . $name . '">' . $this->get("libelle") . ' : </label>';

            // On recupère le type d'input du champ et on réalise le traitement adéquat
            switch ($this->get("input")["type"]) {
                // Si on est sur un select
                case 'select':
                    $inputPrincipal = '<select name="' . $name . '" id="' . $this->get("input")["id"] . '">';
                    
                    // Si on a un niveau d'accès au champ défini, on le spécifie sur le select (un peu différent)
                    $accesSelect = ((!empty($acces)) ? 'disabled' : "");
                    // On met en place l'option de choix vide du select
                    $inputPrincipal .= '<option value="" ' . $accesSelect . '>Choisissez une valeur</option>';

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
                                $inputPrincipal .= ' <option value="'.$cle.'" '.$selected.' '.$accesOption.'>'.$valeur.'</option>';
                        }
                        else {
                            $inputPrincipal .= ' <option value="'.$cle.'" '.$selected.' '.$accesOption.'>'.$valeur.'</option>';
                        }
                        
                    }
                    $inputPrincipal .= '</select>';

                    // On construit le template
                    $templateHTML = $divInputGlobal.$labelPrincipal.$inputPrincipal. '</div>';
                    break;
                //Si on est sur un textarea
                case 'textarea':
                    $inputPrincipal = '<textarea name="' . $name . '" id="' . $this->get("input")["id"] . '"';
                    // Si on a une longueur maximale de champ
                    if(isSet($this->get("contraintes")["max_length"])){
                        $inputPrincipal .= 'maxlenght="' . $this->get("contraintes")["max_length"] . '" ';
                    }
                    // Si on a une longueur minimale de champ
                    if(isSet($this->get("contraintes")["min_length"])){
                        $inputPrincipal .= 'minlenght="' . $this->get("contraintes")["min_length"] . '" ';
                    }
                    $inputPrincipal .= $acces . '>' . $this->get("value") . '</textarea>';
                    // On construit le template
                    $templateHTML = $divInputGlobal.$labelPrincipal.$inputPrincipal. '</div>';
                    break;
                //Si on est sur un checkbox
                case 'checkbox':
                    # code...
                    break;
                //Si on est sur un radiobox
                case 'radio':
                    $templateHTML .= $divInputGlobal;
                    // On enclenche un compteur pour gérer les id
                    $i = 0;
                    foreach ($this->get("listCleValeur") as $key => $value) {
                        // On indique le paramètre checked de l'option si elle correspond à la valeur du champ
                        $checked = ($this->get("value") === strval($key)) ? "checked" : "";

                        $choixRadio = '<input type="radio" name="' . $name . '" id="' . $name . $i . '" value="'.$key.'" '.$acces.' '.$checked.'>';
                        $choixRadio .= '<label for="' . $name . $i . '">' . $value . '</label>';
                        
                        $templateHTML .= '<div>'.$choixRadio.'</div>';
                        $i++;
                    }
                    
                    $templateHTML .= '</div>';
                    break;
                case 'hidden':
                    // On construit le champ
                    $inputPrincipal = '<input type="' . $this->get("input")["type"] . '" name="' . $name . '" id="' . $this->get("input")["id"] . '" ';

                    // Si on a une valeur pour le champ
                    if(!empty($this->getValue())){
                        $inputValue = 'value="' . $this->getValue() . '" ';
                    }
                    else {
                        $inputValue = "";
                    }

                    // On construit le template
                    $templateHTML = $divInputGlobal.$inputPrincipal.$inputValue.'</div></div>';
                    break;
                //Si on est sur un radiobox
                case 'file':
                    // On initialise les template HTML
                    $inputContraintes = "";
                    // On construit le champ
                    $inputPrincipal = '<input type="' . $this->get("input")["type"] . '" name="' . $name . '" id="' . $this->get("input")["id"] . '" ';
                    // Si on a une valeur maximale de champ
                    if(isSet($this->get("input")["accept"])){
                        $inputContraintes .= 'accept="' . $this->get("input")["accept"] . '" ';
                    }
                    
                    $templateHTML = $divInputGlobal.$labelPrincipal.$inputPrincipal.$inputContraintes.$acces.'></div>';
                    break;
                //Sinon on est sur un input text classique
                default:
                    // On initialise les template HTML
                    $inputContraintes = "";
                    // On construit le champ
                    $inputPrincipal = '<input type="' . $this->get("input")["type"] . '" name="' . $name . '" id="' . $this->get("input")["id"] . '" ';

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
                        $inputContraintes .= 'step="' . $this->get("input")["step"] . '" ';
                    }
                    // Si on a une valeur maximale de champ
                    if(isSet($this->get("input")["placeholder"])){
                        $inputContraintes .= 'placeholder="' . $this->get("input")["placeholder"] . '" ';
                    }
                    // Si on a une valeur pour le champ
                    if(!empty($this->getValue())){
                        $inputValue = 'value="' . $this->getValue() . '" ';
                    }
                    else {
                        $inputValue = "";
                    }

                    // On construit le template
                    $templateHTML = $divInputGlobal.'<div>'.$labelPrincipal.$inputPrincipal.$inputContraintes.$inputValue.$acces.'</div>';

                    // Si le champ a besoin d'une confirmation
                    if($this->get("input")["confirmationNeeded"] === true){
                        $labelConfirm = '<label for="' . $name . 'Confirm"> Confirmation : </label>';
                        $inputConfirm = '<input type="' . $this->get("input")["type"] . '" name="' . $name . 'Confirm" id="' . $this->get("input")["id"] . 'Confirm" ';
                        // On construit le template
                        $templateHTML .= '<div>'.$labelConfirm.$inputConfirm.$inputContraintes.$inputValue.$acces.'></div>';
                    }
                    
                    $templateHTML .= '</div></div>';
                    break;
            }

            return $templateHTML;    
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