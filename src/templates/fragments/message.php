<?php
if (isset($parametres["arrayResult"])) {
?>
    <div class="message-retour <?= $parametres["arrayResult"]["type_message"] ?>">
        <?= $parametres["arrayResult"]["message"] ?>
    </div>
<?php
}
?>

<!-- Message de retour AJAX -->
<div class="modal d-none">
    <div>

    </div>
</div>