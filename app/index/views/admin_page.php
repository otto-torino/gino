<section class="admin-home">
<div class="row">
    <div class="col-md-6">
        <!-- <h2>Moduli di sistema</h2> -->
        <div class="row">
            <? $i=0; ?>
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
                <? endif ?>
            <? endforeach ?>
        </div>
    </div>
</div>
</section>
