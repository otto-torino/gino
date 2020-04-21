<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'head_admin.php' %}
	<body class="without-fixed-top">
		<div class="wrapper">
            <!-- Sidebar -->
			{% block index.sidebar %}
			
			<!-- Page Content -->
			<div id="content">
				{% block 'navbar_admin_sidebar.php' %}
				
				<div class="container">
					{% block url %}
				</div>
			</div>
		</div>
	</body>
</html>
