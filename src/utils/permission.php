<?php

/**
 * Classe de gestion des permissions
 */

 class _permission {

    /**
     * Attributs
     */

    protected static $objPermission; //Objet unique sur la classe permission
    protected $urlRedirect = "accueil.php";

    //Constante pour définir les permissions
    protected const PERMISSIONS = [
        "ALL" => [
            "pizza" => [
                "create" => ["autorised" => true, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => true],
                "update" => ["autorised" => true, "partitionnement" => true],
                "delete" => ["autorised" => true, "partitionnement" => true]
            ],
            "composition" => [
                "create" => ["autorised" => true, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => true],
                "update" => ["autorised" => true, "partitionnement" => true],
                "delete" => ["autorised" => true, "partitionnement" => true]
            ],
            "ingredient" => [
                "create" => ["autorised" => false, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => false, "partitionnement" => false],
                "delete" => ["autorised" => false, "partitionnement" => false],
            ],
            "piecejointe" => [
                "create" => ["autorised" => true, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => true],
                "update" => ["autorised" => false, "partitionnement" => true],
                "delete" => ["autorised" => false, "partitionnement" => true],
            ],
            "utilisateur" => [
                "create" => ["autorised" => false, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => true],
                "update" => ["autorised" => true, "partitionnement" => true],
                "delete" => ["autorised" => false, "partitionnement" => true],
            ]
        ]
    ];

    /**
     * Méthodes
     */

    //Méthode pour travailler avec une instance unique sur cette classe
    public static function getPermission(){
        if(empty(static::$objPermission))
            static::$objPermission = new _permission();

        return static::$objPermission;
    }

    /**
     * Redirige l'utilisateur vers l'URL défini dans l'objet
     *
     * @param string $raison Raison de la redirection
     * @return void
     */
    function redirect($raison = ""){
        header("Location: " . $this->urlRedirect . "?redirect=" . $raison);
        exit();
    }

    /**
     * Vérifie que l'utilisateur connecté peut exécuter une action
     * 
     * @param  string $objet Objet concerné
     * @param  string $action Action à réaliser
     * @return boolean True si l'utilisateur est autorisé sinon False
     */
    function verifPermission($objet,$action){
        //On récupère la session
        $objSession = _session::getSession();
        //On vérifie que la session existe bien
        if( ! $objSession->isConnected()) {
            //Si on a pas de session, on retourne false
            return false;
        }
        else {
            //On récupère l'objet de l'utilisateur connecté
            $objUser = $objSession->userConnected();
        }

        // On regarde si on a un champ rôle sur l'utilisateur
        $role = ($objUser->get("u_role_user")->is()) ? $objUser->get("u_role_user") : "ALL";

        return self::PERMISSIONS[$role][$objet][$action]["autorised"];
    }

    /**
     * Vérifie le partitionnement des données autorisé pour un objet, une action et pour l'utilisateur connecté
     * 
     * @param  string $objet Objet concerné
     * @param  string $action Action à réaliser
     * @return boolean True si l'utilisateur voit uniquement ses informations sinon False il voit tout
     */
    function getPartitionnement($objet,$action){
        //On récupère la session
        $objSession = _session::getSession();
        //On vérifie que la session existe bien
        if( ! $objSession->isConnected()) {
            //Si on a pas de session, on retourne false
            return false;
        }
        else {
            //On récupère l'objet de l'utilisateur connecté
            $objUser = $objSession->userConnected();
        }

        // On regarde si on a un champ rôle sur l'utilisateur
        $role = ($objUser->get("u_role_user")->is()) ? $objUser->get("u_role_user") : "ALL";

        return self::PERMISSIONS[$role][$objet][$action]["partitionnement"];
    }
 }




