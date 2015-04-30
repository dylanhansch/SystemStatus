<?php
include_once('../protected/config.php');
include_once('../global.php');

if($logged == 0){
	header("Location: ../login.php");
}elseif($role != "admin"){
	die("You must be an admin or staff member to access this page.");
}

function users(){
	global $mysqli;
	
	$stmt = $mysqli->prepare("SELECT id,username,role FROM users");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_id, $out_username, $out_role);
	$users = array();

	while($stmt->fetch()){
		$users[] = array('id' => $out_id, 'username' => $out_username, 'role' => $out_role);
	}
	$stmt->close();

	return $users;
}

function servers(){
	global $mysqli;
	
	$stmt = $mysqli->prepare("SELECT id,name,url,location,type FROM servers ORDER BY id");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_id,$out_name,$out_url,$out_location,$out_type);
	$servers = array();
	
	while($stmt->fetch()){
		$servers[] = array('id' => $out_id, 'name' => $out_name, 'url' => $out_url, 'location' => $out_location, 'type' => $out_type);
	}
	$stmt->close();
	
	return $servers;
}

function announcements(){
	global $mysqli;
	
	$stmt = $mysqli->prepare("SELECT id,header,status,level FROM announcements ORDER BY id DESC");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_id,$out_header,$out_status,$out_level);
	$announcements = array();
	while($stmt->fetch()){
		$announcements[] = array('id' => $out_id, 'header' => $out_header, 'status' => $out_status, 'level' => $out_level);
	}
	$stmt->close();
	
	return $announcements;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Admin | System Status</title>
		<meta charset="utf-8">
		<meta name="author" content="Dylan Hansch">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<link rel="shortcut icon" content="none">
		
		<link rel="stylesheet" href="<?php echo($basedir); ?>bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="<?php echo($basedir); ?>style.css">
	</head>
	<body>
		<?php include_once('../navbar.php'); ?>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<h1>Manage Servers <a href="servers.php?create" class="btn btn-sm btn-info">Create Server</a></h1>
					<div class="well">
						<table class="table table-striped">
							<tr>
								<th>Name</th>
								<th>Type</th>
								<th>Location</th>
								<th>URL</th>
								<th></th>
							</tr>
							<?php $servers = servers();
							foreach($servers as $server): ?>
							<tr>
								<td><?php echo('<a href="'.$basedir.'admin/servers.php?edit='.$server['id'].'">'.$server['name'].'</a>'); ?></td>
								<td><?php echo($server['type']); ?></td>
								<td><?php echo($server['location']); ?></td>
								<td><?php echo($server['url']); ?></td>
								<td><a href="servers.php?del=<?php echo($server['id']); ?>" onclick="return confirmation()"><span class="glyphicon glyphicon-remove"></span></a>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>
					
					<h1>Manage Users <a href="users.php?create" class="btn btn-sm btn-info">Create User</a></h1>
					<div class="well">
						<table class="table table-striped">
							<tr>
								<th>Username</th>
								<th>Role</th>
								<th></th>
							</tr>
							<?php $users = users();
							foreach($users as $user): ?>
							<tr>
								<td><?php echo('<a href="'.$basedir.'admin/users.php?edit='.$user["id"].'">'.$user["username"].'</a>'); ?></td>
								<td><?php echo($user['role']); ?></td>
								<td><a href="users.php?del=<?php echo($user['id']); ?>" onclick="return confirmation()"><span class="glyphicon glyphicon-remove"></span></a>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>
					
					<h1>Manage Announcements <a href="announcements.php?create" class="btn btn-sm btn-info">Create Announcement</a></h1>
					<div class="well">
						<table class="table table-striped">
							<tr>
								<th>Title</th>
								<th>Level</th>
								<th>Status</th>
								<th></th>
							</tr>
							<?php $announcements = announcements();
							foreach($announcements as $announcement): ?>
							<tr>
								<td><?php echo('<a href="'.$basedir.'admin/announcements.php?edit='.$announcement["id"].'">'.$announcement["header"].'</a>'); ?></td>
								<td><?php echo($announcement['level']); ?></td>
								<td><?php echo($announcement['status']); ?></td>
								<td><a href="users.php?del=<?php echo($user['id']); ?>" onclick="return confirmation()"><span class="glyphicon glyphicon-remove"></span></a>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>
				</div>
			</div>
		</div>
		
		<script type="text/javascript">
		function confirmation() {
			var r = confirm("WARNING!\nThis action is perminate and non reversable. Are you sure you want to continue?");
			if (r == true) {
				return true;
			} else {
				return false;
			}
		}
		</script>
		
		<?php include_once('../footer.php'); ?>
	</body>
</html>