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
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
 <?php if($gotopage): ?>
	<div class="paginator">
    	<div class="column-left"><ul class="pagination"><li><a>
    		<?php if($summary_label): ?>
    			<?= $summary_label.' ' ?>
    		<?php endif ?>
    		<?= $summary ?></a></li></ul></div>
    	<div class="column-center"><?= $navigator ?></div>
    	<div class="column-right"><ul class="pagination"><li><a><?= $gotopage ?></a></li></ul></div>
    	<div class="clear"></div>
	</div>
 <? else: ?>
 	<div class="paginator">
    	<div class="left"><?= $navigator ?></div>
    	<div class="right"><ul class="pagination"><li><a><?= $summary ?></a></li></ul></div>
    	<div class="clear"></div>
	</div>
 <? endif ?>
<? // @endcond ?>
