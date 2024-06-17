<?php

/**
 * 
 * Attributs disponibles
 * @property $table Nom de la table
 * @property $fieldLogin Champ du login clé de connexion d'un utilisateur
 * @property $fieldPassword Champ du password clé de connexion d'un utilisateur
 * @property $fieldSelectorToken Champ clé de sélection du token (réini de mot de passe)
 * @property $fieldToken Password Champ du token (réini de mot de passe)
 * @property $fieldExpirationToken Champ date d'expiration du token (réini de mot de passe)
 * @property $arrayURL Tableau des URLs de gestion de la connexion
 * 
 * Méthodes disponibles
 * @method connexion() Vérification des informations de connexion de l'utilisateur
 * @method formulaireConnexion() Génération du formulaire de connexion en HTML
 * @method formulaireReiniPassword() Génération du formulaire de réinitialisation du mot de passe en HTML
 * @method formulaireNewPassword() Génération du formulaire de redéfinition du mot de passe en HTML
 * @method demandeReiniPassword() Lance une demande de réinitialisation du mot de passe pour un login donné
 * @method envoiMailReini() Envoie le mail de réinitialisation avec le lien
 * @method verifTokenReiniPassword() Vérifie que le token de réinitialisation du mot de passe est valide
 * @method updateNewPassword() Définit le nouveau mot de passe de l'utilisateur
 * 
 */

/**
 * Classe utilisateur : classe de gestion des utilisateurs
 */

class utilisateur extends _model {

    /**
     * Attributs
     */

    // Nom de la table dans la BDD
    protected $table = "utilisateur";
    // Nom des champs clés de connexion d'un utilisateur
    protected $fieldLogin = "u_email";
    protected $fieldPassword = "u_password";
    protected $fieldSelectorToken = "u_selector_reini_password";
    protected $fieldToken = "u_token_reini";
    protected $fieldExpirationToken = "u_expiration_reini_password";

    // URLs de gestion de la connexion
    protected $arrayURL = [
        "formLogin" => "index.php",
        "login" => "se_connecter.php",
        "formReini" => "afficher_reini_password.php",
        "reini" => "reinitialisation_password.php",
        "formNewPassword" => "afficher_new_password.php",
        "newPassword" => "new_password.php",
        "accueil" => "afficher_accueil.php"
    ];

    // Nom des controllers d'action sur l'objet
    protected $actions = [
        "" => ""
    ]; // ["action" => "nom_controller"]

    /**
     * Méthodes
     */
     
    /**
      * Retourne l'URL du formulaire passé en paramètre
      *
      * @param  string $form Formulaire dont on veut récupérer l'URL
      * @return string URL du formulaire
      */
      function getURLFormulaire($form){
        return $this->arrayURL[$form];
     }


     /**
     * Vérification des informations de connexion de l'utilisateur
     *
     * @param  string $strLogin Login de connexion saisi par l'utilisateur
     * @param  string $strPassword Mot de passe de connexion saisi par l'utilisateur
     * @return boolean - True si la connexion réussi sinon False
     */
    function connexion($strLogin,$strPassword){
        //On construit la requête
        $strRequete = "SELECT `$this->champ_id`, `$this->fieldPassword` FROM `$this->table` WHERE `$this->fieldLogin` = :login ";
        $arrayParam = [
            ":login" => $strLogin
        ];

        // On instancie un objet requête
        $objRequete = new _requete();
        // On passe notre requête et nos paramètres
        $objRequete->set("requete",$strRequete);
        $objRequete->set("params",$arrayParam);

        //On exécute la requête avec les parmaètres
        if ( ! $objRequete->execution()) {
            return false;
        }

        //On récupère les résultats
        $arrayResultats = $objRequete->objet->fetchAll(PDO::FETCH_ASSOC);
        //Si le tableau est vide, on retourne une erreur (false)
        if (empty($arrayResultats)) {
            return false;
        }

        //On récupère la ligne de résultat dans une variable
        $arrayInfos = $arrayResultats[0];

        // On vérifie le mot de passe
        if(password_verify($strPassword,$arrayInfos[$this->fieldPassword])) {
            $this->load($arrayInfos[$this->champ_id]);
            return true;
        }

        return false;
    }
    
