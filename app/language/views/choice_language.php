<?php
namespace Gino;
/**
* @file choice_language.php
* @brief Template della vista scelta lingua
*
* Variabili disponibili:
* - **languages**: array, lingue attive, oggetti Gino.App.Language.Lang
* - **flag**: bool, opzione visualizza bandiere al posto delle etichette
* - **flag_prefix**: string, prefisso flag img
* - **flag_suffix**: string, suffisso flag img
* - **lng_support**: bool, indica se la lingua di navigazione del client è supportata dal sistema
* - **lng**: string, codice lingua di navigazione client
* - **lng_dft**: string, codice lingua di default
* - **router**: \Gino\Router, istanza di Gino.Router
*
* Il path delle immagini è il seguente:
* SITE_IMG / flag_prefix $language->label flag_suffix
*
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<section class="language-choice">
    <h1 class="hidden"><?= _('Scelta lingua') ?></h1>
    <ul>
    <?php foreach($languages as $l): ?>
        <li>
            <?php $selected = ($lng_support and $l->code() == $lng) or (!$lng_support and $l->code() == $lng_dft); ?>
            <a<?= $selected ? ' class="active"' : '' ?> href="<?= $router->transformPathQueryString(array('lng' => $l->code())) ?>">
                <?php if($flag): ?>
                    <img src="<?= SITE_IMG . '/' . $flag_prefix . $l->label . $flag_suffix ?>" alt="<?= htmlChars($l->label) ?>" />
                <?php else: ?>
                    <?= htmlChars($l->label) ?>
                <?php endif ?>
            </a>
        </li>
    <?php endforeach ?>
    </ul>
</section>
<? // @endcond ?>
