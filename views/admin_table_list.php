<section class="admin">
  <header>
    <h1 class="left"><?= $title ?></h1>
    <?php if($form_filters): ?>
      <div class="right"><span id="search_icon" class="link"><?= $search_icon ?></span></div>
      <div id="filter_form_container">
        <div id="filter_form_layer">
          <h2><?= $form_filters_title ?></h2>
          <?= $form_filters ?>
        </div>
      </div>
    <?php endif ?> 
    <?php if($link_insert): ?>
      <div class="right"><?= $link_insert ?></div>
    <?php endif ?> 
    <div class="null"></div>
  </header>
  <div>
    <?= $description ?>
  </div>
  <?= $table ?>
  <?php if(!$tot_records): ?>
    <p><?= _("Non sono stati trovati elementi") ?></p>
  <?php endif ?>
  <div class="pagination">
    <div class="left">
      <?= $pnavigation ?>
    </div>
    <div class="right">
      <?= $psummary ?>
    </div>
    <div class="null"></div>
  </div>
  <?php if($form_filters): ?>
  <script type="text/javascript">
    (function() {
      var closed = true;
      var layer = $('filter_form_layer');
      var myFx = new Fx.Morph(layer);
      var coords = layer.getCoordinates();
      var fw = coords.width;
      var fh = coords.height;
      layer.setStyles({
        width: 0,
        height: 0,
      })
      
      $('search_icon').addEvent('click', function() {
        if(closed) {
          layer.style.visibility = 'visible';
          myFx.start({
            width: [0, fw],
            height: [0, fh]
          })
          layer.setStyle('box-shadow', '0px 0px 5px #000');
          closed = false;
        }
        else {
          myFx.start({
            width: [fw, 0],
            height: [fh, 0]
          }).chain(function() {
            layer.style.visibility = 'hidden';
          })
          closed = true;
        }
      })
    })()
  </script>
  <?php endif ?>
</section>
