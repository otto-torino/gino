<?php
namespace Gino\App\Post;
/**
* @file detail.php
* @ingroup gino-post
* @brief Template per la vista dettaglio post
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **controller**: \Gino\App\Post\post
* - **item**: \Gino\App\Post\Item istanza di @ref Gino.App.Post.Item
* - **related_contents_list**: html, lista di link a risorse correlate
* - **social**: html, bottoni per lo share sui social
* - **breadcrumbs**: string
*/
?>
<? //@cond no-doxygen ?>
<!-- Breadcrumbs -->
<? if($breadcrumbs): ?>
	<?= $breadcrumbs ?>
<? endif ?>

<section itemscope itemtype="http://www.schema.org/NewsArticle" id="detail-post-<?= $instance_name ?>">
    <div class="row">
        <div class="col-md-12">
            <h1 itemprop="name"><?= \Gino\htmlChars($item->ml('title')) ?></h1>
            <p><time itemprop="datePublished" content="<?= $item->dateIso() ?>" pubdate="pubdate" datetime="<?= $item->dateIso() ?>"><?= \Gino\dbDateToDate($item->date) ?></time></p>
            <? if($item->objCategories()): ?>
                <p><span class="fa fa-cubes"></span> 
                <? $router = \Gino\Router::instance(); ?>
                <? foreach($item->objCategories() as $ctg): ?>
                	<a href="<?= $router->link($item->getController()->getInstanceName(), 'archive', array('ctg' => $ctg->slug)) ?>"><?= $ctg->ml('name') ?></a> 
                <? endforeach ?>
                </p>
            <? endif ?>
            <? if($item->tags): ?>
                <p class="tags"><span class="fa fa-tag"></span> <?= \Gino\GTag::viewTags($controller, $item->tags) ?></p>
            <? endif ?>
        </div>
    </div>
    <div class="row">
        <? if($item->img): ?>
            <div class="col-sm-4 col-xs-12">
                <a href="<?= $item->getImgPath() ?>" data-toggle="lightbox" data-gallery="gallery">
                	<img src="<?= $item->getImgPath() ?>" class="img-fluid rounded" alt="<?= _('immagine') ?>" />
            	</a>
            </div>
            <div class="col-sm-8 col-xs-12">
        <? else: ?>
            <div class="col-sm-12">
        <? endif ?>
        <div itemprop="articleBody">
            <?= \Gino\htmlChars($item->ml('text')) ?>
        </div>
        <? if($item->social): ?>
            <?= $social ?>
        <? endif ?>
        <? if($related_contents_list): ?>
            <h2><?= _('Potrebbe interessarti anche...') ?></h2>
            <?= $related_contents_list ?>
        <? endif ?>
        </div>
    </div>
</section>
<script>
$(document).on("click", '[data-toggle="lightbox"]', function(event) {
	event.preventDefault();
	$(this).ekkoLightbox();
});
</script>
<? // @endcond ?>
