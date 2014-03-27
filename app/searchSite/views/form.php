<?php
/**
 * Le variabili a disposizione sono:
 * - $form_action: l'url dell'attributo action del form per accedere ai risultati della ricerca
 * - $choices: bool, dice se sono possibili delle scelte di ricerca
 * - $check_options: se $choices è vero mostra il pannello con le scelte di ricerca
 * Nota bene!
 * La vista carica un javascript che gestisce la comparsa/scomparsa del pannello con le opzioni
 * di ricerca, per funzionare gli id del bottone di check deve essere 'search_site_check'!
 * E naturalmente deve essere stampata la variabile $check_options.
 */
?>
<form method="post" class="navbar-form navbar-left searchsite-form" action="<?php echo $form_action ?>" role="search">
    <?php if($choices): ?>
        <div class='form-group'>
            <input type="button" id="search_site_check" value="" />
        </div>
    <?php endif ?>
    <div class='form-group'>
        <input type="text" name="search_site" placeholder="<?= _('Cerca')?>" />
    </div>
    <div class='form-group'>
        <input type="submit" value="" />
    </div>
    <?php if($choices): ?>
        <?= $check_options ?>
    <?php endif ?>
</form>
