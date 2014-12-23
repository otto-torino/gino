<?php
/**
 * @file login.php
 * @brief Template form di login
 * Le variabili a disposizione sono:
 * - $title: string, titolo
 * - $form: html, form di login
 */
?>
<? namespace Gino\App\Auth; ?>
<? //@cond no-doxygen ?>
<section class="auth-login">
  <h1><?= $title ?></h1>
  <?= $form ?>
</section>
<? // @endcond ?>
