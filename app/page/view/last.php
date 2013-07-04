<?php
/**
 * @file view/last.php
 * @ingroup page
 * @brief Template per la vista ultime pagine
 *
 * Variabili disponibili:
 * - **section_id**: attributo id del tag section
 * - **title**: titolo della vista
 * - **feed**: link ai feed rss
 * - **items**: template per ciascuno degli ultimi post (deciso da opzioni)   
 * - **archive**: link all'archivio completo
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<section id="<?= $section_id ?>">
	<header>
		<h1 class="left"><?= $title ?></h1>
		<div class="right feed">
			<?= $feed ?>
		</div>
		<div class="null"></div>
	</header>
	<? if(count($items)): ?>
		<? foreach($items as $item): ?>
			<?= $item ?>
		<? endforeach ?>
		<p class="archive"><?= $archive ?></p>
	<? else: ?>
		<p><?= _('Non risultano elementi registrati') ?></p>
	<? endif ?>
</section>
