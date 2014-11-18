<?php
require_once("protected/config.php");
require_once("global.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<title>System Status</title>
		<meta charset="utf-8">
		<meta name="author" content="Dylan Hansch">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<link rel="shortcut icon" content="none">
		
		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<?php include_once('navbar.php'); ?>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h1><?php echo($name); ?> System Status</h1>
				</div>
			</div>
		</div>
		
		<div id="footer">
			<div class="container">
				<p class="text-muted" align="center"><a href="https://github.com/dylanhansch/SystemStatus">System Status</a> | &copy; 2014 <a href="https://dylanhansch.net">Dylan Hansch</a>. All rights reserved.</p>
			</div>
		</div>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>