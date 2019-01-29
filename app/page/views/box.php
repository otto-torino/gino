<?php
namespace Gino\App\Page;
/**
 * @file box.php
 * @brief Template per la vista delle pagine inserite nel template
 *
 * Variabili disponibili:
 * - **section_id**: attributo id del tag
 * - **tpl**: template del post deciso da opzioni
 * 
 * @copyright 2012-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? //@cond no-doxygen ?>
<article id="<?= $section_id ?>">
    <?= $tpl ?>
</article>
<? // @endcond ?>
