<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'head_admin.php' %}
	<body>
		<!-- top bar -->
		<nav class="navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container-fluid admin-header">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu-admin-container" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="#" title="Otto">
						<img alt="Logo Otto" src="img/logo.png" style="width: 105px; height: 50px;" />
					</a>
				</div>
				
				<div class="navbar-tools">
					<!-- Collect the nav links, forms, and other content for toggling -->
					<div class="collapse navbar-collapse" id="menu-admin-container" style="overflow: auto;">
					{module classid=5 func=render}
					</div><!-- /.navbar-collapse -->
				</div>
			</div>
		</nav>

		<!-- sidenav -->
		{module sysclassid=12 func=sidenav}

		<div class="container">
			{module id=0}
		</div>
	</body>
</html>
