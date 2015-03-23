<?php
/**
 * @file activate_profile.php
 * @brief Template registrazione nuovo profilo per utente già registrato
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
    <?= $form ?>
</section>
