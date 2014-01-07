<? if(!isset($link_position)) $link_position = 'right'; ?>
<section class="tab-container" id="<?= isset($id) ? 'tab-'.$id : '' ?>">
  <h1><?= $title ?></h1>
  <div class="tab-top">
    <div class="<?= $link_position == 'left' ? 'right' : 'left' ?>">
    <? if(isset($tab_title)): ?>
      <h1 class="tab-title <?= $link_position == 'left' ? 'tab-img-right' : 'tab-img-left' ?>"><?= $tab_title ?></h1>
    <? endif ?>
    </div>
    <div class="<?= $link_position == 'left' ? 'left' : 'right' ?>">
      <? if(!is_array($links)) $links = array($links); ?>
      <? foreach($links as $link): ?>
        <div class="tab-ext <?= $link_position == 'left' ? 'left' : 'right' ?><?= $selected_link == $link ? ' tab-ext-selected' : '' ?>">
          <div class="tab-int<?= $selected_link == $link ? ' tab-int-selected' : '' ?>">
            <?= $link ?>
          </div>
        </div>
      <? endforeach ?>
    </div>
    <div class="clear"></div>
  </div>
  <div class="tab-content">
    <?= $content ?>
  </div>
</section>
