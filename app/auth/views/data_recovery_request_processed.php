<?php
/**
 * @file data_recovery_request_processed.php
 * @brief Template processing richiesta di recupero credenziali
 *
 * Le variabili a disposizione sono:
 * - $error: bool, True se la mail inviata non corrisponde ad alcun utente
 */
?>
<section>
<h1><?= _('Recupero credenziali di accesso') ?></h1>
<? if($error === TRUE): ?>
    <p><?= _('L\'indirizzo email inserito non corrisponde ad alcun utente.') ?></p>
<? else: ?>
    <p><?= _('Ti abbiamo inviato un\'email all\'indirizzo di posta elettronica specificato con le istruzioni da seguire per recuperare le credenziali di accesso.') ?></p>
<? endif ?>
</section>
