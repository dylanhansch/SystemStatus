<?php
include_once('protected/config.php');
include_once('global.php');

function servers(){
	global $mysqli;
	
	$stmt = $mysqli->prepare("SELECT id,name,location,type,url FROM servers ORDER BY id");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_id,$out_name,$out_location,$out_type,$out_url);
	$servers = array();
	
	while($stmt->fetch()){
		$servers[] = array('id' => $out_id, 'name' => $out_name, 'location' => $out_location, 'type' => $out_type, 'url' => $out_url);
	}
	$stmt->close();
	
	return $servers;
}

function announcements(){
	global $mysqli;
	
	$stmt = $mysqli->prepare("SELECT header,body,level FROM announcements WHERE status = 'active' ORDER BY id DESC");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_header,$out_body,$out_level);
	$announcements = array();
	while($stmt->fetch()){
		$announcements[] = array('header' => $out_header, 'body' => $out_body, 'level' => $out_level);
	}
	$stmt->close();
	
	return $announcements;
}

function print_table(){ ?>
	<table class="table table-striped table-condensed">
		<thead>
			<tr>
				<th id="status">Status</th>
				<th id="name">Name</th>
				<th id="type">Type</th>
				<th id="location">Location</th>
				<th id="uptime">Uptime</th>
				<th id="load">CPU Load</th>
				<th id="ram">Memory (Free)</th>
				<th id="hdd">Storage (Free)</th>
			</tr>
		</thead>
		<tbody>
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
		}
		?>
		<tr>
			<td><?php echo($data['online']); ?></td>
			<td><?php echo($server['name']); ?></td>
			<td><?php echo($server['type']); ?></td>
			<td><?php echo($server['location']); ?></td>
			<td><?php echo($data['uptime']); ?></td>
			<td><?php echo($data['load']); ?></td>
			<td><?php echo($data['memory']); ?></td>
			<td><?php echo($data['hdd']); ?></td>
		</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	
<?php }

$flag = False;
function isOperational(){
	global $flag;
	$servers = servers();
	foreach($servers as $server):
	
	if($flag == False){
		$output = get_data($server['url']);
		if(($output == NULL) || ($output === false)){
			$flag = True;
		}else{
			$flag = False;
		}
	}
	endforeach;
}

if(isset($_GET['reload'])){
	print_table();
	die();
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
		
		<script>
		function handle() {
		document.getElementById("content").innerHTML = this.responseText;
		setTimeout(reload, <?php echo($refresh); ?>);
		}
		function reload() {
		var req = new XMLHttpRequest();
		req.onload = handle;
		req.open("get", "?reload", true);
		req.send();
		}
		setTimeout(reload, <?php echo($refresh); ?>);
		</script>
	</head>
	<body>
		<?php include_once('navbar.php'); ?>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<div class="well" style="margin-top:25px;">
						<div class="row">
							<?php isOperational(); ?>
							
							<?php if($flag == True){ ?>
								<div class="col-lg-12 pull-left">
									<h2 style="inline-block" class="free">
									<img src="<?php echo($basedir); ?>img/error.png" style="vertical-align:middle;">
									System Outage/Disruption</h2>
									<p style="padding-left:58px">No need to contact support, we've been automatically notified of this incident and awesome technicians are working on resolving the issue.</p>
								</div>
							<?php }else{ ?>
								<div class="col-sm-8 pull-left">
									<h2 style="inline-block" class="free">
									<img src="<?php echo($basedir); ?>img/success.png" style="vertical-align:middle;">
									Everything's Online!</h2>
									<p style="padding-left:58px">If you are experiencing any issues please open a support ticket.</p>
								</div>
								<div class="col-sm-4 pull-right">
									<a href="<?php echo($support_url); ?>" class="btn btn-default btn-primary" style="float:right;">Contact Support</a>
								</div>
							<?php } ?>
						</div>
					</div>
					<div class="content" style="margin-top:25px" id="content">
						<?php
						print_table();
						?>
					</div>
					
					<?php $announcements = announcements();
					foreach($announcements as $announcement){ ?>
						<div class="row">
							<div class="col-lg-12">
								<div class="panel panel-<?php echo($announcement['level']); ?>">
									<div class="panel-heading">
										<h3 class="panel-title"><?php echo($announcement['header']); ?></h3>
									</div>
									<div class="panel-body">
										<?php echo($announcement['body']); ?>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>
					
				</div>
			</div>
		</div>
		
		<?php include_once('footer.php'); ?>
	</body>
</html>