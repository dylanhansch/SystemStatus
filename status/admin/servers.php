<?php
require_once("../protected/config.php");
require_once("../global.php");

if($logged == 0){
	header("Location: ../login.php");
}elseif($role != "admin"){
	die("You must be an admin or staff member to access this page.");
}

if(isset($_GET['edit'])){
	$editserver = $_GET['edit'];
	$stmt = $mysqli->prepare("SELECT name FROM servers WHERE id = ?");
	$stmt->bind_param('i', $editserver);
	$stmt->execute();
	if($stmt->fetch()){
		$editserverfetch = True;
	}
	$stmt->close();
}

if(isset($_GET['create'])){
	$title = "Create Server";
	$createserver_message = "";
	
	if(isset($_POST['name'])){
		$name = $_POST['name'];
		$url = $_POST['url'];
		$location = $_POST['location'];
		$type = $_POST['type'];
		
		if( (!$name) || (!$url) || (!$location) || (!$type) ){
			$createserver_message = "Please complete all the fields below.";
		}else{
			//check for duplicates
			$stmt = $mysqli->prepare("SELECT name FROM `servers` WHERE `name` = ? LIMIT 1");
			echo($mysqli->error);
			$stmt->bind_param('s', $name);
			$stmt->execute();
			$stmt->bind_result($user_query);
			if($stmt->fetch()){
				if($user_query == $name){
					$createserver_message = "That name is already in use.";
				}
			}else{
				$stmt->close();
				
				// Insert into servers
				$stmt = $mysqli->prepare("INSERT INTO servers (name, url, location, type) VALUES (?, ?, ?, ?)");
				echo($mysqli->error);
				$stmt->bind_param('ssss', $name, $url, $location, $type);
				$stmt->execute();
				
				$createserver_message = "Server ".$name." now being monitored.";
				header('Refresh: 2; URL='.$basedir.'admin/');
			}
			$stmt->close();
		}
	}
}elseif(isset($_GET['edit']) && $editserverfetch == True){
	$title = "Edit Server";
	$editserver_message = "";
	
	$stmt = $mysqli->prepare("SELECT name,url,location,type FROM servers WHERE id = ?");
	echo($mysqli->error);
	$stmt->bind_param('i', $editserver);
	$stmt->execute();
	$stmt->bind_result($e_name,$e_url,$e_location,$e_type);
	$stmt->fetch();
	$stmt->close();
	
	if(isset($_POST['name'])){
		$name = $_POST['name'];
		$url = $_POST['url'];
		$location = $_POST['location'];
		$type = $_POST['type'];
		
		if( (!$name) || (!$url) || (!$location) || (!$type) ){
			$createserver_message = "Please complete all the fields below.";
		}else{
			//check for duplicates
			$stmt = $mysqli->prepare("SELECT name FROM servers WHERE id <> ? AND name = ?");
			echo($mysqli->error);
			$stmt->bind_param('is', $editserver,$name);
			$stmt->execute();
			$stmt->bind_result($user_query);
				
			if($stmt->fetch()){
				if($user_query == $name){
					$editserver_message = "That name is already in use.";
				}
			}else{
				$stmt->close();
				
				// Update the server
				$stmt = $mysqli->prepare("UPDATE servers SET name = ?, url = ?, location = ?, type = ? WHERE id = ?");
				echo($mysqli->error);
				$stmt->bind_param('ssssi', $name, $url, $location, $type, $editserver);
				$stmt->execute();
				
				$editserver_message = "Server updated.";
				header('Refresh: 2; URL='.$basedir.'admin/servers.php?edit='.$editserver);
			}
			$stmt->close();
		}
	}
}else{
	header("Location: ".$basedir."admin/");
}

// Delete server from application
function del_server($server_id){
	global $mysqli, $session_id;
	
	$stmt = $mysqli->prepare("DELETE FROM servers WHERE id = ?");
	echo($mysqli->error);
	$stmt->bind_param("i", $server_id);
	$stmt->execute();
	$stmt->close();
}

if(isset($_GET['del'])){
	del_server($_GET['del']);
	header("Location: ".$basedir."admin/");
}
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo($title); ?> | System Status</title>
		<meta charset="utf-8">
		<meta name="author" content="Dylan Hansch">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<link rel="shortcut icon" content="none">
		
		<link rel="stylesheet" href="<?php echo($basedir); ?>assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="<?php echo($basedir); ?>style.css">
	</head>
	<body>
		<?php include_once('../navbar.php'); ?>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					
					<?php if(isset($_GET['create'])){ ?>
					
					<h1>Create Server</h1>
					<ol class="breadcrumb">
					  <li><a href="<?php echo($basedir); ?>admin/">Admin</a></li>
					  <li class="active">Create</li>
					</ol>
					<div class="well">
						<?php echo($createserver_message); ?>
						<form class="form-signin" action="servers.php?create" method="post">
							<div class="row">
								<div class="col-sm-6">
									<label for="name">Name</label>
									<input type="text" class="form-control" name="name" placeholder="Ex. Web" required autofocus>
								</div>
								<div class="col-sm-6">
									<label for="name">Type</label>
									<input type="text" class="form-control" name="type" placeholder="Ex. Web + MySQL" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-6">
									<label for="name">Location</label>
									<input type="text" class="form-control" name="location" placeholder="Ex. Hudson, WI" required>
								</div>
								<div class="col-sm-6">
									<label for="name">URL to remote.php on server</label>
									<input type="text" class="form-control" name="url" placeholder="Ex. http://example.com/remote.php" required>
								</div>
							</div>
							<br>
							<input type="submit" class="btn btn-warning center" value="Create Server"/>
						</form>
					</div>
					
					<?php }elseif(isset($_GET['edit']) && $editserverfetch == True){ ?>
					
					<h1>Edit Server</h1>
					<ol class="breadcrumb">
					  <li><a href="<?php echo($basedir); ?>admin/">Admin</a></li>
					  <li class="active">Edit</li>
					</ol>
					<div class="well">
						<?php echo($editserver_message); ?>
						<form class="form-signin" action="servers.php?edit=<?php echo($editserver); ?>" method="post">
							<div class="row">
								<div class="col-sm-6">
									<label for="name">Name</label>
									<input type="text" class="form-control" name="name" value="<?php echo($e_name); ?>" required>
								</div>
								<div class="col-sm-6">
									<label for="name">Type</label>
									<input type="text" class="form-control" name="type" value="<?php echo($e_type); ?>" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-6">
									<label for="name">Location</label>
									<input type="text" class="form-control" name="location" value="<?php echo($e_location); ?>" required>
								</div>
								<div class="col-sm-6">
									<label for="name">URL to remote.php on server</label>
									<input type="text" class="form-control" name="url" value="<?php echo($e_url); ?>" required>
								</div>
							</div>
							<br>
							<input type="submit" class="btn btn-warning center" value="Update Server"/>
						</form>
					</div>
					
					<?php } ?>
				</div>
			</div>
		</div>
		
		<?php include_once('../footer.php'); ?>
</html>