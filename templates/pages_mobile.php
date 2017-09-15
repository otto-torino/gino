<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'head.php' %}
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
		
		{% block 'footer.php' %}
	</body>
</html>