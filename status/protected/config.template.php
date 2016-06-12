<?php
// Basic Customization
$refresh = "6000";
$basedir = "/status/";
$baseurl = "http://example.com/status/";
$company = "My Company";
$support_url = "http://example.com/support";

// Connection
$host = 'localhost';
$user = 'systemstatus';
$pass = '123456';
$database = 'systemstatus';
$mysqli = new mysqli($host, $user, $pass, $database);
if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
