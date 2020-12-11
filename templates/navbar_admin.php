
<?php $registry = \Gino\Registry::instance(); ?>

<!-- You can also remove the .navbar-expand-md class to ALWAYS hide navbar links and display the toggler button -->

<nav class="navbar navbar-expand-md navbar-dark bg-info fixed-top">
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
	<div class="collapse navbar-collapse justify-content-between align-items-center" id="bs-gino-navbar-collapse">
		<!-- Menu -->
		{% block menu_admin.render %}
		
		<div class="navbar-tools">
			
		</div><!-- /.navbar-tools -->
	</div><!-- /.navbar-collapse -->
</nav>