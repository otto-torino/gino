<?php
/**
* @file breadcrumbs.php
* @brief Template per la vista delle briciole di pane
*
* Variabili disponibili:
* - **id**: string, valore identificativo
* - **items**: array
*
* @version 1.0.0
* @copyright 2016 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? namespace Gino; ?>
<? //@cond no-doxygen ?>
<? if($items && count($items)): ?>
	<section id="breadcrumbs">
		<? foreach($items as $item): ?>
			<div class="inline">
			<? if(array_key_exists('link', $item) and $item['link']): ?>
				<a href="<?= $item['link'] ?>"><?= $item['label'] ?></a>
			<? else: ?>
				<?= $item['label'] ?>
			<? endif ?>
			</div>
		<? endforeach ?>
		<div class="null"></div>
	</section>
<? endif ?>
<? // @endcond ?>
