<?php
require_once("protected/config.php");
require_once("global.php");

if(isset($_SESSION['id'])){
	$stmt = $mysqli->prepare("SELECT firstname,role FROM `users` WHERE `id` = ?");
	$stmt->bind_param('i', $session_id);
	$stmt->execute();
	$stmt->bind_result($fname,$role);
	$stmt->fetch();
	$stmt->close();
}
?>
<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<?php echo($basedir); ?>">System Status</a>
				</div>
				<div class="collapse navbar-collapse navbar-ex1-collapse">
					<ul class="nav navbar-nav pull-left">
						<?php if($role == "admin"){ ?>
						<li><a href="<?php echo($basedir); ?>admin/">Admin</a></li>
						<?php } ?>
					</ul>
					<ul class="nav navbar-nav pull-right">
						<?php if($logged == 0){ ?>
						<li><a href="<?php echo($basedir); ?>login.php">Login</a></li>
						<?php }else{ ?>
						<li class="dropdown">
							<a href="" class="dropdown-toggle" data-toggle="dropdown">Hello, <?php print($fname); ?> <b class="caret"></b></a>
							<ul class="dropdown-menu">
								<li><a href="<?php echo($basedir); ?>account.php">Account</a></li>
								<li class="divider"></li>
								<li><a href="<?php echo($basedir); ?>logout.php">Logout</a></li>
							</ul>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>
		</nav>