<?php
namespace Gino;
/**
* @file section.php
* @brief Template di una section
* 
* Variabili disponibili:
* - **class**: string, classe css della section
* - **id**: string, attributo id della section
* - **header_class**: string
* - **pre_header**: html, elementi da mostrare prima del titolo
* - **header_links**: array, array di link
* - **post_header**: html, elementi da mostrare dopo il titolo
* - **title**: string, titolo section
* - **content**: html, contenuto
* - **footer**: html, contenuto del footer della section
*
* @copyright 2013-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<section class="gino-container <?= isset($class) ? $class : '' ?>" id="<?= isset($id) ? $id : '' ?>">
	<? if(isset($pre_header)): ?><?= $pre_header ?><? endif ?>
	
	<div class="gino-container-header <?= isset($header_class) ? $header_class : '' ?>">
		<? if(isset($title)): ?>
			<h1><?= $title ?></h1>
		<? endif ?>
		
		<? if(isset($header_links)): ?>
		<div class="gino-container-links">
			<?= (is_array($header_links)) ? implode(" ", $header_links) : $header_links ?>
		</div>
		<? endif ?>
	</div>
	
	<? if(isset($post_header)): ?><?= $post_header ?><? endif ?>
    
	<div class="container"><?= $content ?></div>
	<? if(isset($footer)): ?>
		<footer><?= $footer ?></footer>
	<? endif ?>
</section>
<? // @endcond ?>
