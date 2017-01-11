<?php
/**
 * @file sidenav.php
 * @brief Template per la vista sidenav
 *
 * Variabili disponibili:
 * - **sysmdls**: array
 * - **mdls**: array
 * - **ctrl**: object, controller
 * - **fas**: array, elenco delle applicazioni installate
 * - **hide**: array, elenco delle applicazioni da non mostrare
 * - **openclose**: bool, TRUE se la barra è a scomparsa
 *
 * @version 1.0.0
 * @copyright 2017 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>

<script type="application/javascript">
<!-- Set the width of the side navigation to 250px -->
function openSNav() {
    document.getElementById("mySidenav").style.width = "100px";
}

<!-- Set the width of the side navigation to 0 -->
function closeSNav() {
    document.getElementById("mySidenav").style.width = "0";
}
</script>

<div id="mySidenav" class="sidenav">

	<? if($openclose): ?>
		<a href="javascript:void(0)" class="closebtn" onclick="closeSNav()">&times;</a>
	<? endif ?>
	
	<? foreach($sysmdls as $sm): ?>
		<? if(!in_array($sm['name'], $hide)): ?>
			<a href="<?= $ctrl->link($sm['name'], 'manage'.ucfirst($sm['name']))?>" title="<?= $sm['label'] ?>">
				<i class="fa fa-<?= $fas[$sm['name']] ?>"></i>
			</a>
		<? endif ?>
	<? endforeach ?>
	
	<? foreach($mdls as $m): ?>
		<? if(!in_array($m['name'], $hide)): ?>
			<a href="<?= $ctrl->link($m['name'], 'manageDoc')?>" title="<?= $m['label'] ?>">
				<i class="fa fa-<?= $fas[$m['name']] ?>"></i>
			</a>
		<? endif ?>
	<? endforeach ?>
</div>

<? if($openclose): ?>
	<!-- Open the sidenav -->
	<span onclick="openSNav()"><i style="cursor: pointer; margin-left:20px;" class="fa fa-bars fa-2x"></i></span>
<? else: ?>
	<script>$('mySidenav').setStyle('width', '80px');</script>
<? endif ?>