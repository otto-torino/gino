<?php
namespace Gino;
/**
* @file related_contents_list.php
* @brief Template che stampa la lista di contenuti correlati
*
* Variabili disponibili:
* - **related_contents**: array. Un array associativo $content_type => $links dove:
*                                - content_type: string, tipo di contenuto (pagine, articoli, etc.)
*                                - links: array, array di link completi di tag a
*/
?>
<? //@cond no-doxygen ?>
<ul class="related_contents">
    <? foreach($related_contents as $content_type => $links): ?>
        <li>
            <?= $content_type ?>
            <ul>
                <? $i = 0; ?>
                <? foreach($links as $link): ?>
                    <? if($i < 3): ?>
                        <li><?= $link ?></li>
                        <? $i++ ?>
                    <? endif ?>
                <? endforeach ?>
            </ul>
        </li>
    <? endforeach ?>
</ul>
<? // @endcond ?>
