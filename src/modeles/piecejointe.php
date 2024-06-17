<?php

/**
 * 
 * Attributs disponibles
 * @property $table Nom de la table
 * @property $racine Répertoire racine où sont stockés les fichiers
 * 
 * Méthodes disponibles
 * @method get_url() Retourne l'URL complète du fichier
 * @method addFile() Réalise l'ajout dans fichier sur le serveur et dans la BDD
 * @method verifUpload() Vérifie que le fichier est correctement uploader
 * @method uploadFile() Upload le fichier au bon endroit sur le serveur
 * @method insertFile() Ajoute le fichier dans la base de données
 * 
 */

/**
 * Classe _piecejointe : classe générique des pieces jointes
 */

class piecejointe extends _model {

    /**
     * Attributs
     */

    // Nom de la table dans la BDD
    protected $table = "piecejointe";
    // Répertoire racine où sont stockés les fichiers
    protected $racine = "public/uploads/";

    /**
     * Méthodes
     */

    /**
     * Getters
     */
    
    /**
     * Retourne l'URL complète du fichier
     *
     * @return string URL complète du fichier (chemin + nom)
     */
    function get_url(){
        return $this->get("pj_chemin")->getValue() . $this->get("pj_nom_fichier")->getValue();
    }
    
    /**
     * Réalise l'ajout dans fichier sur le serveur et dans la BDD
     *
     * @param object $field Objet du champ qui doit contenir le fichier
     * @return mixed Idenfitiant du fichier inséré ou false si échec
     */
    function addFile($field){
        
        // On vérifie le fichier uploader
        if($this->verifUpload($field)) {
            // On réalise l'upload du fichier sur le serveur au bon endroit
            if($this->uploadFile($_FILES[$field->get("name")])) {
                // On insert le fichier dans la table de la base de données
                return $this->insertFile();
            }
        }

        // Sinon on retourne false
        return false;
    }
    
    /**
     * Vérifie que le fichier est correctement uploader
     *
     * @param object $field Objet du champ qui doit contenir le fichier
     * @return boolean True si tout est OK sinon False
     */
    function verifUpload($field) {
        // On vérifie qu'un fichier est uploader
        if (empty($_FILES[$field->get("name")])) {
            // Si c'est vide, on retourne false
            return false;
        } 
        
        // On stocke les informations dans une variable
        $arrayFile = $_FILES[$field->get("name")];
        // On récupère le code erreur correspondant
        $intCodeErreur = $arrayFile["error"];
        if ($intCodeErreur == UPLOAD_ERR_INI_SIZE or $intCodeErreur == UPLOAD_ERR_FORM_SIZE) {
            // La taille du fichier n'est pas bonne
            return false;
        } 
        else if ($intCodeErreur != UPLOAD_ERR_OK) {
            // Erreur technique
            return false;
        }

        return true;
    }
    
    /**
     * Upload le fichier au bon endroit sur le serveur
     *
     * @param array $arrayFile Tableau des informations du fichier uploader
     * @return boolean True si tout est OK sinon False
     */
    function uploadFile($arrayFile) {
        // On construit le nom du fichier
        // On génère un identifiant unique
        $uniqueId = uniqid();
        // On génère une chaîne aléatoire supplémentaire
        $randomBytes = bin2hex(random_bytes(8));
        // On construit le nom du fichier par concaténation
        $fileName = $uniqueId . '_' . $randomBytes;
        // On ajoute l'extension l'extension
        $fileName .= '.' . pathinfo($arrayFile["name"], PATHINFO_EXTENSION);
        // On stocke les informations concernant le fichier dans les champs correspondants
        $this->get("pj_nom_fichier")->setValue($fileName);
        $this->get("pj_type_fichier")->setValue($arrayFile["type"]);
        $this->get("pj_taille")->setValue($arrayFile["size"]);
        $this->get("pj_statut")->setValue("V");

        // On construit la date
        $dateCrea = new DateTime();
        $this->get("pj_date_creation")->setValue($dateCrea->format("Y-m-d H:i:s"));

        // On construit le chemin du fichier
        $chemin = $dateCrea->format("Y") . "/" . $dateCrea->format("m") . "/" . $dateCrea->format("d") . "/";
        $chemin = $this->racine . $chemin;
        $this->get("pj_chemin")->setValue($chemin);
        // On crée le répertoire s'il n'existe pas
        if ( !  is_dir($chemin)) mkdir($chemin, 0777, true);
        
        // Si jamais un fichier du même nom dans le même répertoire existe
        if (file_exists($chemin . $fileName)) {
            return false;
        }

        // On déplace le fichier et effectue le retour
        return move_uploaded_file($arrayFile["tmp_name"], $chemin . $fileName);
    }
    
    /**
     * Ajoute le fichier dans la base de données
     *
     * @return mixed Idenfitiant du fichier inséré ou false si échec
     */
    function insertFile() {
        // Si il y a une erreur dans l'insert
        if(!$this->insert()){
            // On retourne false
            return false;
        }
        
        // Sinon on retourne l'id de la pièce jointe
        return $this->id();
    }
    
}