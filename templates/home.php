<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'head.php' %}
	<body>
		{% block 'navbar.php' %}
		
		<div class="container bg-white">
			<div class="row">
				<div class="col-md-6">
					{module pageid=3 func=full}
				</div>
				<div class="col-md-6">
					{module pageid=1 func=full}
					{module pageid=2 func=full}
				</div>
			</div>
		</div>
		
		{% block 'footer.php' %}
	</body>
</html>
