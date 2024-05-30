<?php

/**
 * Classe utilisateur : classe de gestion des utilisateurs
 */

class utilisateur extends _model {

    /**
     * Attributs
     */

    //Nom de la table dans la BDD
    protected $table = "utilisateur";
    //Clé de la table
    protected $champ_id = "u_id";
    //Nom des champs clés de connexion d'un utilisateur
    protected $fieldLogin = "u_email";
    protected $fieldPassword = "u_password";
    //Liste des champs
    protected $fields = [ 
        "u_nom" => [
            "type"=>"text",
            "type_input" => "text",
            "libelle"=>"Nom",
            "max_length" => 100
        ],
        "u_prenom" => [
            "type"=>"text",
            "type_input" => "text",
            "libelle"=>"Prénom",
            "max_length" => 150
        ],
        "u_email" =>  [
            "type"=>"text",
            "type_input" => "email",
            "libelle"=>"E-mail",
            "unique" => "O",
            "max_length" => 325,
            "format" => "/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/"
        ],
        "u_password" =>  [
            "type"=>"text",
            "type_input" => "password",
            "password" => true,
            "libelle"=>"Mot de passe",
            "max_length" => 20,
            "min_length" => 8,
            "format" => "/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/"
        ],
        "u_role_user" =>  [
            "type"=>"text",
            "type_input" => "select",
            "libelle"=>"Rôle de l'utilisateur",
            "liste_cle_valeur" => [
                "ROLE_TECHNICIEN" => "Technicien",
                "ROLE_VENDEUR" => "Vendeur",
                "ROLE_CLIENT" => "Client"
            ]
        ],
        "u_selector_reini_password" =>  [
            "type"=>"text"
        ],
        "u_token_reini_password" =>  [
            "type"=>"text"
        ],
        "u_expiration_reini_password" =>  [
            "type"=>"datetime",
            "format" => "Y-m-d H:i:s"
        ]
    ]; 

    //Nom des controllers d'action sur l'objet
    protected $actions = [
        "create" => "creer_utilisateur",
        "read"  => "detail_utilisateur",
        "update" => "modifier_utilisateur",
        "delete" => "supprimer_utilisateur",
        "list" => "lister_utilisateurs"
    ];

    //URLs de gestion de la connexion
    protected $arrayURL = [
        "formLogin" => "index.php",
        "login" => "se_connecter.php",
        "formReini" => "afficher_reini_password.php",
        "reini" => "reinitialisation_password.php",
        "formNewPassword" => "afficher_new_password.php",
        "newPassword" => "new_password.php",
        "accueil" => "afficher_accueil.php"
    ];

    /**
     * Méthodes
     */
     
     /**
      * Enregistre le mot de passe de l'utilisateur
      *
      * @param  string $valeur Valeur du mot de passe à enregistrer
      * @return void
      */
     function set_password($valeur){
        $this->values[$this->fieldPassword] = password_hash($valeur,PASSWORD_BCRYPT);
     }

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
        //On construit la requête SELECT
        $strRequete = "SELECT `$this->champ_id`, `$this->fieldPassword` FROM `$this->table` WHERE `$this->fieldLogin` = :login ";
        $arrayParam = [
            ":login" => $strLogin
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
        if (empty($arrayResultats)) {
            return false;
        }

        //On récupère la ligne de résultat dans une variable
        $arrayInfos = $arrayResultats[0];

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
                <input type="'.$this->fields[$this->fieldLogin]["type_input"].'" name="login" id="login">
            </div>
            <div>
                <label for="password">Mot de passe : </label>
                <input type="'.$this->fields[$this->fieldPassword]["type_input"].'" name="password" id="password">
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
                <input type="'.$this->fields[$this->fieldLogin]["type_input"].'" name="login" id="login">
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
                <input type="'.$this->fields[$this->fieldLogin]["type_input"].'" name="login" id="login">
            </div>
            <div>
                <label for="password">Mot de passe : </label>
                <input type="'.$this->fields[$this->fieldPassword]["type_input"].'" name="password" id="password">
            </div>
            <div>
                <label for="confirmPassword">Confirmation du mot de passe : </label>
                <input type="'.$this->fields[$this->fieldPassword]["type_input"].'" name="confirmPassword" id="confirmPassword">
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
        $urlToEmail = 'http://tickets.mdurand.mywebecom.ovh/'.$this->arrayURL["formNewPassword"].'?'.http_build_query([
            'selector' => $strSelector,
            'validator' => bin2hex($strToken)
        ]);

        //On construit la requête UPDATE
        $strRequeteReini = "UPDATE `$this->table` SET `u_selector_reini_password` = :selector, u_token_reini_password = :token, u_expiration_reini_password	=  :expiration 
            WHERE `$this->champ_id` = :userid";
        $paramReini = [
            ':userid' => $this->id(),
            ':selector' => $strSelector,
            ':token' => hash('sha256', $strToken),
            ':expiration' => $dateExpiration->format('Y-m-d H:i:s')
        ];

        //On prépare la requête
        $objRequeteReini = $bdd->prepare($strRequeteReini);
        //On exécute la requête avec les parmaètres
        if ( ! $objRequeteReini->execute($paramReini)) {
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
        $bdd = static::bdd();
        $strRequete = "SELECT `$this->champ_id`,`u_token_reini_password` FROM `$this->table` WHERE `u_selector_reini_password` = :selector AND `$this->fieldLogin` = :login 
            AND u_expiration_reini_password >= NOW()";
        $param = [
            ":selector" => $strSelector,
            ":login" => $strLogin
        ];
        $objRequete = $bdd->prepare($strRequete);

        //On exécute la requête avec les parmaètres
        if ( ! $objRequete->execute($param)) {
            return false;
        }

        $arrayResults = $objRequete->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($arrayResults)) {
            $calc = hash('sha256', hex2bin($strToken));
            if (hash_equals($calc, $arrayResults[0]['u_token_reini_password'])) {
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
            $this->set_password($strPassword);
            //On remet à zéro les éléments du token de réinitialisation
            $this->set("u_selector_reini_password","");
            $this->set("u_token_reini_password","");
            $this->set("u_expiration_reini_password","");

            return $this->update();
        }

        return false;
    }
}