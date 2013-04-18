<?php
/**
 * @file view/archive.php
 * @ingroup page
 * @brief Template per la vista archivio
 *
 * Variabili disponibili:
 * - **section_id**: attributo id del tag section
 * - **title**: titolo della vista
 * - **items**: array di array con chiavi (name, url, f) (nome tag, url archivio tag, frequenza)
 * - **max_f**: massima frequenza
 *
 * @version 0.1
 * @copyright 2012 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<section id="<?= $section_id ?>">
	<h1><?= $title ?></h1>
	<? if(count($items)): ?>
		<? foreach($items as $item): ?>
			<a href="<?= $item['url'] ?>" style="font-size:<?= str_replace(',', '.', (0.8 + 2 * round($item['f']/$max_f, 1))) ?>em"><?= $item['name'] ?></a> 
		<? endforeach ?>
	<? endif ?>
</section>
