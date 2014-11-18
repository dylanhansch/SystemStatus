<?php
session_name("systemstatus");
session_start();
include_once("protected/config.php");
session_destroy();

if(isset($_COOKIE['id_cookie'])){
	setcookie("id_cookie","",time()-50000,"/");
	setcookie("pass_cookie","",time()-50000,"/");
}

header("Location: " . $basedir);
?>