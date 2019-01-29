<?php
namespace Gino;
/**
* @file paginator_pagination.php
* @brief Template completo paginazione, include navigazione pagine e sommario
*
* Variabili disponibili:
* - **summary**: string, sommario
* - **summary_label**: string, label del sommario
* - **navigator**: html, controller di navigazione pagine @ref paginator_navigator.php
* - **gotopage**: html, interfaccia di invio a una pagina specifica
* 
* @copyright 2014-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<div class="paginator">
<?php if($gotopage): ?>
	<div class="gotopage">
		<a><?php if($summary_label): ?><?= $summary_label.' ' ?><?php endif ?> 
		<?= $summary ?></a>
	</div>
	
	<div class=""><?= $navigator ?></div>
	<div class=""><a><?= $gotopage ?></a></div>
 <? else: ?>
 	<div class=""><?= $navigator ?></div>
	<div class=""><a><?= $summary ?></a></div>
<?php endif ?>
</div>
<? // @endcond ?>
