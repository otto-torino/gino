<?php
namespace Gino;
/**
 * @file admin_table_list.php
 * @brief Template della lista record in area amministrativa
 *
 * Variabili disponibili:
 * - **title**: string, titolo.
 * - **search_icon**: html, icona ricerca
 * - **form_filters_title**: string, titolo form filtri
 * - **form_filters**: html, form filtri
 * - **link_insert**: html, link inserimento nuovo record
 * - **link_export**: html, link esportazione record
 * - **link_modal**: string, indirizzo della modale per l'esportazione dei dati
 * - **model_name**: string, nome del modello completo di namespace
 * - **description**: html, testo informativo
 * - **table**: html, tabella con i record ed i bottoni di manipolazione
 * - **tot_records**: int, numero di record
 * - **pagination**: html, paginazione (sommario e navigazione)
 *
 * @copyright 2014-2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? //@cond no-doxygen ?>
<section class="admin">
	<header>
		<h1 class="left"><?= $title ?></h1>
		<?php if($form_filters): ?>
			<div class="right"> &#160; <span id="search_icon" class="link"><?= $search_icon ?></span></div>
			<div id="filter_form_container">
				<div id="filter_form_layer">
					<h2><?= $form_filters_title ?></h2>
					<?= $form_filters ?>
				</div>
			</div>
		<?php endif ?>
		<?php if($link_insert || $link_export || $link_modal): ?>
			<div class="right">
			<?php if($link_modal): ?>
				<!-- Open Modal -->
				<a href="<?= $link_modal ?>" class="modal-overlay icon fa fa-download fa-2x icon-tooltip" 
				title="<?= _("esportazione dati") ?>" 
				data-type="ajax" 
				data-overlay="false" 
				data-title="<?= sprintf(_("Esportazione dei dati del modello %s"), $model_name) ?>">
				</a>&nbsp;
			<?php endif ?>
        	<?php if($link_export): ?>
            	<?= $link_export ?>&nbsp;
        	<?php endif ?>
			<?php if($link_insert): ?>
				<?= $link_insert ?>
			<?php endif ?>
			</div>
		<?php endif ?>
		<div class="null"></div>
	</header>
	
	<? if($description): ?>
		<div class="backoffice-info">
			<?= $description ?>
		</div>
	<? endif ?>
	<?= $table ?>
	
	<?php if(!$tot_records): ?>
		<p><?= _("Non sono stati trovati elementi") ?></p>
	<?php endif ?>
	<?= $pagination ?>
	
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
            width: [0, fw + 50],
            height: [0, fh],
            opacity: [0, 1]
          })
          layer.setStyle('box-shadow', '0px 0px 2px #aaa');
          closed = false;
        }
        else {
          myFx.start({
            width: [fw + 50, 0],
            height: [fh, 0],
            opacity: [1, 0]
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
<? // @endcond ?>
