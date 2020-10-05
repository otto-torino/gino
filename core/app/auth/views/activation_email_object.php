<?php
namespace Gino\App\Auth;
/**
 * @file activation_email_object.php
 * @brief Template oggetto mail inviata a seguito di conferma indirizzo email
 *
 * Le variabili a disposizione sono:
 * - $profile: Gino.App.Auth.RegistrationProfile, profilo di registrazione
 */
?>
<? //@cond no-doxygen ?>
<? $registry = \Gino\Registry::instance(); ?>
<?= sprintf(_("Attivazione account %s | %s"), $registry->sysconf->head_title, $profile->description); ?>
<? // @endcond ?>
