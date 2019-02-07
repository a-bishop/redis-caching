<?php

require_once('database.php');

$id = $_REQUEST["id"];
$gid = $_REQUEST["gid"];
$bet = $_REQUEST["bet"];
// $wp = $_REQUEST["wp"];

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
	if ($row["k"] == "wp") {
		$wp = $row["v"];
	}
}

error_log($wp);

$sql = 'INSERT INTO user_game_data (user_id, game_id, k, v) VALUES ';
$sql = $sql . "($id, $gid, 'last_play', now()) ";
$sql = $sql . "ON DUPLICATE KEY UPDATE v = now()";
$retval = $conn->query( $sql );
if(! $retval )
{	
	$m = "Could not update last_play: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

// Get the win percent for this game.
$sql = "SELECT k, v FROM user_game_data WHERE user_id = $id AND game_id = $gid AND k IN ('win_count', 'lose_count', 'win_total', 'lose_total')";
$retval = $conn->query( $sql);
if(! $retval )
{	
	$m = "Could not retrieve stats from user_game_data: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

$win_count = 0;
$lose_count = 0;
$win_total = 0;
$lose_total = 0;
while ($row = $retval->fetch_assoc())
{
	if ($row["k"] == "win_count") $win_count = intval($row["v"]);
	if ($row["k"] == "lose_count") $lose_count = intval($row["v"]);
	if ($row["k"] == "win_total") $win_total = intval($row["v"]);
	if ($row["k"] == "lose_total") $lose_total = intval($row["v"]);
}

$delta_coins = (rand()/getrandmax() < $wp/2) ? $bet : -$bet;
$delta_xp = intval($bet);
$delta_level = (rand()/getrandmax() < 0.005) ? 1 : 0; // level-up 1 in 200 plays on average

if ($delta_coins > 0)
{
	$count_key = "win_count";
	$total_key = "win_total";
	$count_value = $win_count + 1;
	$total_value = $win_total + $delta_coins;
}
else
{
	$count_key = "lose_count";
	$total_key = "lose_total";
	$count_value = $lose_count + 1;
	$total_value = $lose_total - $delta_coins;
}
$sql = "INSERT INTO user_game_data (user_id, game_id, k, v) ";
$sql .= "VALUES ($id, $gid, '$count_key', '$count_value') ";
$sql .= "ON DUPLICATE KEY UPDATE v = '$count_value'";
$retval = $conn->query( $sql );
if(! $retval )
{	
	$m = "Could not update win/lose count in user_game_data: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}
$win_total = $win_total + $delta_coins;
$sql = "INSERT INTO user_game_data (user_id, game_id, k, v) ";
$sql .= "VALUES ($id, $gid, '$total_key', '$total_value') ";
$sql .= "ON DUPLICATE KEY UPDATE v = '$total_value'";
$retval = $conn->query( $sql );
if(! $retval )
{
        $m = "Could not update win/lose total in user_game_data: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}	

$sql = "UPDATE users SET xp=xp+$delta_xp, coins=coins+$delta_coins, level=level+$delta_level WHERE id=$id";
$retval = $conn->query( $sql );
if(! $retval )
{
        $m = "Could not update users table with play result: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}


$result = [];

$result["status"] = "success";
$result["delta_coins"] = $delta_coins;
$result["delta_xp"] = $delta_xp;
$result["delta_level"] = $delta_level;


echo json_encode($result);
$conn->close();
?>

