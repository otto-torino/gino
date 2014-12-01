<?php
/**
 * @file render.php
 * @brief Template per stampa header/footer
 * Le variabili a disposizione sono:
 * - id (string): id univoco per attributo html: site_header | site_footer
 * - content (string): tag immagine (con link alla home se header) o codice html
 * - type (int): tipo di contenuto: 1 => immagine, 2 => codice html
 * - header (bool): vero se si tratta di un header, falso se è un footer
 * - img_path (string): path relativo all'immagine se presente, null atrimenti
 * - code (string): codice html
 */
?>
<? namespace Gino\App\Graphics; ?>
<? //@cond no-doxygen ?>
<div id="<?= $id ?>">
    <?= $content ?>
</div>
<? // @endcond ?>
