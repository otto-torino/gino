<?php
/**
 * @file activation_email_object.php
 * @brief Template oggetto mail inviata a seguito di conferma indirizzo email
 *
 * Le variabili a disposizione sono:
 * - $user: Gino.App.Auth.User, utente
 * - $data: array. Array di informazioni aggiuntive legate ai profili.
 *                  'description': descrizione profilo
 *                  'content': informazioni
 *                  'update_url': url update informazioni
 * - $form_password: html, form di modifica password
 */
?>
<? namespace Gino\App\Auth; ?>
<? //@cond no-doxygen ?>
<section>
    <h1><?= (string) $user ?></h1>
    <table class="table table-hover table-striped table-bordered">
        <tr>
            <th><?= _('Nome') ?></th>
            <td><?= \Gino\htmlChars($user->firstname) ?></td>
        </tr>
        <tr>
            <th><?= _('Cognome') ?></th>
            <td><?= \Gino\htmlChars($user->lastname) ?></td>
        </tr>
        <tr>
            <th><?= _('Username') ?></th>
            <td><?= \Gino\htmlChars($user->username) ?></td>
        </tr>
        <tr>
            <th><?= _('Email') ?></th>
            <td><?= \Gino\htmlChars($user->email) ?></td>
        </tr>
    </table>

    <h2><?= _('Modifica la password') ?></h2>
    <? if ($pwd_updated): ?>
        <p class="alert alert-success"><?= _('La password è stata modificata con successo.') ?></p>
    <? endif ?>
    <?= $form_password ?>
    <? foreach($data as $d): ?>
        <h2><?= \Gino\htmlChars($d['description']) ?></h2>
        <?= $d['content'] ?>
        <? if($d['update_url']): ?>
            <p><a class="btn btn-primary" href="<?= $d['update_url'] ?>"><?= _('modifica') ?></a></p>
        <? endif ?>
    <? endforeach ?>

    <? if(count($profiles_data)): ?>
        <h2><?= _('Profili di registrazione') ?></h2>
        <p><?= _('È possibile registrarsi ad altri servizi.') ?></p>
        <table class="table table-striped table-hovered table-bordered">
            <? foreach($profiles_data as $d): ?>
            <tr>
                <th><?= $d['profile']->ml('description') ?></th>
                <td><a class="btn btn-primary" href="<?= $d['activation_url'] ?>"><?= _('attiva') ?></a></td>
            </tr>
            <? endforeach ?>
        </table>
    <? endif ?>

    <h2><?= _('Elimina account') ?></h2>
    <p><?= _('L\'eliminazione è definitiva.') ?></p>
    <p><a class="btn btn-danger" onclick="if(confirm('<?= \Gino\jsVar(_('Sicuro di voler procedere con l\'eliminazione dell\'account?'))?>')) location.href='<?= $delete_account_url ?>';"><?= _('elimina account') ?></a>
</section>
<? // @endcond ?>
