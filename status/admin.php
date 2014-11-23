<?php
require_once("protected/config.php");
require_once("global.php");

if($logged == 0){
	header("Location: login.php");
}elseif($role != "admin"){
	die("You must be an admin or staff member to access this page.");
}

if(isset($_GET['edituser'])){
	$stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
	$stmt->bind_param('i', $_GET['edituser']);
	$stmt->execute();
	if($stmt->fetch()){
		$edituserfetch = True;
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
			$stmt->bind_param('si', $npass1, $_GET['edituser']);
			$stmt->execute();
			
			$editpassword_message = "Password changed!";
		}
		$stmt->close();
	}
}elseif(isset($_GET['edituser']) && $edituserfetch == True){
	$title = "Edit User";
	$edituser_message = "";
	
	$stmt = $mysqli->prepare("SELECT username,email,firstname,lastname,role FROM users WHERE id = ?");
	echo($mysqli->error);
	$stmt->bind_param('i', $_GET['edituser']);
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
			$stmt->bind_param('iss', $session_id,$username,$email);
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
				$stmt->bind_param('sssssi', $username, $email, $fname, $lname, $role, $_GET['edituser']);
				$stmt->execute();
				
				$edituser_message = "User's account updated.";
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
					  <li><a href="admin.php?edituser=<?php echo($_GET['edituser']); ?>">Edit</a></li>
					  <li class="active">Password</li>
					</ol>
					<div class="well">
						<?php echo($editpassword_message); ?>
						<form class="form-signin" action="admin.php?edituser=<?php echo($_GET['edituser']); ?>&pass" method="post">
							<div class="row">
								<div class="col-sm-6">
									<label for="name">New Password</label>
									<input type="password" class="form-control" name="npass1" placeholder="New Password" required>
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
					
					<h1>Edit User <a href="<?php echo($basedir); ?>admin.php?edituser=<?php echo($_GET['edituser']); ?>&pass" class="btn btn-info btn-sm">Change Password</a></h1>
					<ol class="breadcrumb">
					  <li><a href="admin.php">Admin</a></li>
					  <li class="active">Edit</li>
					</ol>
					<div class="well">
						<?php echo($edituser_message); ?>
						<form class="form-signin" action="admin.php?edituser=<?php echo($_GET['edituser']); ?>" method="post">
							<div class="row">
								<div class="col-sm-6">
									<label for="name">Username</label>
									<input type="text" class="form-control" name="username" value="<?php echo($e_username); ?>" required autofocus>
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
					
					<?php }else{ ?>
					
					<h1>Manage Servers</h1>
					<div class="well">
						
					</div>
					
					<h1>Manage Users <a href="?createuser" class="btn btn-sm btn-info">Create User</a></h1>
					<div class="well">
						
						<table class="table table-striped">
							<tr>
								<th>Username</th>
								<th>Role</th>
							</tr>
							<?php $users = users();
							foreach($users as $user): ?>
							<tr>
								<td><?php echo('<a href="'.$basedir.'admin.php?edituser='.$user["id"].'">'.$user["username"].'</a>'); ?></td>
								<td><?php echo($user['role']); ?></td>
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
		
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="bootstrap/js/bootstrap.min.js"></script>
	</body>
</html>