<?php
require_once("protected/config.php");
require_once("global.php");
require_once("vendors/PHPMailer/PHPMailerAutoload.php");

function servers(){
	global $mysqli;
	
	$stmt = $mysqli->prepare("SELECT id,name,location,host,type,url FROM servers ORDER BY id");
	echo($mysqli->error);
	$stmt->execute();
	$stmt->bind_result($out_id,$out_name,$out_location,$out_host,$out_type,$out_url);
	$servers = array();
	
	while($stmt->fetch()){
		$servers[] = array('id' => $out_id, 'name' => $out_name, 'location' => $out_location, 'host' => $out_host, 'type' => $out_type, 'url' => $out_url);
	}
	$stmt->close();
	
	return $servers;
}

function get_data($url){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

$servers = servers();
foreach($servers as $server) {
	$fetchfailed = False;
	$fetchsuccess = False;

	echo "<P>##### Processing " . $server["name"];

	$output = get_data($server['url']);
	if(($output == NULL) || ($output === false)){
		echo "<BR>Server is down. !!!!";
		// Checks to see if the server has already been marked as down
		$stmt = $mysqli->prepare("SELECT id FROM incidents WHERE server = ? AND status = 'open' LIMIT 1");
		echo "<BR> Mysql error? " . $mysqli->error;
		$stmt->bind_param('i', $server['id']);
		$stmt->execute();
		$row_id = NULL;
		$stmt->bind_result($row_id);
		if(!($stmt->fetch())){
			$fetchfailed = True;
		}
		$stmt->close();
		
		echo "<BR>Existing report? " . !$fetchfailed;
		echo "<BR>Existing report number? " . $row_id;
		
		// Runs if there is no existing incident report
		if($fetchfailed == True){
			$stmt = $mysqli->prepare("INSERT INTO incidents (server, start, status) VALUES (?, now(), 'open')");
			echo "<BR> Mysql error?" . $mysqli->error;
			$stmt->bind_param('i', $server['id']);
			$stmt->execute();
			$stmt->close();
			
			$inc_id = $mysqli->insert_id;
			
			$serverdown = $server['name'];
			echo "<br>" . $serverdown . " is down as " . $inc_id;
			
			$mail = new PHPMailer;
			
			$mail->isSMTP();
			$mail->Host = $smtp;
			$mail->SMTPAuth = true;
			$mail->Username = $smtp_user;
			$mail->Password = $smtp_pass;
			$mail->SMTPSecure = $smtp_secure;
			$mail->Port = $smtp_port;
			
			$mail->From = $sender;
			$mail->FromName = $sender_name;
			$mail->addAddress($contact, $contact_name);
			$mail->addReplyTo($replyto, $replyto_name);
			
			$mail->isHTML(false);
			
			$mail->Subject = 'Alert: Incident #'.$inc_id.' for '.$serverdown;
			$mail->Body    = "Hi $contact_name,

This is a notification sent by System Status.
Incident alert for $serverdown. Your installation has detected that $serverdown has gone offline.
$app_url/admin/incident.php?id=$inc_id

Log in to your account at $app_url to verify that this is correct information, and take the appropriate steps.
If this incident is by error, please file a bug report on GitHub. https://github.com/dylanhansch/SystemStatus/issues

Best regards,
$sender_name";
			
			if($mail->send()){
				echo 'Message has been sent.';
			}else{
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $mail->ErrorInfo;
			}
		}
	}else{
		echo "<br>Server is up.";
		// otherwise, the server is online.
		// check to see if there are any open incidents that should now be closed.
		$stmt = $mysqli->prepare("SELECT id FROM incidents WHERE server = ? AND status = 'open' LIMIT 1");
		echo "<BR> Mysql error? " . $mysqli->error;
		$stmt->bind_param('i', $server['id']);
		$stmt->execute();
		$row_id = NULL;
		$stmt->bind_result($row_id);
		if($stmt->fetch()){
			$fetchsuccess = True;
		}
		$stmt->close();
		
		// this means that there is an open incident report
		if($fetchsuccess == True){
			echo "closing incident $row_id for server";
			$stmt = $mysqli->prepare("UPDATE incidents SET end = now(), status = 'closed' WHERE id = ?");
			echo "<BR> Mysql error? " . $mysqli->error;
			$stmt->bind_param('i', $row_id);
			$stmt->execute();
			$stmt->close();
			
			$serverdown = $server['name'];
		
			$mail = new PHPMailer;
			
			$mail->isSMTP();
			$mail->Host = $smtp;
			$mail->SMTPAuth = true;
			$mail->Username = $smtp_user;
			$mail->Password = $smtp_pass;
			$mail->SMTPSecure = $smtp_secure;
			$mail->Port = $smtp_port;
			
			$mail->From = $sender;
			$mail->FromName = $sender_name;
			$mail->addAddress($contact, $contact_name);
			$mail->addReplyTo($replyto, $replyto_name);
			
			$mail->isHTML(false);
			
			$mail->Subject = "Closed: Incident #$row_id for $serverdown";
			$mail->Body    = "Hi $contact_name,

This is a notification sent by System Status.
Incident closed for $serverdown. Your installation has detected that $serverdown has came back online.
$app_url/admin/incident.php?id=$row_id

Best regards,
$sender_name";
			
			if($mail->send()){
				echo 'Message has been sent.';
			}else{
				echo 'Message could not be sent.';
				echo 'Mailer Error: ' . $mail->ErrorInfo;
			}
		}
	}

}
