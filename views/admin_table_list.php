<section>
<header>
<h1 class="left"><?= $title ?></h1>
<?php if($link_insert): ?>
	<div class="right"><?= $link_insert ?></div>
<?php endif ?> 
<div class="null"></div>
</header>
<?php if($form_filters): ?>
<div class="right" style="width:30%">
<h2><?= $form_filters_title ?></h2>
<?= $form_filters ?>
</div>
<div class="left" style="width:68%">
<?php endif ?>
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
</div>
<div class="null"></div>
<?php endif ?>
</section>
