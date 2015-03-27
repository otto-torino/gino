<?php
/**
 * @file registration_result.php
 * @brief Template risultato registrazione
 *
 * Le variabili a disposizione sono:
 * - $profile: Gino.App.Auth.RegistrationProfile, profilo di registrazione
 * - $error: mixed, bool FALSE, oppure string messaggio errore
 * - $request: Gino.App.Auth.RegistrationRequest, richiesta di registrazione
 */
?>
<section>
    <h1><?= \Gino\htmlChars($profile->title) ?></h1>
    <? if($error !== FALSE): ?>
        <p><? _('Registrazione non riuscita.') ?></p>
        <p><?= $error ?></p>
    <? else: ?>
        <p><?= sprintf(_('Grazie <strong>%s</strong>,<br />la registrazione è avvenuta con successo.'), \Gino\htmlChars($request->firstname.' '.$request->lastname)) ?></p>
        <p><?= _('Ti abbiamo inviato un\'email per la conferma del tuo indirizzo di posta elettronica.') ?></p>
        <? if($profile->auto_enable): ?>
            <p><?= _('Il tuo account verrà attivato appena avrai confermato l\'indirizzo email.') ?></p>
        <? else: ?>
            <p><?= _('A seguito di conferma dell\'indirizzo email il tuo account verrà verificato ed attivato da un amministratore.') ?></p>
        <? endif ?>
    <? endif ?>
    <p><?= _('Riceverai una mail di conferma ad attivazione avvenuta.') ?></p>
</section>
