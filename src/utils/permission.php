<?php

/**
 * Classe de gestion des permissions
 */

 class _permission {

    /**
     * Attributs
     */

    protected static $objPermission; //Objet unique sur la classe permission
    protected $urlRedirect = "accueil";

    //Constante pour définir les permissions
    protected const PERMISSIONS = [
        "ROLE_TECHNICIEN" => [
            "ticket" => [
                "create" => ["autorised" => false, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => false],
                "delete" => ["autorised" => false, "partitionnement" => true],
            ],
            "vente" => [
                "create" => ["autorised" => true, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => false],
                "delete" => ["autorised" => true, "partitionnement" => false],
            ],
            "utilisateur" => [
                "create" => ["autorised" => true, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => false],
                "delete" => ["autorised" => true, "partitionnement" => false],
            ],
            "produit" => [
                "create" => ["autorised" => false, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => false, "partitionnement" => true],
                "delete" => ["autorised" => false, "partitionnement" => true],
            ],
            "message" => [
                "create" => ["autorised" => true, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => true],
                "delete" => ["autorised" => true, "partitionnement" => true],
            ]
        ],
        "ROLE_VENDEUR" => [
            "ticket" => [
                "create" => ["autorised" => false, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => false],
                "delete" => ["autorised" => false, "partitionnement" => true],
            ],
            "vente" => [
                "create" => ["autorised" => true, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => false],
                "delete" => ["autorised" => true, "partitionnement" => false],
            ],
            "utilisateur" => [
                "create" => ["autorised" => true, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => false],
                "delete" => ["autorised" => true, "partitionnement" => false],
            ],
            "produit" => [
                "create" => ["autorised" => false, "partitionnement" => true],
                "read"  => ["autorised" => false, "partitionnement" => true],
                "update" => ["autorised" => false, "partitionnement" => true],
                "delete" => ["autorised" => false, "partitionnement" => true],
            ],
            "message" => [
                "create" => ["autorised" => true, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => true],
                "delete" => ["autorised" => true, "partitionnement" => true],
            ]
        ],
        "ROLE_CLIENT" => [
            "ticket" => [
                "create" => ["autorised" => true, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => true],
                "update" => ["autorised" => true, "partitionnement" => true],
                "delete" => ["autorised" => true, "partitionnement" => true],
            ],
            "vente" => [
                "create" => ["autorised" => false, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => true],
                "update" => ["autorised" => false, "partitionnement" => true],
                "delete" => ["autorised" => false, "partitionnement" => true],
            ],
            "utilisateur" => [
                "create" => ["autorised" => false, "partitionnement" => true],
                "read"  => ["autorised" => true, "partitionnement" => true],
                "update" => ["autorised" => true, "partitionnement" => true],
                "delete" => ["autorised" => true, "partitionnement" => true],
            ],
            "produit" => [
                "create" => ["autorised" => false, "partitionnement" => true],
                "read"  => ["autorised" => false, "partitionnement" => true],
                "update" => ["autorised" => false, "partitionnement" => true],
                "delete" => ["autorised" => false, "partitionnement" => true],
            ],
            "message" => [
                "create" => ["autorised" => true, "partitionnement" => false],
                "read"  => ["autorised" => true, "partitionnement" => false],
                "update" => ["autorised" => true, "partitionnement" => true],
                "delete" => ["autorised" => true, "partitionnement" => true],
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

        return self::PERMISSIONS[$objUser->get("u_role_user")][$objet][$action]["autorised"];
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

        return self::PERMISSIONS[$objUser->get("u_role_user")][$objet][$action]["partitionnement"];
    }
 }




