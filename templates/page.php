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
		<!-- system js -->
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
		<!-- class="rheader rheader--top" -->
		<header class="navbar-inverse navbar-fixed-top" role="navigation">
			<div class="rheader-main">
				<div class="rheader-logo" itemscope="itemscope" itemtype="http://schema.org/Organization">
					<a href="#" itemprop="url" title="Otto" class="navbar-brand">
					<img itemprop="logo" alt="Logo Otto" src="img/logo.png" style="width: 109px; height: 50px;">
					</a>
				</div>
				
				<div class="rheader-nav">
					<h1 class="hidden">Menu</h1>
					<!-- Brand and toggle get grouped for better mobile display -->
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="menu-main-container">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
					</div>
					
				
					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="menu-main-container">
						{module classid=4 func=render}
					</div>
					<!-- /.navbar-collapse -->
					
					<div class="navbar-language">
						{module sysclassid=2 func=choiceLanguage}
					</div>
					
					<?php  if(!$registry->session->user_id): ?>
					<div class="rheader-login">
						<a href="auth/login">Accedi</a>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</header>
		<div class="container bg-white">
			{module id=0}
		</div>
		<footer>
			
		</footer>
	</body>
</html>
