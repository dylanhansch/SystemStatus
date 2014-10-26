<?php
// Basic Customization
$name = "My Company";
$refresh = "5000";
$basedir = "/status/";

// Connection
$host = 'localhost';
$user = 'demo';
$pass = '';
$database = 'hsdlinks';
$mysqli = new mysqli($host, $user, $pass, $database);
if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
