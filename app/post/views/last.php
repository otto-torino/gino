<?php
namespace Gino\App\Post;

$registry = \Gino\Registry::instance();
$registry->addRawJs("<script src=\"https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.6.0/slick.js\"></script>");

/**
* @file last.php
* @brief Template per la vista ultimi post
*
* Variabili disponibili:
* - **instance_name**: string, nome istanza modulo
* - **items**: array, oggetti @ref Gino.App.Post.Item
* - **feed_url**: string, url feed rss
* - **archive_url**: string,  url archivio completo
* - **slideshow**: bool
* - **slideshow_items**: array, oggetti @ref Gino.App.Post.Item
*/
?>
<? //@cond no-doxygen ?>
<section id="last-post-<?= $instance_name ?>">
    <h1>Ultimi post <a class="fa fa-rss" href="<?= $feed_url ?>"></a></h1>
    
    <? if($slideshow && count($slideshow_items) > 1): ?>
    <div class="show-post-<?= $instance_name ?> slider">
    
    	<? foreach($slideshow_items as $slide): ?>
    	<div class="slide">
    		<? if($slide->img && file_exists(\Gino\absolutePath($slide->getImgPath()))): ?>
    			<? $image = new \Gino\GImage(\Gino\absolutePath($slide->getImgPath())); $thumb = $image->thumb(100, 100); ?>
    			<img class="left" style="margin: 0 10px 10px 0" src="<?= $thumb->getPath() ?>" />
    		<? endif ?>
            <div class="title"><a href="<?= $slide->getUrl() ?>"><?= \Gino\htmlChars($slide->ml('title')) ?></a></div>
            <time><?= \Gino\dbDateToDate($slide->date) ?></time>
            <?= \Gino\cutHtmlText(\Gino\htmlChars($slide->ml('text')), 80, '...', false, false, true, array('endingPosition' => 'in')) ?>
    	</div>
    	<? endforeach; ?>
	</div>
<script type="text/javascript">
$(document).ready(function() {
    $('.show-post-<?= $instance_name ?>').slick({
        slidesToShow: 6,
        slidesToScroll: 1,
        autoplay: true,
        autoplaySpeed: 1500,
        arrows: false,
        dots: false,
        pauseOnHover: false,
        responsive: [{
            breakpoint: 768,
            settings: {
                slidesToShow: 4
            }
        }, {
            breakpoint: 520,
            settings: {
                slidesToShow: 3
            }
        }]
    });
});
</script>
    <? endif ?>
    
    <? if(count($items)): ?>
        <? foreach($items as $n): ?>
            <article>
                <h1><a href="<?= $n->getUrl() ?>"><?= \Gino\htmlChars($n->ml('title')) ?></a></h1>
                <time><?= \Gino\dbDateToDate($n->date) ?></time>
                <? if($n->img && file_exists(\Gino\absolutePath($n->getImgPath()))): ?>
                    <? $image = new \Gino\GImage(\Gino\absolutePath($n->getImgPath())); $thumb = $image->thumb(100, 100); ?>
                    <img class="left" style="margin: 0 10px 10px 0" src="<?= $thumb->getPath() ?>" />
                <? endif ?>
                <?= \Gino\cutHtmlText(\Gino\htmlChars($n->ml('text')), 80, '...', false, false, true, array('endingPosition' => 'in')) ?>
                <div class="null"></div>
            </article>
        <? endforeach ?>
        <div class="right"><a class="btn btn-primary" href="<?= $archive_url ?>"><?= _("elenco completo") ?></a></div>
    <? else: ?>
        <p><?= _('Non risultano post') ?></p>
    <? endif ?>
</section>


<? // @endcond ?>
