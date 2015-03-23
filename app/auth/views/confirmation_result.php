<?php
/**
 * @file confirmation_result.php
 * @brief Template risultato conferma registrazione
 *
 * Le variabili a disposizione sono:
 * - $profile: Gino.App.Auth.RegistrationProfile, profilo di registrazione
 * - $registration_request: Gino.App.Auth.RegistrationRequest richiesta di registrazione
 * - $mail_sent: bool, invio mail con attivazione automatica riuscito
 */
?>
<? namespace Gino\App\Auth; ?>
<? //@cond no-doxygen ?>
<? $registry = \Gino\Registry::instance(); ?>
<section>
    <h1><?= \Gino\htmlChars($profile->title) ?></h1>
    <p><?= _('La registrazione è stata confermata con successo!') ?></p>
    <? if($profile->auto_enable): ?>
        <p><?= _('L\'utente è attivo, puoi effettuare il login:') ?></p>
        <p><a class="btn btn-primary" href="<?= $registry->router->link('auth', 'login') ?>">vai alla pagina di login</a></p>
    <? else: ?>
        <p><?= _('La tua utenza dovrà ora essere attivata da un amministratore, riceverai un\'email di conferma ad attivazione avvenuta.') ?></p>
    <? endif ?>
</section>
<? // @endcond ?>
