<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'head.php' %}
	<body>
		{% block 'navbar_mobile.php' %}
		
		<div class="container bg-white">
			{module pageid=1 func=full}
		</div>
		
		{% block 'footer.php' %}
	</body>
</html>