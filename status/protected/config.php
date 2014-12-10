<?php
// Basic Customization
$refresh = "6000";
$basedir = "/status/";
$support_url = "http://example.com/support";

// Settings for Cron
$app_url = "http://example.com/status";
$smtp = "example.com";
$smtp_user = "user";
$smtp_pass = "pass";
$smtp_secure = "tls";
$smtp_port = 587;
$sender = "info@example.com";
$sender_name = "example";
$replyto = "info@example.com";
$replyto_name = "example";
$contact = "user@example.com";
$contact_name = "example user";

// Connection
$host = 'localhost';
$user = 'demo';
$pass = '';
$database = 'systemstatusdev';
$mysqli = new mysqli($host, $user, $pass, $database);
if ($mysqli->connect_errno) {
    die('Failed to connect to MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}
