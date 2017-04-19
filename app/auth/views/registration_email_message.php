<?php
namespace Gino\App\Auth;
/**
 * @file registration_email_message.php
 * @brief Template corpo della mail inviata a seguito di registrazione
 *
 * Le variabili a disposizione sono:
 * - $profile: Gino.App.Auth.RegistrationProfile, profilo di registrazione
 * - $request: Gino.App.Auth.RegistrationRequest, richiesta di registrazione
 * - $confirmation_url: string, URL per conferma indirizzo email
 */
?>
<? //@cond no-doxygen ?>
<? $registry = \Gino\Registry::instance() ?>
<?= sprintf(_("Buongiorno %s, grazie per esserti registrato su %s.\nPer confermare il tuo indirizzo email devi seguire il link qui sotto:\n%s"),
        $request->firstname.' '.$request->lastname, 
        $registry->sysconf->head_title, 
        $confirmation_url
    ) ?>
<? if(!$profile->auto_enable): ?>
    <?= sprintf(_("\nIl tuo account dovrà essere verificato ed attivato da un amministratore del sistema.")) ?>
<? endif ?>
<? // @endcond ?>
