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