    /**
     * Génération du formulaire de connexion en HTML
     *
     * @return string - Code HTML du formulaire de connexion
     */
    function formulaireConnexion(){
        //On initialise le template HTML du code
        $template = "";

        $template = '<form action="'.$this->arrayURL["login"].'" method="post" id="form_connexion">
            <div>
                <label for="login">Identifiant : </label>
                <input type="'.$this->fields[$this->fieldLogin]->get("input")["type"].'" name="login" id="login">
            </div>
            <div>
                <label for="password">Mot de passe : </label>
                <input type="'.$this->fields[$this->fieldPassword]->get("input")["type"].'" name="password" id="password">
            </div>
            <input type="submit" value="Connexion">
        </form>
        <a href="'.$this->arrayURL["formReini"].'" class="lien_mdp_oublie">Mot de passe oublié</a>';

        return $template;
    }

    /**
     * Génération du formulaire de réinitialisation du mot de passe en HTML
     *
     * @return string - Code HTML du formulaire de connexion
     */
    function formulaireReiniPassword(){
        //On initialise le template HTML du code
        $template = "";

        $template = '<form action="'.$this->arrayURL["reini"].'" method="post" id="form_reini_password">
            <div>
                <label for="login">Identifiant : </label>
                <input type="'.$this->fields[$this->fieldLogin]->get("input")["type"].'" name="login" id="login">
            </div>
            <input type="submit" value="Réinitialiser">
        </form>
        <a href="'.$this->arrayURL["formLogin"].'" class="lien_mdp_oublie">Retour à la connexion</a>';

        return $template;
    }

    /**
     * Génération du formulaire de redéfinition du mot de passe en HTML
     *
     * @return string - Code HTML du formulaire de connexion
     */
    function formulaireNewPassword($strSelector,$strToken){
        //On initialise le template HTML du code
        $template = "";

        $template = '<form action="'.$this->arrayURL["newPassword"].'?'.http_build_query(['selector' => $strSelector,'validator' => $strToken]).'" method="post" id="form_new_password">
            <div>
                <label for="login">Identifiant : </label>
                <input type="'.$this->fields[$this->fieldLogin]->get("input")["type"].'" name="login" id="login">
            </div>
            <div>
                <label for="password">Mot de passe : </label>
                <input type="'.$this->fields[$this->fieldPassword]->get("input")["type"].'" name="password" id="password">
            </div>
            <div>
                <label for="confirmPassword">Confirmation du mot de passe : </label>
                <input type="'.$this->fields[$this->fieldPassword]->get("input")["type"].'" name="confirmPassword" id="confirmPassword">
            </div>
            <input type="submit" value="Enregistrer">
        </form>
        <a href="'.$this->arrayURL["formLogin"].'" class="lien_mdp_oublie">Retour à la connexion</a>';

        return $template;
    }

    
    /**
     * Lance une demande de réinitialisation du mot de passe pour un login donné
     *
     * @param  mixed $strLogin
     * @return void True si la demande de réinitialisation a réussi sinon False
     */
    function demandeReiniPassword($strLogin){
        //On construit la requête SELECT
        $strRequete = "SELECT `$this->champ_id` FROM `$this->table` WHERE `$this->fieldLogin` = :login ";
        $arrayParam = [
            ":login" => $strLogin
        ];

        // On instancie un objet requête
        $objRequete = new _requete();
        // On passe notre requête et nos paramètres
        $objRequete->set("requete",$strRequete);
        $objRequete->set("params",$arrayParam);

        //On exécute la requête avec les parmaètres
        if ( ! $objRequete->execution()) {
            return false;
        }

        //On récupère les résultats
        $arrayResultats = $objRequete->objet->fetchAll(PDO::FETCH_ASSOC);
        //Si le tableau est vide, on retourne une erreur (false)
        if (empty($arrayResultats)) {
            return false;
        }

        //On récupère la ligne de résultat dans une variable
        $arrayInfos = $arrayResultats[0];
        $this->load($arrayInfos[$this->champ_id]);
        

        //On génère les éléments pour le token
        $strSelector = bin2hex(random_bytes(8));
        $strToken = random_bytes(32);
        //On définit une date d'expiration de 1h
        $dateExpiration = new DateTime('NOW');
        $dateExpiration->add(new DateInterval('PT01H'));

        //On génère l'URL à envoyer par mail
        $urlToEmail = 'http://pizza.mdurand.mywebecom.ovh/'.$this->arrayURL["formNewPassword"].'?'.http_build_query([
            'selector' => $strSelector,
            'validator' => bin2hex($strToken)
        ]);

        //On construit la requête UPDATE
        $strRequeteReini = "UPDATE `$this->table` SET `$this->fieldSelectorToken` = :selector, `$this->fieldToken` = :token, `$this->fieldExpirationToken`	=  :expiration 
            WHERE `$this->champ_id` = :userid";
        $paramReini = [
            ':userid' => $this->id(),
            ':selector' => $strSelector,
            ':token' => hash('sha256', $strToken),
            ':expiration' => $dateExpiration->format('Y-m-d H:i:s')
        ];

        // On passe notre requête et nos paramètres
        $objRequete->set("requete",$strRequeteReini);
        $objRequete->set("params",$paramReini);

        //On exécute la requête avec les parmaètres
        if ( ! $objRequete->execution()) {
            return false;
        }

        $this->envoiMailReini($urlToEmail,$strLogin);
        return true;
    }
    
