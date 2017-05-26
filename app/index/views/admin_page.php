<section class="admin-home">
<div class="row">
    <div class="col-md-6">
        <!-- <h2>Moduli di sistema</h2> -->
        <div class="row">
            <? $i=0; $hide_list=array(); ?>
            <? foreach($sysmdls as $sm): ?>
                <? if(!in_array($sm['name'], $hide)): ?>
                    <div class="col-md-6">
                    <div style="cursor: pointer" class="panel panel-danger" onclick="location.href='<?= $ctrl->link($sm['name'], 'manage'.ucfirst($sm['name']))?>'">
                            <div class="panel-heading">
                                <h3 class="panel-title"><a href="<?= $ctrl->link($sm['name'], 'manage'.ucfirst($sm['name'])) ?>"><?= $sm['label'] ?></a></h3>
                            </div>
                            <div class="panel-body text-center">
                                <p class="text-center">
                                    <i class="fa fa-<?= $fas[$sm['name']] ?> fa-3x"></i>
                                </p>
                                <div class="small"><?= \Gino\htmlchars($sm['description']) ?></div>
                            </div>
                        </div>
                    </div>
                    <? if (++$i%2 == 0): ?>
                        <div class="clearfix"></div>
                    <? endif ?>
                <? else: ?>
                	<?php $hide_list[] = array('link' => $ctrl->link($sm['name'], 'manage'.ucfirst($sm['name'])), 'label' => $sm['label']); ?>
                <? endif ?>
            <? endforeach ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="row">
            <!-- <h2>Moduli istanziabili</h2> -->
            <? $i=0; ?>
            <? foreach($mdls as $m): ?>
                <? if(!in_array($m['name'], $hide)): ?>
                    <div class="col-md-6">
                    <div style="cursor: pointer" class="panel panel-info" onclick="location.href='<?= $ctrl->link($m['name'], 'manageDoc') ?>'">
                            <div class="panel-heading">
                                <h3 class="panel-title"><a href="<?= $ctrl->link($m['name'], 'manageDoc') ?>"><?= $m['label'] ?></a></h3>
                            </div>
                            <div class="panel-body text-center">
                                <p class="text-center">
                                    <i class="fa fa-<?= $fas[$m['name']] ?> fa-3x"></i>
                                </p>
                                <div class="small"><?= \Gino\htmlchars($m['description']) ?></div>
                            </div>
                        </div>
                    </div>
                    <? if (++$i%2 == 0): ?>
                        <div class="clearfix"></div>
                    <? endif ?>
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
