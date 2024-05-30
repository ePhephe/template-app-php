<header>
<img src="public/images/logo.png" alt="Logo de l'application myTickets" class="logo_header">
    <nav>
        <ul>
            <?php
                if($objPermission->verifPermission("vente","create")) {
            ?>
            <li><a href="afficher_form_vente.php?action=create">Nouvelle vente</a></li>
            <?php
                }
                if($objPermission->verifPermission("utilisateur","create")) {
            ?>
            <li><a href="afficher_form_utilisateur.php?action=create">Nouveau compte</a></li>
            <?php
                }
                if($objPermission->verifPermission("ticket","read")) {
            ?>
            <li><a href="afficher_tickets.php">Tickets</a></li>
            <?php
                }
            ?>
        </ul>
    </nav>
    <div>
        <?php
            if($objPermission->verifPermission("utilisateur","update")) {
        ?>
        <a href="afficher_form_utilisateur.php?action=update&id=<?= $objSession->userConnected()->id() ?>">
                <img src="public/images/icon-profile.png" alt="">
        </a>
        <?php
            }
        ?>
        <a href="se_deconnecter.php">
            <img src="public/images/icon-deconnexion.png" alt="">
        </a>
    </div>
</header>
<div class="div_header_fixed">

</div>
<div class="background_app">

</div>