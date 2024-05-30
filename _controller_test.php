<?php

/**
 * TEST
 */

 require_once "src/utils/init.php";

 $objUtilisateur = new utilisateur(6);
 
 echo $objUtilisateur->getFormulaire("read");