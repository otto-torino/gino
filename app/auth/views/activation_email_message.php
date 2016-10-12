<?php
/**
 * @file activation_email_message.php
 * @brief Template messaggio mail inviata a seguito di conferma indirizzo email
 *
 * Le variabili a disposizione sono:
 * - $profile: Gino.App.Auth.RegistrationProfile, profilo di registrazione
 * - $user: Gino.App.Auth.User, utente attivato
 * - $login_url: string, URL login
 * - $profile_url: string, URL pagina profilo
 */
?>
<? namespace Gino\App\Auth; ?>
<? //@cond no-doxygen ?>
<? $registry = \Gino\Registry::instance(); ?>
<?= sprintf(_("Buongiorno %s %s,\nla registrazione è stata confermata, la tua utenza è stata attivata, puoi loggarti al sistema dal seguente url:\n %s\n\n"), $user->firstname, $user->lastname, $login_url) ?>
<?= sprintf(_('Ti ricordiamo il nome utente da te scelto: %s.'), $user->username) . "\n" ?>
<?= sprintf(_("La tua pagina profilo è disponibile una volta loggato al seguente indirizzo:\n%s"), $profile_url) ?>
<? // @endcond ?>
