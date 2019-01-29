<!DOCTYPE html>
<html lang="<?= LANG ?>">
	{% block 'head_admin.php' %}
	<body class="without-fixed-top">
		<div class="wrapper">
            <!-- Sidebar -->
			{module sysclassid=12 func=sidebar}
			
			<!-- Page Content -->
			<div id="content">
				{% block 'navbar_admin_sidebar.php' %}
				
				<div class="container">
					{module id=0}
				</div>
			</div>
		</div>
	</body>
</html>
