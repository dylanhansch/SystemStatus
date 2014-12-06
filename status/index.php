<?php
require_once("protected/config.php");
require_once("global.php");

function servers(){
	global $mysqli;
	
	$stmt = $mysqli->prepare("SELECT name,location,host,type,url FROM servers ORDER BY id");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_name,$out_location,$out_host,$out_type,$out_url);
	$servers = array();
	
	while($stmt->fetch()){
		$servers[] = array('name' => $out_name, 'location' => $out_location, 'host' => $out_host, 'type' => $out_type, 'url' => $out_url);
	}
	$stmt->close();
	
	return $servers;
}

function get_data($url){
  $ch = curl_init();
  $timeout = 5;
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
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
					<div class="content" style="margin-top:25px" id="content">
						<table class="table table-striped table-condensed">
							<tr>
								<th id="status">Status</th>
								<th id="name">Name</th>
								<th id="type">Type</th>
								<th id="host">Host</th>
								<th id="location">Location</th>
								<th id="uptime">Uptime</th>
								<th id="load">CPU Load</th>
								<th id="ram">RAM (Free)</th>
								<th id="hdd">HDD (Free)</th>
							</tr>
							<?php $servers = servers();
							foreach($servers as $server):
							
							$output = get_data($server['url']);
							if(($output == NULL) || ($output === false)){
								$data = array();
								$data['uptime'] = '
								<div class="progress">
									<div class="progress-bar progress-bar-danger" role="progressbar" style="width: 100%;"><small>Down</small></div>
								</div>
								';
								$data['load'] = '
								<div class="progress">
									<div class="progress-bar progress-bar-danger" role="progressbar" style="width: 100%;"><small>Down</small></div>
								</div>
								';
								$data['online'] = '
								<div class="progress">
									<div class="progress-bar progress-bar-danger" role="progressbar" style="width: 100%;"><small>Down</small></div>
								</div>
								';
								$data['memory'] = '
								<div class="progress progress-striped active">
									<div class="progress-bar progress-bar-danger" role="progressbar" style="width: 100%;"><small>n/a</small></div>
								</div>
								';
								$data['hdd'] = '
								<div class="progress progress-striped active">
									<div class="progress-bar progress-bar-danger" role="progressbar" style="width: 100%;"><small>n/a</small></div>
								</div>
								';
							}else{
								$data = json_decode($output, true);
								$data["load"] = number_format($data["load"], 2);
							}
							?>
							<tr>
								<td><?php echo($data['online']); ?></td>
								<td><?php echo($server['name']); ?></td>
								<td><?php echo($server['type']); ?></td>
								<td><?php echo($server['host']); ?></td>
								<td><?php echo($server['location']); ?></td>
								<td><?php echo($data['uptime']); ?></td>
								<td><?php echo($data['load']); ?></td>
								<td><?php echo($data['memory']); ?></td>
								<td><?php echo($data['hdd']); ?></td>
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