<?php
/**
 * @file data_recovery_success.php
 * @brief Template successo richiesta di recupero credenziali
 *
 * Le variabili a disposizione sono:
 * - $profile_url: string, url pagina profilo utente
 */
?>
<section>
    <h1><?= _('Recupero credenziali di accesso') ?></h1>
    <p><?= _('Ti abbiamo inviato un\'email contenente le nuove credenziali di accesso.') ?></p>
    <p><?= _('Potrai modificare la password nella tua pagina profilo a login effettuato.') ?></p>
    <p><a class="btn btn-primary" href="<?= $profile_url ?>"><?= _('profilo utente') ?></a></p>
</section>