    /**
     * Envoie le mail de réinitialisation avec le lien
     *
     * @param  string $urlToEmail URL à fournir contenant le token
     * @param  string $destinataire Adresse e-mail de l'utilisateur
     * @return void
     */
    function envoiMailReini($urlToEmail,$destinataire){
        mail("mdurand@mywebecom.ovh", 'Réinitialisation de votre mot de passe', $urlToEmail);
    }

    
    /**
     * Vérifie que le token de réinitialisation du mot de passe est valide
     *
     * @param  string $strLogin Identifiant de l'utilisateur
     * @param  string $strSelector Selecteur du token
     * @param  string $strToken Token de réinitialisation
     * @return mixed True si le token est valide sinon False
     */
    function verifTokenReiniPassword($strLogin, $strSelector, $strToken){
        //On prépare la requête
        $strRequete = "SELECT `$this->champ_id`,`$this->fieldToken` FROM `$this->table` WHERE `$this->fieldSelectorToken` = :selector AND `$this->fieldLogin` = :login 
            AND `$this->fieldExpirationToken` >= NOW()";
        $param = [
            ":selector" => $strSelector,
            ":login" => $strLogin
        ];

        // On instancie un objet requête
        $objRequete = new _requete();
        // On passe notre requête et nos paramètres
        $objRequete->set("requete",$strRequete);
        $objRequete->set("params",$param);

        //On exécute la requête avec les parmaètres
        if ( ! $objRequete->execution()) {
            return false;
        }

        $arrayResults = $objRequete->objet->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($arrayResults)) {
            $calc = hash('sha256', hex2bin($strToken));
            if (hash_equals($calc, $arrayResults[0][$this->fieldToken])) {
                //Si le token est valide on charge l'utilisateur et on retourne True
                $this->load($arrayResults[0][$this->champ_id]);
                return true;
            }
        }

        return false;
    }
    
    /**
     * Définit le nouveau mot de passe de l'utilisateur
     *
     * @param  string $strPassword
     * @param  string $strConfirmPassword
     * @return boolean True si la mise à jour s'est bien déroulé sinon False
     */
    function updateNewPassword($strPassword,$strConfirmPassword){
        //On vérifie que le mot de passe et sa confirmation correspondent
        if($strPassword === $strConfirmPassword){
            //S'ils correspondent on met à jours le mot de passe
            $this->set($this->fieldPassword,$strPassword);
            //On remet à zéro les éléments du token de réinitialisation
            $this->set($this->fieldSelectorToken,"");
            $this->set($this->fieldToken,"");
            $this->set($this->fieldExpirationToken,"");

            return $this->update();
        }

        return false;
    }
}