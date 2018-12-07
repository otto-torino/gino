<?php
namespace Gino;
/**
* @file paginator_navigator.php
* @brief Template dei controller per la navigazione delle pagine, paginazione
*
* Variabili disponibili:
* - **pages**: array di pagine, ciascun elemento puo' essere:
*              - un array del tipo array(numero_pagina => url)
*              - un array del tipo array(numero_pagina => null) che indica la pagina attiva
*              - una stringa, i puntini (...) che separano pagine non consecutive
* - next: url pagina successiva, null se inesistente
* - prev: url pagina precedente, null se inesistente
* - **additional_classes**: string
*
* @copyright 2014-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<nav aria-label="Page navigation">
	<ul class="pagination <?= $additional_classes ?>">
		<?php if($prev): ?>
		<li class="page-item">
			<a class="page-link" href="<?= $prev ?>" aria-label="Previous">
			<span aria-hidden="true">&laquo;</span>
			<span class="sr-only">Previous</span>
			</a>
		</li>
		<?php endif ?>
		
		<?php foreach($pages as $page): ?>
    	<li class="page-item <?= is_array($page) && is_null($page[1]) ? 'active' : null ?>">
    		<?php if(is_array($page)): ?>
    			<!-- define href -->
				<?php if(is_null($page[1])) { $href=null; } else { $href="href=\"".$page[1]."\""; } ?>
				
				<a class="page-link" <?= $href ?>><?= $page[0] ?></a>
			<?php else: ?>
				<a class="page-link"><?= $page ?></a>
			<?php endif ?>
        </li>
        <?php endforeach ?>
        
        <?php if($next): ?>
		<li class="page-item">
			<a class="page-link" href="<?= $next ?>" aria-label="Next">
			<span aria-hidden="true">&raquo;</span>
			<span class="sr-only">Next</span>
			</a>
		</li>
		<?php endif ?>
	</ul>
</nav>
<? // @endcond ?>
