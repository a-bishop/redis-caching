<?php

require_once('database.php');

$id = $_REQUEST["id"];
$gid =$_REQUEST["gid"];

$gameData = [];
$userGameData = [];

$sql = "SELECT * FROM game_data where game_id = '$gid'";
$retval = $conn->query( $sql );
if(! $retval )
{	
	$m = "Could not retrieve game data: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}


while ($row = $retval->fetch_assoc())
{
	if ($row["k"] != "wp") {
		$gameData[$row["k"]] = $row["v"];
	}
}

$sql = "SELECT * FROM user_game_data WHERE user_id = $id AND game_id = $gid";
$retval = $conn->query($sql);
if (!$retval )
{	
	$m = "Could not retrieve user game data: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

$sessionCount = 0;
while ($row = $retval->fetch_assoc())
{
	$userGameData[$row["k"]] = $row["v"];
	if ($row["k"] == "sessions") {
		$sessionCount = intval($row["v"]) + 1;
	}
}

if ($sessionCount == 0) 
{
	$sql = "INSERT INTO user_game_data (user_id, game_id, k, v) VALUES ($id, $gid, 'sessions', '1')";
}
else
{
	$sql = "UPDATE user_game_data SET v='" . strval($sessionCount) . "' WHERE user_id=$id AND game_id=$gid AND k='sessions'";
}
$retval = $conn->query($sql);
if (! $retval )
{
	$m = "Could not set user game session count: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

echo '{"game_data":' . json_encode($gameData) . ', "user_game_data":' . json_encode($userGameData) . '}';

$conn->close();
?>

