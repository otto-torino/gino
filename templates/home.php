<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'core/head.php' %}
	<body>
		{% block 'core/debug_alert.php' %}
		{% block 'navbar.php' %}
		
		<div class="container bg-white">
			
			<div class="jumbotron jumbotron-fluid">
				<div class="container text-center">
					<h1 class="display-4">gino</h1>
					<p class="lead">#hello_world</p>
					<hr class="my-4">
					<p class="lead">
						<a class="btn btn-primary btn-lg" href="page/view/about-gino/" role="button">Learn more</a>
					</p>
				</div>
			</div>
			
			<div class="row">
				<div class="col-md-6">{% block article.showcase %}</div>
				<div class="col-md-6">{% block cal.calendar %}</div>
			</div>
		</div>
		
		{% block 'core/footer.php' %}
	</body>
</html>
