<?php
namespace Gino\App\Page;
/**
* @file showcase.php
* @brief Template per la vista vetrina
*
* Variabili disponibili:
* - **title**: titolo della vista
* - **feed_url**: link ai feed rss
* - **wrapper_id**: attributo id del wrapper che contiene gli elementi della vetrina
* - **items**: elementi della vetrina   
* - **ctrls**: controlli della vetrina (per lo slide)
* - **options**: opzioni da passare alla classe javascript che gestisce lo slider
*
* @copyright 2012-2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<section id="showcasepages">
<header>
    <h1 class="left"><?= \Gino\htmlChars($title) ?></h1>
    <div class="right feed">
        <a href="<?= $feed_url ?>" class="fa fa-rss"></a>
    </div>
    <div class="null"></div>
</header>
<? if(count($items)): ?>
    <div id="page-showcase-wrapper">
    <? foreach($items as $item): ?>
        <?= $item ?>
    <? endforeach ?>
    </div>
    <table>
        <tr>
        <? foreach($ctrls as $ctrl): ?>
            <td><?= $ctrl ?></td>
        <? endforeach ?>
        </tr>
    </table>
    <script type="text/javascript">
	var pageslider;
    (function() {
    	window.addEvent('load', function() {
            pageslider = new PageSlider('page-showcase-wrapper', 'sym_page_', <?= $options ?>);
        });
    })()
    </script>
<? else: ?>
<p><?= _('Non risultano elementi registrati') ?></p>
<? endif ?>
</section>
<? // @endcond ?>
