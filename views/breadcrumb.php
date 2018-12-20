<?php
namespace Gino;
/**
* @file breadcrumb.php
* @brief Template per la vista delle briciole di pane
*
* Variabili disponibili:
* - **id**: string, valore identificativo
* - **items**: array
*
* @version 1.0.0
* @copyright 2016-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @author Marco Guidotti guidottim@gmail.com
* @author abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<? if($items && count($items)): ?>
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb">
		<? for($i=0, $end=count($items); $i<$end; $i++): ?>
			<? if($i == ($end-1)) { $class = 'active'; } else { $class = ''; } ?>
			<li class="breadcrumb-item <?= $class ?>">
			<? if(array_key_exists('link', $items[$i]) and $items[$i]['link']): ?>
				<a href="<?= $items[$i]['link'] ?>"><?= $items[$i]['label'] ?></a>
			<? else: ?>
				<?= $items[$i]['label'] ?>
			<? endif ?>
			</li>
		<? endfor ?>
		</ol>>
	</nav>
<? endif ?>
<? // @endcond ?>