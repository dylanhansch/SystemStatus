<?php
require_once("protected/config.php");
require_once("global.php");

if($logged == 0){
	header("Location: login.php");
}elseif($role != "admin"){
	die("You must be an admin or staff member to access this page.");
}

if(isset($_GET['edituser'])){
	$edituser = $_GET['edituser'];
	$stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
	$stmt->bind_param('i', $edituser);
	$stmt->execute();
	if($stmt->fetch()){
		$edituserfetch = True;
	}
	$stmt->close();
}

if(isset($_GET['editserver'])){
	$editserver = $_GET['editserver'];
	$stmt = $mysqli->prepare("SELECT name FROM servers WHERE id = ?");
	$stmt->bind_param('i', $editserver);
	$stmt->execute();
	if($stmt->fetch()){
		$editserverfetch = True;
	}
	$stmt->close();
}

if(isset($_GET['createuser'])){
	$title = "Create User";
	$createuser_message = "";
	
	if(isset($_POST['username'])){

		$username = $_POST['username'];
		$email = $_POST['email'];
		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$pass1 = $_POST['pass1'];
		$pass2 = $_POST['pass2'];
		$role = $_POST['role'];
		
		if( (!$username) || (!$email) || (!$fname) || (!$lname) || (!$pass1) || (!$pass2) || (!$role) ){
			$createuser_message = "Please complete all the fields below.";
		}else{
			if($pass1 != $pass2){
				$createuser_message = "Your passwords do not match.";
			}else{
				//securing the data
				$pass1 = crypt($pass1);
				
				//check for duplicates
				$stmt = $mysqli->prepare("SELECT username FROM `users` WHERE `username` = ? OR `email` = ? LIMIT 1");
				echo($mysqli->error);
				$stmt->bind_param('ss', $username,$email);
				$stmt->execute();
				$stmt->bind_result($user_query);
				if($stmt->fetch()){
					if($user_query == $username){
						$createuser_message = "Your username is already in use.";
					}else{
						$createuser_message = "Your email is already in use.";
					}
				}else{
					$stmt->close();
					//insert the members
					$ip_address = $_SERVER['REMOTE_ADDR'];
					
					$stmt = $mysqli->prepare("INSERT INTO users (username, email, firstname, lastname, password, role) VALUES (?, ?, ?, ?, ?, ?)");
					echo($mysqli->error);
					$stmt->bind_param('ssssss', $username, $email, $fname, $lname, $pass1, $role);
					$stmt->execute();
					
					$createuser_message = "User registered.";
					header('Refresh: 2; URL='.$basedir.'admin.php');
				}
				$stmt->close();
			}
		}
	}
}elseif(isset($_GET['edituser']) && isset($_GET['pass']) && $edituserfetch == True){
	$title = "Update Password";
	$editpassword_message = "";
	
	if(isset($_POST['npass1'])){
		
		$npass1 = $_POST['npass1'];
		$npass2 = $_POST['npass2'];
		
		if( (!$npass1) || (!$npass2) ){
			// Checking for completion
			$editpassword_message = "Please complete all the fields below.";
		}elseif($npass1 != $npass2){
			// Making sure they match
			$editpassword_message = "Your new passwords do not match.";
		}else{
			$stmt->close();
			// Change the password
			$npass1 = crypt($npass1);
			
			$stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE id = ?");
			$stmt->bind_param('si', $npass1, $edituser);
			$stmt->execute();
			
			$editpassword_message = "Password changed!";
			header('Refresh: 2; URL='.$basedir.'admin.php?edituser='.$_GET['edituser']);
		}
		$stmt->close();
	}
}elseif(isset($_GET['edituser']) && $edituserfetch == True){
	$title = "Edit User";
	$edituser_message = "";
	
	$stmt = $mysqli->prepare("SELECT username,email,firstname,lastname,role FROM users WHERE id = ?");
	echo($mysqli->error);
	$stmt->bind_param('i', $edituser);
	$stmt->execute();
	$stmt->bind_result($e_username,$e_email,$e_firstname,$e_lastname,$e_role);
	$stmt->fetch();
	$stmt->close();
	
	if(isset($_POST['username'])){

		$username = $_POST['username'];
		$email = $_POST['email'];
		$fname = $_POST['fname'];
		$lname = $_POST['lname'];
		$role = $_POST['role'];
		
		//error handling
		if( (!$username) || (!$email) || (!$fname) || (!$lname) || (!$role) ){
			$edituser_message = "Please complete all the fields below.";
		}else{
			//check for duplicates
			$stmt = $mysqli->prepare("SELECT username FROM users WHERE id <> ? AND (username = ? OR email = ?)");
			echo($mysqli->error);
			$stmt->bind_param('iss', $edituser,$username,$email);
			$stmt->execute();
			$stmt->bind_result($user_query);
				
			if($stmt->fetch()){
				if($user_query == $username){
					$edituser_message = "That username is already in use.";
				}else{
					$edituser_message = "That email is already in use.";
				}
			}else{
				$stmt->close();
				//insert the members
				
				$stmt = $mysqli->prepare("UPDATE users SET username = ?, email = ?, firstname = ?, lastname = ?, role = ? WHERE id = ?");
				echo($mysqli->error);
				$stmt->bind_param('sssssi', $username, $email, $fname, $lname, $role, $edituser);
				$stmt->execute();
				
				$edituser_message = "User's account updated.";
				header('Refresh: 2; URL='.$basedir.'admin.php?edituser='.$_GET['edituser']);
			}
			$stmt->close();
		}
	}
}elseif(isset($_GET['createserver'])){
	$title = "Create Server";
	$createserver_message = "";
	
	if(isset($_POST['name'])){
		$name = $_POST['name'];
		$url = $_POST['url'];
		$location = $_POST['location'];
		$host = $_POST['host'];
		$type = $_POST['type'];
		
		if( (!$name) || (!$url) || (!$location) || (!$host) || (!$type) ){
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
				$stmt = $mysqli->prepare("INSERT INTO servers (name, url, location, host, type) VALUES (?, ?, ?, ?, ?)");
				echo($mysqli->error);
				$stmt->bind_param('sssss', $name, $url, $location, $host, $type);
				$stmt->execute();
				
				$createserver_message = "Server ".$name." now being monitored.";
				header('Refresh: 2; URL='.$basedir.'admin.php');
			}
			$stmt->close();
		}
	}
}elseif(isset($_GET['editserver']) && $editserverfetch == True){
	$title = "Edit Server";
	$editserver_message = "";
	
	$stmt = $mysqli->prepare("SELECT name,url,location,host,type FROM servers WHERE id = ?");
	echo($mysqli->error);
	$stmt->bind_param('i', $editserver);
	$stmt->execute();
	$stmt->bind_result($e_name,$e_url,$e_location,$e_host,$e_type);
	$stmt->fetch();
	$stmt->close();
	
	if(isset($_POST['name'])){
		$name = $_POST['name'];
		$url = $_POST['url'];
		$location = $_POST['location'];
		$host = $_POST['host'];
		$type = $_POST['type'];
		
		if( (!$name) || (!$url) || (!$location) || (!$host) || (!$type) ){
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
				$stmt = $mysqli->prepare("UPDATE servers SET name = ?, url = ?, location = ?, host = ?, type = ? WHERE id = ?");
				echo($mysqli->error);
				$stmt->bind_param('sssssi', $name, $url, $location, $host, $type, $editserver);
				$stmt->execute();
				
				$editserver_message = "Server updated.";
				header('Refresh: 2; URL='.$basedir.'admin.php?editserver='.$_GET['editserver']);
			}
			$stmt->close();
		}
	}
}else{
	$title = "Admin";
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
	
	$stmt = $mysqli->prepare("SELECT id,name,url,location,host,type FROM servers ORDER BY id");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_id,$out_name,$out_url,$out_location,$out_host,$out_type);
	$servers = array();
	
	while($stmt->fetch()){
		$servers[] = array('id' => $out_id, 'name' => $out_name, 'url' => $out_url, 'location' => $out_location, 'host' => $out_host, 'type' => $out_type);
	}
	$stmt->close();
	
	return $servers;
}

