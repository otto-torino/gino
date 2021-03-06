<?php
namespace Gino\App\Auth;
/**
 * @file registration_email_object.php
 * @brief Template oggetto mail inviata a seguito di registrazione
 *
 * Le variabili a disposizione sono:
 * - $profile: Gino.App.Auth.RegistrationProfile, profilo di registrazione
 */
?>
<? //@cond no-doxygen ?>
<? $registry = \Gino\Registry::instance(); ?>
<?= sprintf(_("Registrazione %s | %s"), $registry->sysconf->head_title, $profile->description); ?>
<? // @endcond ?>
