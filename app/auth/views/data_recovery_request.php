<?php
/**
 * @file data_recovery_request.php
 * @brief Template richiesta di recupero credenziali
 *
 * Le variabili a disposizione sono:
 * - $form: html, form invio email
 */
?>
<section>
    <h1><?= _('Recupero credenziali di accesso') ?></h1>
    <p><?= _('Se hai perso username o password puoi attivare la procedura di recupero.') ?></p>
    <p><?= _('Inserisci l\'indirizzo email legato al tuo account qui sotto e premi invia. Riceverai un\'email con le istruzioni da seguire per recuperare le tue credenziali di accesso.') ?></p>
    <?= $form ?>
</section>
