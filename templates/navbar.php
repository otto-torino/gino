
<?php $registry = \Gino\Registry::instance(); ?>

<!-- You can also remove the .navbar-expand-md class to ALWAYS hide navbar links and display the toggler button -->

<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
	<!-- Brand image -->
	<a class="navbar-brand" href="#" itemprop="url" title="Otto">
		<img class="" itemprop="logo" alt="Logo Otto" src="img/logo.png" style="width: 109px; height: 50px;">
	</a>
	<button class="navbar-toggler" type="button" 
		data-toggle="collapse" 
		data-target="#bs-gino-navbar-collapse" 
		aria-controls="bs-gino-navbar-collapse" 
		aria-expanded="false" 
		aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	
	<!-- Collect the nav links, forms, and other content for toggling -->
	<div class="collapse navbar-collapse" id="bs-gino-navbar-collapse">
		<!-- Menu -->
		{module classid=4 func=render}
	</div><!-- /.navbar-collapse -->
	
	<div class="navbar-tools">
	
		<!-- Choice language -->
		<div class="navbar-language">
			{module sysclassid=2 func=choiceLanguage}
		</div>
		
		<!-- Search -->
		<div class="navbar-search">
			{module sysclassid=13 func=form}
		</div>
		
		<!-- Link to login -->
		<?php if(!$registry->session->user_id): ?>
		<div class="navbar-login">
			<a href="auth/login">Accedi</a>
		</div>
		<?php endif; ?>
	</div>
</nav>