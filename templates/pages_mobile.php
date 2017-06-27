<!DOCTYPE html>
<html lang="<?= LANG ?>">
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
		<!-- core js -->
		<?=  $registry->variables('core_js') ?>
		<!-- apps js -->
		<?=  $registry->variables('js') ?>
		<?= \Gino\Document::errorMessages() ?>
		<link rel="shortcut icon" href="<?= $this->_registry->favicon ?>" />
		<link href='https://fonts.googleapis.com/css?family=Roboto:300,900,700,300italic' rel='stylesheet' type='text/css' />
		<!-- Gino onload function -->
		<?= \Gino\Javascript::vendor() ?>
		<?= \Gino\Javascript::onLoadFunction() ?>
		<!-- google analytics -->
		<?= \Gino\Javascript::analytics() ?>
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-fixed-top">
			<div class="container-fluid main-header">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">

					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-gino-navbar-collapse" aria-expanded="false">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#" itemprop="url" title="Otto" class="navbar-brand">
						<img class="" itemprop="logo" alt="Logo Otto" src="img/logo.png" style="width: 109px; height: 50px;">
					</a>
				</div>

				<div class="navbar-tools">
					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="bs-gino-navbar-collapse" style="overflow: auto;">
						<!-- Menu -->
						{module classid=4 func=render}
					</div><!-- /.navbar-collapse -->
				 </div><!-- /.navbar-tools -->
			</div><!-- /.container-fluid -->
		</nav>
		
		<div class="container bg-white">
			{module id=0}
		</div>
		<footer class="text-center">
			Otto Srl | <a href="page/view/privacy-cookie/">Privacy - Cookie</a> | <a href="admin">Area amministrativa</a>
			<div class="credits"><a target="_blank" href="http://www.otto.to.it"><img style="margin-left: 20px; width: 30px;" src="img/otto_credits.jpg" /></a></div>
		</footer>
	</body>
</html>