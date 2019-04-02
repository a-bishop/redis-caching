<?php

$dbhost = '172.17.0.2:3306';
$dbuser = 'piggie_user';
$dbpass = 'piggie';
$conn = new mysqli($dbhost, $dbuser, $dbpass);
if ( $conn->connect_error )    
{               
        $m = "Could not connect to database: " . $conn->connect_error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
	
}               
$conn->select_db('piggie');

?>

