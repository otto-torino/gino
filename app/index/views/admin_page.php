<?php
/**
 * @file admin_page.php
 * @brief Template per la vista home page amministrazione
 *
 * Variabili disponibili:
 * - **sysmdls**: array, elenco dei moduli di sistema
 * - **mdls**: array, elenco dei moduli non di sistema
 * - **ctrl**: object, controller
 * - **fas**: array, elenco delle applicazioni installate
 * - **hide**: array, elenco delle applicazioni da non mostrare
 * - **view_hidden_apps**: bool, per visualizzare le applicazioni nascoste
 *
 * @version 1.0.0
 * @copyright 2017-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<? $card_style = [
    'text-white bg-primary',
    'text-white bg-secondary',
    'text-white bg-success',
    'text-white bg-danger',
    'text-white bg-warning',
    'text-white bg-info',
    //'bg-light',
    'text-white bg-dark',
];
$cs_count = count($card_style); ?>
<section class="admin-home">
<div class="row">
    <!-- <h2>Moduli di sistema</h2> -->
    <div class="col-md-8">
    	<div class="row">
    	
    	<? $cs=0; $hide_list=array(); ?>
        <? foreach($sysmdls as $sm): ?>
			<? if(!in_array($sm['name'], $hide)): ?>
				
				<?php if($cs >= $cs_count) {$cs = 0;} ?>
				<?php $class_card = $card_style[$cs]; $cs++; ?>
            	<div class="card text-center admin-app-card <?= $class_card ?>" style="max-width: 18rem;">
                    <!-- Graphic -->
					<? if (array_key_exists($sm['name'], $fas)): ?>
						<i class="card-img-top fa fa-<?= $fas[$sm['name']] ?> fa-3x"></i>
					<? else: ?>
						<?= _("impostare l'icona nel file configuration.php") ?>
					<? endif ?>
					
					<div class="card-body">
						<!-- Title -->
						<h5 class="card-title"><a href="<?= $ctrl->link($sm['name'], 'manage'.ucfirst($sm['name'])) ?>"><?= $sm['label'] ?></a></h5>
						<!-- Description -->
						<p class="card-text"><?= \Gino\htmlchars($sm['description']) ?></p>
					</div>
				</div>
			<? else: ?>
                <?php $hide_list[] = array('link' => $ctrl->link($sm['name'], 'manage'.ucfirst($sm['name'])), 'label' => $sm['label']); ?>
            <? endif ?>
		<? endforeach ?>
		</div>
	</div>
	
	<!-- <h2>Moduli istanziabili</h2> -->
    <div class="col-md-4">
    	<div class="row">
    	
        <? $cs=0; ?>
        <? foreach($mdls as $m): ?>
			<? if(!in_array($m['name'], $hide)): ?>
				
				<?php if($cs >= $cs_count) {$cs = 0;} ?>
				<?php $class_card = $card_style[$cs]; $cs++; ?>
            	<div class="card text-center admin-app-card <?= $class_card ?>" style="max-width: 18rem;">
                    <!-- Graphic -->
					<? if (array_key_exists($m['name'], $fas)): ?>
						<i class="card-img-top fa fa-<?= $fas[$m['name']] ?> fa-3x"></i>
					<? else: ?>
						<?= _("impostare l'icona nel file configuration.php") ?>
					<? endif ?>
					
					<div class="card-body">
						<!-- Title -->
						<h5 class="card-title"><a href="<?= $ctrl->link($m['name'], 'manageDoc') ?>"><?= $m['label'] ?></a></h5>
						<!-- Description -->
						<p class="card-text"><?= \Gino\htmlchars($m['description']) ?></p>
					</div>
				</div>
			<? else: ?>
                <?php $hide_list[] = array('link' => $ctrl->link($m['name'], 'manageDoc'), 'label' => $m['label']); ?>
            <? endif ?>
		<? endforeach ?>
		</div>
    </div>
</div>

<!-- Visualizzazione applicazioni nascoste -->
<? if (count($hide_list) and $view_hidden_apps): ?>
	<script type="application/javascript">
function openHideList() {
	var d = document.getElementById("hidelist").style.display;
	
	if(d == 'block') {
		$('hidelist').setStyle('display', 'none');
	}
	else {
		$('hidelist').setStyle('display', 'block');
	}
}
	</script>

	<div><span style="cursor: pointer;" onclick="openHideList()">+ <?= _("visualizza le applicazioni nascoste"); ?></span></div>
	<div id="hidelist" style="display: none;">
		<ul>
		<? foreach($hide_list as $hl): ?>
			<li><a href="<?= $hl['link'] ?>"><?= $hl['label'] ?></a></li>
		<? endforeach ?>
		</ul>
	</div>
<? endif ?>
</section>
