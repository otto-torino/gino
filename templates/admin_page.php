<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'head_admin.php' %}
	<body>
		{% block 'navbar_admin.php' %}

		<!-- sidenav -->
		{module sysclassid=12 func=sidenav}

		<div class="container">
			{module id=0}
		</div>
	</body>
</html>
