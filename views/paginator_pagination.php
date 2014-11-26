<?php
/**
* @file paginator_pagination.php
* @brief Template completo paginazione, include navigazione pagine e sommario
*
* Variabili disponibili:
* - **summary**: string, sommario
* - **navigator**: html, controller di navigazione pagine @ref paginator_navigator.php
*
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? namespace Gino\App\News; ?>
<? //@cond no-doxygen ?>
    <div class="paginator">
        <div class="left"><?= $navigator ?></div>
        <div class="right"><ul class="pagination"><li><a><?= $summary ?></a></li></ul></div>
        <div class="clear"></div>
    </div>
<? // @endcond ?>
