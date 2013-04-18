<?php
/**
 * @file view/box.php
 * @ingroup page
 * @brief Template per la vista delle pagine inserite nel template
 *
 * Variabili disponibili:
 * - **section_id**: attributo id del tag section
 * - **tpl**: template del post deciso da opzioni
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<section id="<?= $section_id ?>">
	<?= $tpl ?>
</section>
