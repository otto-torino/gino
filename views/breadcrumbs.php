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
		<? for($i=0, $end=count($items); $i<$end; $i++): ?>
			<div class="inline">
			<? if(array_key_exists('link', $items[$i]) and $items[$i]['link']): ?>
				<a href="<?= $items[$i]['link'] ?>"><?= $items[$i]['label'] ?></a>
			<? else: ?>
				<?= $items[$i]['label'] ?>
			<? endif ?>
			
			<? if($i<$end-1): ?>
				>
			<? endif ?>
			</div>
		<? endfor ?>
		<div class="null"></div>
	</section>
<? endif ?>
<? // @endcond ?>
