<?php
/**
 * @file view/block.php
 * @brief Template per la vista delle pagine inserite nel template
 *
 * Variabili disponibili:
 * - **section_id**: attributo id del tag section
 * - **tpl**: template del post deciso da opzioni
 *
 * @copyright 2012-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? namespace Gino\App\Page; ?>
<? //@cond no-doxygen ?>
<section id="<?= $section_id ?>">
    <?= $tpl ?>
</section>
<? // @endcond ?>
