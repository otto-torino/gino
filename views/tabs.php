<?php
namespace Gino;
/**
* @file tabs.php
* @brief Template che stampa una tab html
*
* Variabili disponibili:
* - **link_position**: string, left|right. Posizione dei link.
* - **id**: string. Attributo id del container.
* - **title**: string. Titolo pagina.
* - **tab_title**: string. Titolo tab.
* 
* - **links**: array, array di array che comprendono i riferimenti dei link; questi ultimi elementi hanno le chiavi: 
*             @a link (string), @a label (string), @a disabled (bool)
* - **selected_link**: array, link selezionato da confrontare con gli elementi di links.
* - **content**: html, contenuto della tab selezionata
*
* @copyright 2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<? if(!isset($link_position)) $link_position = 'right'; ?>

<section class="tab-container" id="<?= isset($id) ? 'tab-'.$id : '' ?>">
	<h1><?= $title ?></h1>
	
	<ul class="nav nav-tabs">
	<? if(is_array($links) && count($links)): ?>
	<? foreach($links as $link): ?>
		<? if(isset($link['disabled']) && $link['disabled']) $disabled_class = 'disabled'; else $disabled_class = null; ?>
		<? if($selected_link['link'] == $link['link']) $active_class = 'active'; else $active_class = null; ?>
		
        <li class="nav-item">
        	<a class="nav-link <?= $active_class ?> <?= $disabled_class ?>" href="<?= $link['link'] ?>">
        		<?= $link['label'] ?>
        	</a>
        </li>
	<? endforeach ?>
	</ul>
	<? endif; ?>
	
	<div class="tab-content">
		<?= $content ?>
	</div>
</section>
<? // @endcond ?>
