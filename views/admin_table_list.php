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
 * - **trigger_modal**: string, nome della classe di innesco della modale
 * - **render_modal**: string, modale
 * - **script_modal**: string, script che permette di visualizzare la modale
 * - **model_name**: string, nome del modello completo di namespace
 * - **description**: html, testo informativo
 * - **table**: html, tabella con i record ed i bottoni di manipolazione
 * - **tot_records**: int, numero di record
 * - **pagination**: html, paginazione (sommario e navigazione)
 * 
 * @copyright 2014-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? //@cond no-doxygen ?>
<section class="gino-admin">
	<div class="gino-admin-header">
		<h1><?= $title ?></h1>
		
		<div class="gino-admin-icons">
		<?php if($form_filters): ?>
			<div class="right">
				 &#160; <span class="link" data-toggle="collapse" href="#collapseFormFilter" 
				role="button" aria-expanded="false" aria-controls="collapseFormFilter" 
				id="collapseLink">
				<?= $search_icon ?>
				</span>
			</div>
		<?php endif ?>
		
		<?php if($link_insert || $link_export || $link_modal): ?>
			<div class="right">
			<?php if($link_modal): ?>
				<!-- Open Modal -->
				<span class="icon fa fa-download fa-2x <?= $trigger_modal ?> link"></span>&nbsp;
			<?php endif ?>
        	<?php if($link_export): ?>
            	<?= $link_export ?>&nbsp;
        	<?php endif ?>
			<?php if($link_insert): ?>
				<?= $link_insert ?>
			<?php endif ?>
			</div>
		<?php endif ?>
		</div>
	</div>
	
	<div class="container">
	<?php if($form_filters): ?>
		<div class="collapse" id="collapseFormFilter">
			<div class="card card-body">
				<h2><?= $form_filters_title ?></h2>
				<?= $form_filters ?>
			</div>
		</div>
	<? endif ?>
	
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
	
	</div>
</section>
<script>
(function($) {
	$('#collapseLink').click(function() {
		$('#collapseFormFilter').toggle();
	});
})(jQuery);
</script>

<?php if($link_modal): ?>
	<?= $render_modal ?>
	<?= $script_modal ?>
<?php endif ?>
<? // @endcond ?>
