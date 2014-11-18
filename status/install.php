<?php
require_once("protected/config.php");

if(isset($_GET['pop'])){
	$stmt = $mysqli->prepare("
	CREATE TABLE `servers` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(65) NOT NULL,
		`url` varchar(255) NOT NULL,
		`location` varchar(65) NOT NULL,
		`host` varchar(65) NOT NULL,
		`type` varchar(65) NOT NULL,
		PRIMARY KEY (`id`)
	)
	");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->close();
	
	$stmt = $mysqli->prepare("
	CREATE TABLE `users` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`username` varchar(24) NOT NULL,
		`email` varchar(255) NOT NULL,
		`firstname` varchar(255) NOT NULL,
		`lastname` varchar(255) NOT NULL,
		`password` varchar(255) NOT NULL,
		`role` varchar(255) NOT NULL,
		PRIMARY KEY (`id`)
	)
	");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->close();
	
	$stmt = $mysqli->prepare("
	CREATE TABLE `logins` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`user` int(11) NOT NULL,
		`date` timestamp NOT NULL,
		`ip_address` varchar(15),
		PRIMARY KEY (`id`)
	)
	");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->close();
	
	header("Location: install.php?success");
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Install | System Status</title>
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
					<?php if(isset($_GET['success'])){ ?>
					
					<h1 style="color:green">Congrats!</h1>
					<p>System Status is successfully installed, now you can start adding your servers so you can monitor them! :)</p>
					
					<h2 style="color:red">ALERT!</h2>
					<p>You <strong>MUST</strong> remove install.php if you want this application to be secure! Failing to do so, <strong>WILL</strong> result in a loss of data once a malicious user comes along.</p>
					
					<?php }else{ ?>
					
					<h1>Install System Status</h1>
					<hr>
					<h3>Step 1.</h3>
					<p>Create a database. For example a database called, "systemstatus".
					
					<h3>Step 2.</h3>
					<p>Fill out the config file in "protected/config.php" with the relevant information.</p>
					
					<h3>Step 3.</h3>
					<p>Populate the database with necessary tables.</p>
					<a href="?pop" class="btn btn-warning">Populate Database</a>
					
					<?php } ?>
				</div>
			</div>
		</div>
		
		<div id="footer">
			<div class="container">
				<p class="text-muted" align="center"><a href="https://github.com/dylanhansch/SystemStatus">System Status</a> | &copy; 2014 <a href="http://dylanhansch.net">Dylan Hansch</a>. All rights reserved.</p>
			</div>
		</div>
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>