<?php
include_once('../protected/config.php');
include_once('../global.php');

if($logged == 0){
	header("Location: ../login.php");
}elseif($role != "admin"){
	die("You must be an admin or staff member to access this page.");
}

if(isset($_GET['edit'])){
	$edit = $_GET['edit'];
	$stmt = $mysqli->prepare("SELECT header FROM announcements WHERE id = ?");
	echo($mysqli->error);
	$stmt->bind_param('i', $edit);
	$stmt->execute();
	if($stmt->fetch()){
		$fetch = True;
	}
	$stmt->close();
}

if(isset($_GET['create'])){
	$title = "Create Announcement";
	$create_message = "";
	
	if(isset($_POST['header'])){

		$header = $_POST['header'];
		$body = $_POST['body'];
		$level = $_POST['level'];
		
		if( (!$header) || (!$body) || (!$level) ){
			$create_message = "Please complete all the fields below.";
		}else{
			// Check for duplicates
			$stmt = $mysqli->prepare("SELECT header FROM `announcements` WHERE `header` = ? LIMIT 1");
			echo($mysqli->error);
			$stmt->bind_param('s', $header);
			$stmt->execute();
			if($stmt->fetch()){
				$create_message = "That title is already in use.";
			}else{
				$stmt->close();
				
				// Insert the information
				$stmt = $mysqli->prepare("INSERT INTO announcements (header, body, level, status) VALUES (?, ?, ?, 'active')");
				echo($mysqli->error);
				$stmt->bind_param('sss', $header, $body, $level);
				$stmt->execute();
				
				$create_message = "Announcement Posted.";
				header('Refresh: 2; URL='.$basedir.'admin/');
			}
			$stmt->close();
		}
	}
}elseif(isset($_GET['edit']) && $fetch == True){
	$title = "Edit Announcement";
	$edit_message = "";
	
	$stmt = $mysqli->prepare("SELECT header,body,status,level FROM announcements WHERE id = ?");
	echo($mysqli->error);
	$stmt->bind_param('i', $edit);
	$stmt->execute();
	$stmt->bind_result($e_header,$e_body,$e_status,$e_level);
	$stmt->fetch();
	$stmt->close();
	
	if(isset($_POST['header'])){

		$header = $_POST['header'];
		$body = $_POST['body'];
		$level = $_POST['level'];
		$status = $_POST['status'];
		
		if( (!$header) || (!$body) || (!$level) || (!$status) ){
			$edit_message = "Please complete all the fields below.";
		}else{
			// Check for duplicates
			$stmt = $mysqli->prepare("SELECT header FROM announcements WHERE id <> ? AND header = ?");
			echo($mysqli->error);
			$stmt->bind_param('is', $edit,$header);
			$stmt->execute();
			if($stmt->fetch()){
				$edit_message = "That title have already been used.";
			}else{
				$stmt->close();
				
				// Update the announcement
				$stmt = $mysqli->prepare("UPDATE announcements SET header = ?, body = ?, level = ?, status = ? WHERE id = ?");
				echo($mysqli->error);
				$stmt->bind_param('ssssi', $header, $body, $level, $status, $edit);
				$stmt->execute();
				$stmt->close();
				
				$edit_message = "Announcement updated.";
				header('Refresh: 2; URL='.$basedir.'admin/announcements.php?edit='.$edit);
			}
		}
	}
}else{
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
		
		<link rel="stylesheet" href="<?php echo($basedir); ?>bootstrap/css/bootstrap.min.css">
		<link rel="stylesheet" href="<?php echo($basedir); ?>style.css">
	</head>
	<body>
		<?php include_once('../navbar.php'); ?>
		
		<div class="container">
			<div class="row">
				<div class="col-lg-12">
					<?php if(isset($_GET['create'])){ ?>
					
					<h1>Create Announcement</h1>
					<ol class="breadcrumb">
					  <li><a href="<?php echo($basedir); ?>admin/">Admin</a></li>
					  <li class="active">Create</li>
					</ol>
					<?php echo($create_message); ?>
					<form class="form-signin" action="announcements.php?create" method="post">
						<div class="row">
							<div class="col-sm-9">
								<label for="name">Title</label>
								<input type="text" class="form-control" name="header" placeholder="Ex: Hardware Upgrade on Node #26" required autofocus>
							</div>
							<div class="col-sm-3">
								<label for="name">Level</label>
								<select name="level" class="form-control" required>
									<option value="danger" selected="selected">Alert</option>
									<option value="warning">Warning</option>
									<option value="info">Info</option>
									<option value="success">Success</option>
								</select>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-lg-12">
								<label for="name">Body</label>
								<textarea name="body" rows="12" class="form-control" placeholder="Completed upgrade in approximately 10 minutes. All services are back online." required></textarea>
							</div>
						</div>
						<br>
						<input type="submit" class="btn btn-warning center" value="Post"/>
					</form>
					
					<?php }elseif(isset($_GET['edit']) && $fetch == True){ ?>
					
					<h1>Edit Announcement</h1>
					<ol class="breadcrumb">
					  <li><a href="<?php echo($basedir); ?>admin/">Admin</a></li>
					  <li class="active">Edit</li>
					</ol>
					<?php echo($edit_message); ?>
					<form class="form-signin" action="announcements.php?edit=<?php echo($edit); ?>" method="post">
						<div class="row">
							<div class="col-sm-6">
								<label for="name">Title</label>
								<input type="text" class="form-control" name="header" value="<?php echo($e_header); ?>" required autofocus>
							</div>
							<div class="col-sm-3">
								<label for="name">Level</label>
								<select name="level" class="form-control" required>
									<option value="danger" <?php if($e_level == "danger"){ echo('selected="selected"'); } ?>>Alert</option>
									<option value="warning" <?php if($e_level == "warning"){ echo('selected="selected"'); } ?>>Warning</option>
									<option value="info" <?php if($e_level == "info"){ echo('selected="selected"'); } ?>>Info</option>
									<option value="success" <?php if($e_level == "success"){ echo('selected="selected"'); } ?>>Success</option>
								</select>
							</div>
							<div class="col-sm-3">
								<label for="name">Status</label>
								<select name="status" class="form-control" required>
									<option value="active" <?php if($e_status == "active"){ echo('selected="selected"'); } ?>>Active</option>
									<option value="done" <?php if($e_status == "done"){ echo('selected="selected"'); } ?>>Done</option>
								</select>
							</div>
						</div>
						<br>
						<div class="row">
							<div class="col-lg-12">
								<label for="name">Body</label>
								<textarea name="body" rows="12" class="form-control" required><?php echo($e_body); ?></textarea>
							</div>
						</div>
						<br>
						<input type="submit" class="btn btn-warning center" value="Update"/>
					</form>
					<?php } ?>
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