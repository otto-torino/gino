<?php
namespace Gino\App\Page;
/**
* @file last.php
* @brief Template per la vista ultime pagine
*
* Variabili disponibili:
* - **title**: titolo della vista
* - **feed_url**: link ai feed rss
* - **items**: pagine
*
* @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<section id="lastpages">
<header>
    <h1 class="left"><?= $title ?></h1>
    <div class="right feed">
        <a href="<?= $feed_url ?>" class="fa fa-rss"></a>
    </div>
    <div class="null"></div>
</header>
<? if(count($items)): ?>
    <? foreach($items as $item): ?>
        <?= $item ?>
    <? endforeach ?>
<? else: ?>
<p><?= _('Non risultano elementi registrati') ?></p>
<? endif ?>
</section>
<? // @endcond ?>
