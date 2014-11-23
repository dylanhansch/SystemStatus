<?php
require_once("protected/config.php");
require_once("global.php");

function servers(){
	global $mysqli;
	
	$stmt = $mysqli->prepare("SELECT name,location,host,type FROM servers ORDER BY id");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_name,$out_location,$out_host,$out_type);
	$servers = array();
	
	while($stmt->fetch()){
		$servers[] = array('name' => $out_name, 'location' => $out_location, 'host' => $out_host, 'type' => $out_type);
	}
	$stmt->close();
	
	return $servers;
}
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
					<div class="well" style="margin-top:25px">
						<table class="table table-striped">
							<tr>
								<th>Status</th>
								<th>Name</th>
								<th>Type</th>
								<th>Host</th>
								<th>Location</th>
								<th>Uptime</th>
								<th>CPU Load</th>
								<th>RAM (Free)</th>
								<th>HDD (Free)</th>
							</tr>
							<?php $servers = servers();
							foreach($servers as $server): ?>
							<tr>
								<td></td>
								<td><?php echo($server['name']); ?></td>
								<td><?php echo($server['type']); ?></td>
								<td><?php echo($server['host']); ?></td>
								<td><?php echo($server['location']); ?></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>
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