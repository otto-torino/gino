<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'core/head.php' %}
	<body>
		{% block 'navbar_mobile.php' %}
		
		<div class="container bg-white">
			{module id=0}
		</div>
		
		{% block 'core/footer.php' %}
	</body>
</html>