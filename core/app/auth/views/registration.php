<?php
/**
 * @file registration.php
 * @brief Template form di registrazione
 *
 * Le variabili a disposizione sono:
 * - $profile: Gino.App.Auth.RegistrationProfile, profilo di registrazione
 * - $form: html, form di registrazione
 */
?>
<section>
<? if($profile->title): ?>
    <h1><?= \Gino\htmlChars($profile->title) ?></h1>
<? endif ?>
<?= \Gino\htmlChars($profile->text) ?>
<p><strong><?= _("A seguito della registrazione riceverai un'email di conferma con un url da visitare per confermare l'indirizzo email fornito.") ?></strong></p>
<? if(!$profile->auto_enable): ?>
    <p><?= _('Il tuo account verrà attivato a seguito di verifica dell\'amministratore.') ?></p>
<? endif ?>
<?= $form ?>
</section>
