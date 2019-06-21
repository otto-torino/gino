<?php
/**
 * @file sidebar.php
 * @brief Template per la vista sidebar
 * 
 * Variabili disponibili:
 * - **sysmdls**: array
 * - **mdls**: array
 * - **ctrl**: object, controller
 * - **fas**: array, elenco delle applicazioni installate
 * - **hide**: array, elenco delle applicazioni da non mostrare
 * 
 * @version 1.0.0
 * @copyright 2017-2018 Otto srl MIT License http://www.opensource.org/licenses/mit-license.php
 * @authors Marco Guidotti guidottim@gmail.com
 * @authors abidibo abidibo@gmail.com
 */
?>
<nav id="sidebar">
	<div class="sidebar-header">
		<h3>Settings Sidebar</h3>
	</div>
	
	<!-- https://bootstrapious.com/p/bootstrap-sidebar -->
	
	<ul class="list-unstyled components">
        <!-- 
        <li>
            <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Pages</a>
                <ul class="collapse list-unstyled" id="pageSubmenu">
                    <li>
         -->
    <? foreach($sysmdls as $sm): ?>
		<? if(!in_array($sm['name'], $hide)): ?>
		<li class="nav-item">
			<? if (array_key_exists($sm['name'], $fas)): ?>
				<i class="fa fa-<?= $fas[$sm['name']] ?>"></i> 
				<a href="<?= $ctrl->link($sm['name'], 'manage'.ucfirst($sm['name']))?>" title="<?= $sm['label'] ?>"><?= $sm['label'] ?></a>
			<? else: ?>
				<i class="fa fa-question"></i>
			<? endif ?>
		</li>
		<? endif ?>
	<? endforeach ?>
	
	<? foreach($mdls as $m): ?>
		<? if(!in_array($m['name'], $hide)): ?>
		<li class="nav-item">
			<? if (array_key_exists($m['name'], $fas)): ?>
				<i class="fa fa-<?= $fas[$m['name']] ?>"></i> 
				<a href="<?= $ctrl->link($m['name'], 'manageDoc')?>" title="<?= $m['label'] ?>"><?= $m['label'] ?></a>
			<? else: ?>
				<i class="fa fa-question"></i>
			<? endif ?>
		</li>
		<? endif ?>
	<? endforeach ?>
	</ul>
</nav>
<script>
(function($) {
	$(document).ready(function () {
		$('#sidebarCollapse').on('click', function () {
			$('#sidebar').toggleClass('active');
		});
	});
})(jQuery)
</script>
