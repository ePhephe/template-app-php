<?php

/**
 * Template : Page d'accueil
 * 
 * Paramètres :
 *  Néant
 */

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Titre de la page, unique pour chacune d'elle -->
    <title>myTickets - Votre accueil</title>
    <!-- Include des éléments du header, commun à toutes les pages -->
    <?php
        include_once("src/templates/fragments/header.php");
    ?>
</head>
<body id="page_accueil">
    <!-- Include des éléments de navigation (header, menu, ...), commun à toutes les pages -->
    <?php
        include_once("src/templates/fragments/nav.php");
    ?>
    <main class="container-full flex align-center justify-center direction-column" >
    <?php 


    ?>
        <!-- Affichage des messages d'information/erreur s'il y en a -->
        <?php
            include_once("src/templates/fragments/message.php");
        ?>
    </main>
    <!-- Include des éléments de footer (footer, script, ...), commun à toutes les pages -->
    <?php
        include_once("src/templates/fragments/footer.php");
    ?>
</body>
</html>