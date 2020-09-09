<?php
namespace Gino\App\Post;
/**
* @file archive.php
* @brief Template per la vista archivio post
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **controller**: object Gino.App.Post.post
* - **ctg**: mixed, categoria @ref Gino.App.Post.Category o null
* - **tag**: string, tag post
* - **items**: array, oggetti di tipo @ref Gino.App.Post.Item
* - **feed_url**: string, url ai feed RSS
* - **pagination**: html, paginazione
* - **search_form**: html, form di ricerca
* - **link_form**: html
*/
?>
<? //@cond no-doxygen ?>
<section id="archive-post-<?= $instance_name ?>">
    <h1>
        <?= _('Archivio post') ?> 
        <? if($ctg): ?>
        - <?= \Gino\htmlChars($ctg->ml('name')); ?>
        <? endif ?>
        <? if($tag): ?>
        - tag <?= \Gino\htmlChars($tag); ?>
        <? endif ?>
        <a style="margin-left: 20px" class="fa fa-rss" href="<?= $feed_url ?>"></a> 
        <?= $link_form ?>
    </h1>
    <?= $search_form ?>
    
    <? if(count($items)): ?>
        <? foreach($items as $n): ?>
        <article>
        	<h1><a href="<?= $n->getUrl() ?>"><?= \Gino\htmlChars($n->ml('title')) ?></a></h1>
        	
            <div class="row">
                <? if($n->img): ?>
                	<? $image = new \Gino\GImage(\Gino\absolutePath($n->getImgPath())); $thumb = $image->thumb(500, 500); ?>
            		<div class="col-sm-3 col-xs-12">
            			<a href="<?= $thumb->getPath() ?>" data-toggle="lightbox" data-gallery="gallery">
            				<img src="<?= $thumb->getPath() ?>" class="img-fluid rounded" />
            			</a>
            		</div>
            		<div class="col-sm-9 col-xs-12">
        		<? else: ?>
            		<div class="col-sm-12">
        		<? endif ?>
        		
        		<div class="tags"><time><?= \Gino\dbDateToDate($n->date) ?></time> <?= \Gino\GTag::viewTags($controller, $n->tags) ?></div>
                <?= \Gino\cutHtmlText(\Gino\htmlChars($n->ml('text')), 300, '...', false, false, true, array('endingPosition' => 'in')) ?>
			</div>
        </article>
        <? endforeach ?>
        <?= $pagination ?>
    <? else: ?>
        <p><?= _('Non risultano post') ?></p>
    <? endif ?>
</section>
<script>
$(document).on("click", '[data-toggle="lightbox"]', function(event) {
	event.preventDefault();
	$(this).ekkoLightbox();
});
</script>
<? // @endcond ?>
