<?php
// Basic Customization
$name = "My Company";
$refresh = "5000";

// Connection
$host = 'host';
$user = 'user';
$pass = 'pass';
$data = 'database';
mysql_connect($host, $user, $pass) or die(mysql_error());
mysql_select_db($database) or die(mysql_error());
