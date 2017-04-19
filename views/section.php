<?php
namespace Gino;
/**
* @file section.php
* @brief Template di una section
*
* Variabili disponibili:
* - **class**: string, classe css della section
* - **id**: string, attributo id della section
* - **pre_header**: html, elementi da mostrare prima del titolo
* - **header_links**: array, array di link
* - **post_header**: html, elementi da mostrare dopo il titolo
* - **header_class**: string, classe css del tag h1
* - **title**: string, titolo section
* - **content**: html, contenuto
* - **footer**: html, contenuto del footer della section
*
* @copyright 2013-2014 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
* @authors Marco Guidotti guidottim@gmail.com
* @authors abidibo abidibo@gmail.com
*/
?>
<? //@cond no-doxygen ?>
<section class="<?= isset($class) ? $class : '' ?>" id="<?= isset($id) ? $id : '' ?>">
  <? if(isset($pre_header) or isset($header_links) or isset($post_header)): ?>
    <header>
  <? endif ?>
  <? if(isset($pre_header)): ?><?= $pre_header ?><? endif ?>
  <? if(isset($header_links)): ?>
    <h1 class="headerInside left<?= isset($header_class) ? ' '.$header_class : '' ?>"><?= $title ?></h1>
    <div class="headerInside right"><?= (is_array($header_links)) ?implode(" ", $header_links) : $header_links ?></div>
    <div class="clear"></div>
  <? else: ?>
  <? if(isset($title)): ?><h1 class="<?= isset($header_class) ? $header_class : '' ?>"><?= $title ?></h1><? endif ?>
  <? endif ?>
  <? if(isset($post_header)): ?><?= $post_header ?><? endif ?>
  <? if(isset($pre_header) or isset($header_links) or isset($post_header)): ?>
    </header>
  <? endif ?>
  <div class="section_body"><?= $content ?></div>
  <? if(isset($footer)): ?>
    <footer><?= $footer ?></footer>
  <? endif ?>
</section>
<? // @endcond ?>
