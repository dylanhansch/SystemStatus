<?php
// Basic Customization
$refresh = "6000";
$basedir = "/status/";
$support_url = "http://example.com/support";

// Connection
$host = 'localhost';
$user = 'demo';
$pass = '';
$database = 'systemstatusdev';
$mysqli = new mysqli($host, $user, $pass, $database);
if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
