<?php
namespace Gino\App\Post;
/**
* @file evidence.php
* @brief Template per la vista vetrina post
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **items**: array, oggetti di tipo @ref Gino.App.Post.Item
* - **autostart**: bool, opzione autostart
* - **autointerval**: int, intervallo animazione (ms)
*/
?>
<? //@cond no-doxygen ?>
<? $tot = count($items); ?>
<? if($tot): ?>
<section id="evidence-post-<?= $instance_name ?>">
    <h1 class="hidden">
        <?= _('Headlines') ?>
        <? if($feed_url): ?>
            <a href="<?= $feed_url ?>" class="fa fa-rss"></a>
        <? endif ?>
    </h1>
    
    <div id="carousel-evidence-post-<?= $instance_name ?>" class="carousel slide" data-ride="carousel" data-interval="<?= $autointerval ?>">
        <? $indicators = "<ol class=\"carousel-indicators\">";
        $i = 0; ?>
        
        <div class="carousel-inner">
        <? foreach($items as $n): ?>
        	<? 
        	if($i == 0) $active = 'active'; else $active = '';
        	$indicators .= "<li data-target=\"#carousel-evidence-post-".$instance_name."\" data-slide-to=\"$i\" class=\"$active\"></li>"; ?>
        	
        	<div class="carousel-item <?= $active ?>">
                <article>
                	<? if($n->getImgPath()): ?>
                	<img class="d-none d-sm-block left" src="<?= $n->getImgPath() ?>" />	<!-- Hidden only on xs -->
                	<? endif ?>
                	
                	<h1><a href="<?= $n->getUrl() ?>"><?= \Gino\htmlChars($n->ml('title')) ?></a></h1>
                    <div class="d-none d-lg-block">
                    	<?= \Gino\htmlChars(\Gino\cutHtmlText($n->ml('text'), 440, '...', false, false, true, array('endingPosition'=>'in'))) ?>
                  	</div>
                  	<div class="d-none d-md-block d-lg-none">	<!-- Visible only on md -->
                  		<?= \Gino\htmlChars(\Gino\cutHtmlText($n->ml('text'), 340, '...', false, false, true, array('endingPosition'=>'in'))) ?>
					</div>
                    <div class="d-none d-sm-block d-md-none">	<!-- Visible only on sm -->
                    	<?= \Gino\htmlChars(\Gino\cutHtmlText($n->ml('text'), 260, '...', false, false, true, array('endingPosition'=>'in'))) ?>
                    </div>
                    <div class="d-block d-sm-none">	<!-- Visible only on xs -->
                    	<?= \Gino\htmlChars(\Gino\cutHtmlText($n->ml('text'), 260, '...', false, false, true, array('endingPosition'=>'in'))) ?>
                    </div>
				</article>
            </div>
            <? $i++; ?>
        <? endforeach ?>
        </div>
        <? $indicators .= "</ol>"; echo $indicators; ?>
        
        <!-- Controls -->
        <a class="carousel-control-prev" href="#carousel-evidence-post-<?= $instance_name ?>" role="button" data-slide="prev">
    	<span class="carousel-control-prev-icon" aria-hidden="true"></span>
    	<span class="sr-only">Previous</span>
  		</a>
  		<a class="carousel-control-next" href="#carousel-evidence-post-<?= $instance_name ?>" role="button" data-slide="next">
    	<span class="carousel-control-next-icon" aria-hidden="true"></span>
    	<span class="sr-only">Next</span>
  		</a>
    </div>
</section>
<? endif ?>
<? // @endcond ?>
