<?php
include_once('protected/config.php');
include_once('global.php');

$message = "";
if(isset($_POST['user'])){
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$remember = $_POST['remember'];
	
	//error handling
	if( (!$user) || (!$pass) ){
		$message = "Please insert both fields";
	}else{
		//securing the data
		$stmt = $mysqli->prepare("SELECT id,username,password FROM `users` WHERE `email` = ? OR `username` = ? LIMIT 1");
		$stmt->bind_param('ss', $user, $user);
		$stmt->execute();
		$stmt->bind_result($id,$username,$pwhash);
		
		if($stmt->fetch()){
			$stmt->close();
			if($pwhash !== crypt($pass, $pwhash)){
				$message = "Invalid credentials.";
			}else{
				//start the sessions
				$_SESSION['pass'] = $pwhash;
				
				$_SESSION['username'] = $username;
				$_SESSION['id'] = $id;
				
				if($remember == "yes"){
					//create the cookies
					setcookie("id_cookie",$id,time()+60*60*24*100,"/");
					pass_cookie("id_cookie",$id,time()+60*60*24*100,"/");
				}
				
				header("Location: " . $basedir);
			}
		}else{
			$message = "Invalid credentials.";
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Login | System Status</title>
		<meta charset="utf-8">
		<meta name="author" content="Dylan Hansch">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<link rel="shortcut icon" content="none">
		
		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="style.css">
		
		<style>
			body {
			  padding-bottom: 40px;
			  background-color: #eee;
			}

			.form-signin {
			  max-width: 330px;
			  padding: 15px;
			  margin: 0 auto;
			}
			.form-signin .form-signin-heading,
			.form-signin .checkbox {
			  margin-bottom: 10px;
			}
			.form-signin .checkbox {
			  font-weight: normal;
			}
			.form-signin .form-control {
			  position: relative;
			  height: auto;
			  -webkit-box-sizing: border-box;
				 -moz-box-sizing: border-box;
					  box-sizing: border-box;
			  padding: 10px;
			  font-size: 16px;
			}
			.form-signin .form-control:focus {
			  z-index: 2;
			}
			.form-signin input[type="email"] {
			  margin-bottom: -1px;
			  border-bottom-right-radius: 0;
			  border-bottom-left-radius: 0;
			}
			.form-signin input[type="password"] {
			  margin-bottom: 10px;
			  border-top-left-radius: 0;
			  border-top-right-radius: 0;
			}
		</style>
	</head>
	<body>
		<?php include_once('navbar.php'); ?>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					
					<form action="login.php" method="post" class="form-signin" role="form">
						<h2 class="form-signin-heading">Please sign in</h2>
						<p><?php echo($message); ?></p>
						<input type="text" class="form-control" name="user" placeholder="Username / Email" required autofocus>
						<input type="password" class="form-control" name="pass" placeholder="Password" required>
						<div class="checkbox">
							<label>
								<input type="checkbox" name="remember" value="yes"/>Remember me?
							</label>
						</div>
						<button class="btn btn-lg btn-primary btn-block" type="submit" name="Login">Sign in</button>
					</form>
					
				</div>
			</div>
		</div>
		
		<?php include_once('footer.php'); ?>
	</body>
</html>