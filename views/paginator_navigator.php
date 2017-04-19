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
*
* @copyright 2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<ul class="pagination">
<?php if($prev): ?>
    <li><a href="<?= $prev ?>">«</a></li>
<?php endif ?>
<?php foreach($pages as $page): ?>
    <?php if(is_array($page)): ?>
        <li<?= is_null($page[1]) ? ' class="active"' : '' ?>>
            <?php if(is_null($page[1])): ?>
                <a style="cursor: auto;"><?= $page[0] ?></a>
            <?php else: ?>
                <a href="<?= $page[1] ?>"><?= $page[0] ?></a>
            <?php endif ?>
        </li>
    <?php else: ?>
        <li><a style="cursor: auto"><?= $page ?></a></li>
    <?php endif ?>
<?php endforeach ?>
<?php if($next): ?>
    <li><a href="<?= $next ?>">»</a></li>
<?php endif ?>
</ul>
<? // @endcond ?>
