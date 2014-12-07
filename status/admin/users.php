<?php
require_once("../protected/config.php");
require_once("../global.php");

if($logged == 0){
	header("Location: login.php");
}elseif($role != "admin"){
	die("You must be an admin or staff member to access this page.");
}

if(isset($_GET['edit'])){
	$edituser = $_GET['edit'];
	$stmt = $mysqli->prepare("SELECT username FROM users WHERE id = ?");
	$stmt->bind_param('i', $edituser);
	$stmt->execute();
	if($stmt->fetch()){
		$edituserfetch = True;
	}
	$stmt->close();
}

if(isset($_GET['create'])){
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
					header('Refresh: 2; URL='.$basedir.'admin/');
				}
				$stmt->close();
			}
		}
	}
}elseif(isset($_GET['edit']) && isset($_GET['pass']) && $edituserfetch == True){
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
			header('Refresh: 2; URL='.$basedir.'admin/users.php?edit='.$edituser);
		}
		$stmt->close();
	}
}elseif(isset($_GET['edit']) && $edituserfetch == True){
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
				header('Refresh: 2; URL='.$basedir.'admin/users.php?edit='.$edituser);
			}
			$stmt->close();
		}
	}
}else{
	header("Location: ".$basedir."admin/");
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

if(isset($_GET['del'])){
	del_user($_GET['del']);
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
					
					<h1>Create User</h1>
					<ol class="breadcrumb">
					  <li><a href="<?php echo($basedir); ?>admin/">Admin</a></li>
					  <li class="active">Create</li>
					</ol>
					<div class="well">
						<?php echo($createuser_message); ?>
						<form class="form-signin" action="users.php?create" method="post">
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
										<option value="admin">Admin</option>
									</select>
								</div>
							</div>
							<br>
							<input type="submit" class="btn btn-warning center" value="Create User"/>
						</form>
					</div>
					
					<?php }elseif(isset($_GET['edit']) && isset($_GET['pass']) && $edituserfetch == True){ ?>
					
					<h1>Update Password</h1>
					<ol class="breadcrumb">
					  <li><a href="<?php echo($basedir); ?>admin/">Admin</a></li>
					  <li><a href="<?php echo($basedir); ?>admin/users.php?edit=<?php echo($edituser); ?>">Edit</a></li>
					  <li class="active">Password</li>
					</ol>
					<div class="well">
						<?php echo($editpassword_message); ?>
						<form class="form-signin" action="users.php?edit=<?php echo($edituser); ?>&pass" method="post">
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
					
					<?php }elseif(isset($_GET['edit']) && $edituserfetch == True){ ?>
					
					<h1>Edit User <a href="<?php echo($basedir); ?>admin/users.php?edit=<?php echo($edituser); ?>&pass" class="btn btn-info btn-sm">Change Password</a></h1>
					<ol class="breadcrumb">
					  <li><a href="<?php echo($basedir); ?>admin/">Admin</a></li>
					  <li class="active">Edit</li>
					</ol>
					<div class="well">
						<?php echo($edituser_message); ?>
						<form class="form-signin" action="users.php?edit=<?php echo($edituser); ?>" method="post">
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
					
					<?php } ?>
				</div>
			</div>
		</div>
		
		<?php include_once('../footer.php'); ?>
	</body>
</html>