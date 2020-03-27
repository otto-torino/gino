<?php
namespace Gino\App\Post;
/**
* @file showcase.php
* @brief Template per la vista vetrina post
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **items**: array, oggetti di tipo @ref Gino.App.Post.Item
* - **feed_url**: string, url ai feed RSS
* - **archive_url**: string, url alla pagina di elenco dei post
* - **autointerval**: int, intervallo animazione (ms)
*                   'false', stop the items from automatically sliding
*/
?>
<? //@cond no-doxygen ?>
<section id="showcase-post-<?= $instance_name ?>">
    <h1>
        <?= _('Post') ?>
        <? if($feed_url): ?>
            <a href="<?= $feed_url ?>" class="fa fa-rss"></a>
        <? endif ?>
    </h1>
    <div id="carousel-post-<?= $instance_name ?>" class="carousel slide" data-ride="carousel" data-interval="<?= $autointerval ?>">
        <? $tot = count($items); 
        $indicators = "<ol class=\"carousel-indicators\">";
        $i = 0; ?>
        
        <div class="carousel-inner">
        <? foreach($items as $n): ?>
        	<? 
        	if($i == 0) $active = 'active'; else $active = '';
        	$indicators .= "<li data-target=\"#carousel-post-".$instance_name."\" data-slide-to=\"$i\" class=\"$active\"></li>"; ?>
        	
        	<div class="carousel-item <?= $active ?>">
                <article>
                    <h1><a href="<?= $n->getUrl() ?>"><?= \Gino\htmlChars($n->ml('title')) ?></a></h1>
                    <?= \Gino\htmlChars(\Gino\cutHtmlText($n->ml('text'), 150, '...', false, false, true, array('endingPosition'=>'in'))) ?>
                </article>
            </div>
            <? $i++; ?>
        <? endforeach ?>
        </div>
        <? $indicators .= "</ol>"; echo $indicators; ?>
        
        <!-- Controls -->
        <a class="carousel-control-prev" href="#carousel-post-<?= $instance_name ?>" role="button" data-slide="prev">
    	<span class="carousel-control-prev-icon" aria-hidden="true"></span>
    	<span class="sr-only">Previous</span>
  		</a>
  		<a class="carousel-control-next" href="#carousel-post-<?= $instance_name ?>" role="button" data-slide="next">
    	<span class="carousel-control-next-icon" aria-hidden="true"></span>
    	<span class="sr-only">Next</span>
  		</a>
    </div>
    
    <div class="right"><a class="btn btn-primary" href="<?= $archive_url ?>"><?= _("elenco completo") ?></a></div>
</section>
<? // @endcond ?>