// Delete user from application
function del_user($user_id){
	global $mysqli, $session_id;
	
	$stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
	echo($mysqli->error);
	$stmt->bind_param("i", $user_id);
	$stmt->execute();
	$stmt->close();
}

if(isset($_GET['deluser'])){
	del_user($_GET['deluser']);
	header("Location: admin.php");
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

if(isset($_GET['delserver'])){
	del_server($_GET['delserver']);
	header("Location: admin.php");
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
		
		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<?php include_once('navbar.php'); ?>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<?php if(isset($_GET['createuser'])){ ?>
					
					<h1>Create User</h1>
					<ol class="breadcrumb">
					  <li><a href="admin.php">Admin</a></li>
					  <li class="active">Create</li>
					</ol>
					<div class="well">
						<?php echo($createuser_message); ?>
						<form class="form-signin" action="admin.php?createuser" method="post">
							<div class="row">
								<div class="col-sm-6">
									<label for="name">Username</label>
									<input type="text" class="form-control" name="username" placeholder="Username" required autofocus>
								</div>
								<div class="col-sm-6">
									<label for="name">Email Address</label>
									<input type="text" class="form-control" name="email" placeholder="Email" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-6">
									<label for="name">First Name</label>
									<input type="text" class="form-control" name="fname" placeholder="Firstname" required>
								</div>
								<div class="col-sm-6">
									<label for="name">Last Name</label>
									<input type="text" class="form-control" name="lname" placeholder="Lastname" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-6">
									<label for="name">Password</label>
									<input type="password" class="form-control" name="pass1" placeholder="Password" required>
								</div>
								<div class="col-sm-6">
									<label for="name">Last Name</label>
									<input type="password" class="form-control" name="pass2" placeholder="Confirm Password" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-3">
									<label for="name">Role</label>
									<select name="role" class="form-control" required>
										<option value="user" selected="selected">User</option>
										<option value="staff">Staff</option>
										<option value="admin">Admin</option>
									</select>
								</div>
							</div>
							<br>
							<input type="submit" class="btn btn-warning center" value="Create User"/>
						</form>
					</div>
					
					<?php }elseif(isset($_GET['edituser']) && isset($_GET['pass']) && $edituserfetch == True){ ?>
					
					<h1>Update Password</h1>
					<ol class="breadcrumb">
					  <li><a href="admin.php">Admin</a></li>
					  <li><a href="admin.php?edituser=<?php echo($edituser); ?>">Edit</a></li>
					  <li class="active">Password</li>
					</ol>
					<div class="well">
						<?php echo($editpassword_message); ?>
						<form class="form-signin" action="admin.php?edituser=<?php echo($edituser); ?>&pass" method="post">
							<div class="row">
								<div class="col-sm-6">
									<label for="name">New Password</label>
									<input type="password" class="form-control" name="npass1" placeholder="New Password" required autofocus>
								</div>
								<div class="col-sm-6">
									<label for="name">Confirm New Password</label>
									<input type="password" class="form-control" name="npass2" placeholder="Confirm New Password" required>
								</div>
							</div>
							<br>
							<input type="submit" class="btn btn-warning center" value="Update Password"/>
						</form>
					</div>
					
					<?php }elseif(isset($_GET['edituser']) && $edituserfetch == True){ ?>
					
					<h1>Edit User <a href="<?php echo($basedir); ?>admin.php?edituser=<?php echo($edituser); ?>&pass" class="btn btn-info btn-sm">Change Password</a></h1>
					<ol class="breadcrumb">
					  <li><a href="admin.php">Admin</a></li>
					  <li class="active">Edit</li>
					</ol>
					<div class="well">
						<?php echo($edituser_message); ?>
						<form class="form-signin" action="admin.php?edituser=<?php echo($edituser); ?>" method="post">
							<div class="row">
								<div class="col-sm-6">
									<label for="name">Username</label>
									<input type="text" class="form-control" name="username" value="<?php echo($e_username); ?>" required>
								</div>
								<div class="col-sm-6">
									<label for="name">Email Address</label>
									<input type="text" class="form-control" name="email" value="<?php echo($e_email); ?>" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-6">
									<label for="name">First Name</label>
									<input type="text" class="form-control" name="fname" value="<?php echo($e_firstname); ?>" required>
								</div>
								<div class="col-sm-6">
									<label for="name">Last Name</label>
									<input type="text" class="form-control" name="lname" value="<?php echo($e_lastname); ?>" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-3">
									<label for="name">Role</label>
									<select name="role" class="form-control" required>
										<option value="user" <?php if($e_role == "user"){ ?> selected="selected" <?php } ?>>User</option>
										<option value="staff" <?php if($e_role == "staff"){ ?> selected="selected" <?php } ?>>Staff</option>
										<option value="admin" <?php if($e_role == "admin"){ ?> selected="selected" <?php } ?>>Admin</option>
									</select>
								</div>
							</div>
							<br>
							<input type="submit" class="btn btn-warning center" value="Update User"/>
						</form>
					</div>
					
					<?php }elseif(isset($_GET['createserver'])){ ?>
					
					<h1>Create Server</h1>
					<ol class="breadcrumb">
					  <li><a href="admin.php">Admin</a></li>
					  <li class="active">Create</li>
					</ol>
					<div class="well">
						<?php echo($createserver_message); ?>
						<form class="form-signin" action="admin.php?createserver" method="post">
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
									<label for="name">Host</label>
									<input type="text" class="form-control" name="host" placeholder="Ex. Advantage Servers" required>
								</div>
								<div class="col-sm-6">
									<label for="name">Location</label>
									<input type="text" class="form-control" name="location" placeholder="Ex. Hudson, WI" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12">
									<label for="name">URL to remote.php on server</label>
									<input type="text" class="form-control" name="url" placeholder="Ex. http://example.com/remote.php" required>
								</div>
							</div>
							<br>
							<input type="submit" class="btn btn-warning center" value="Create Server"/>
						</form>
					</div>
					
					<?php }elseif(isset($_GET['editserver']) && $editserverfetch == True){ ?>
					
					<h1>Edit Server</h1>
					<ol class="breadcrumb">
					  <li><a href="admin.php">Admin</a></li>
					  <li class="active">Edit</li>
					</ol>
					<div class="well">
						<?php echo($editserver_message); ?>
						<form class="form-signin" action="admin.php?editserver=<?php echo($editserver); ?>" method="post">
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
									<label for="name">Host</label>
									<input type="text" class="form-control" name="host" value="<?php echo($e_host); ?>" required>
								</div>
								<div class="col-sm-6">
									<label for="name">Location</label>
									<input type="text" class="form-control" name="location" value="<?php echo($e_location); ?>" required>
								</div>
							</div>
							<br>
							<div class="row">
								<div class="col-sm-12">
									<label for="name">URL to remote.php on server</label>
									<input type="text" class="form-control" name="url" value="<?php echo($e_url); ?>" required>
								</div>
							</div>
							<br>
							<input type="submit" class="btn btn-warning center" value="Update Server"/>
						</form>
					</div>
					
					<?php }else{ ?>
					
					<h1>Manage Servers <a href="?createserver" class="btn btn-sm btn-info">Create Server</a></h1>
					<div class="well">
						<table class="table table-striped">
							<tr>
								<th>Name</th>
								<th>Type</th>
								<th>Host</th>
								<th>Location</th>
								<th>URL</th>
								<th></th>
							</tr>
							<?php $servers = servers();
							foreach($servers as $server): ?>
							<tr>
								<td><?php echo('<a href="'.$basedir.'admin.php?editserver='.$server['id'].'">'.$server['name'].'</a>'); ?></td>
								<td><?php echo($server['type']); ?></td>
								<td><?php echo($server['host']); ?></td>
								<td><?php echo($server['location']); ?></td>
								<td><?php echo($server['url']); ?></td>
								<td><a href="?delserver=<?php echo($server['id']); ?>" onclick="return confirmation()"><span class="glyphicon glyphicon-remove"></span></a>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>
					
					<h1>Manage Users <a href="?createuser" class="btn btn-sm btn-info">Create User</a></h1>
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
								<td><?php echo('<a href="'.$basedir.'admin.php?edituser='.$user["id"].'">'.$user["username"].'</a>'); ?></td>
								<td><?php echo($user['role']); ?></td>
								<td><a href="?deluser=<?php echo($user['id']); ?>" onclick="return confirmation()"><span class="glyphicon glyphicon-remove"></span></a>
							</tr>
							<?php endforeach; ?>
						</table>
					</div>
					
					<?php } ?>
				</div>
			</div>
		</div>
		
		<div id="footer">
			<div class="container">
				<p class="text-muted" align="center"><a href="https://github.com/dylanhansch/SystemStatus">System Status</a> | &copy; 2014 <a href="http://dylanhansch.net">Dylan Hansch</a>. All rights reserved.</p>
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
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>