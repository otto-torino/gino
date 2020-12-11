
<?php $registry = \Gino\Registry::instance(); ?>

<head>
	<meta charset="utf-8" />
	<base href="<?= $registry->request->root_absolute_url ?>" />
	<title><?= $registry->title ?></title>
	<meta name="description" content="<?= $registry->description ?>" />
	<meta name="keywords" content="<?= $registry->keywords ?>" />
	<meta content="width=device-width, initial-scale=1.0" name="viewport">
	<!-- other meta set from modules -->
	<?=  $registry->variables('meta') ?>
	<!-- other link tags set from modules -->
	<?=  $registry->variables('head_links') ?>
	<!-- system css -->
	<?=  $registry->variables('css') ?>
	<?=  $registry->variables('raw_css') ?>
	<!-- core js -->
	<?=  $registry->variables('core_js') ?>
	<?=  $registry->variables('raw_js') ?>
	<!-- apps js -->
	<?=  $registry->variables('js') ?>
	<?= \Gino\Document::errorMessages() ?>
	<link rel="shortcut icon" href="<?= $this->_registry->favicon ?>" />
	<link href='https://fonts.googleapis.com/css?family=Roboto:300,900,700,300italic' rel='stylesheet' type='text/css' />
	<!-- Gino onload function -->
	<?= \Gino\Javascript::onLoadFunction() ?>
	
	<!-- in gino-min ? -->
	<script>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	</script>
	
</head>