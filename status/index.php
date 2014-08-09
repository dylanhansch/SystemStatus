<?php
require("config.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<!-- Meta -->
		<title>System Status</title>
		<meta charset="utf-8">
		<meta name="author" content="Dylan Hansch">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<link rel="shortcut icon" content="none">
		<!-- End Meta -->
		
		<!-- Style Referencing -->
		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="style.css">
		<!-- End Style Referencing -->
	</head>
	<body>
		<!-- Navigation Bar -->
		<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="index.php">System Status</a>
				</div>
				<div class="collapse navbar-collapse navbar-ex1-collapse">
					<ul class="nav navbar-nav pull-left">
						
					</ul>
					<ul class="nav navbar-nav pull-right">
						
					</ul>
				</div>
			</div>
		</nav>
		<!-- End Navigation Bar -->
		
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h1><?php echo($name); ?> System Status</h1>
				</div>
			</div>
		</div>
		
		<!-- Footer -->
		<div id="footer">
			<div class="container">
				<p class="text-muted" align="center"><a href="https://github.com/dylanhansch/SystemStatus">System Status</a> | &copy; 2014 <a href="http://dylanhansch.net">Dylan Hansch</a>. All rights reserved.</p>
			</div>
		</div>	
		<!-- End Footer -->
		
		<!-- Script Referencing -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="bootstrap/js/bootstrap.min.js"></script>
		<!-- End Script Referencing -->
	</body>
</html>