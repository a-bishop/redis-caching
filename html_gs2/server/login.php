<?php

//----------------------------
//   preample: db connect,
//   get url params, 
//   create $result
//----------------------------
require_once('database.php');
require_once('redis.php');

error_log("gs2 login ------------------------------------");
$id = $_REQUEST["id"];
$snip = $_REQUEST["snip"];
$friends = json_decode($_REQUEST["friends"]);
$result = [];

// Get friends lists from social network.
// $sn_result = file_get_contents("http://" . $snip . "/sn/friends.php?id=" . $id);
// $friends = json_decode($sn_result, true);


// Build a SQL list of ids (including the player's) to
// retrieve data from.
$id_in_clause = "(";
foreach ($friends as $friend_id) {
	$id_in_clause .= $friend_id . ",";
}
$id_in_clause .= $id . ")";

// DB CALL: get basic user data from friends and player.
$sql = 'SELECT * FROM users where id in ' . $id_in_clause;
$retval = $conn->query( $sql );
if(! $retval )
{	
	$m = "Could not retrieve user and friend data: " . $conn->error;
	error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

// iterate through the DB results; append all user data to $app_friends, except of course
// for the player's data, which goes into $user_data.
$app_friend_data = [];
$user_data = [];
while ($row = $retval->fetch_assoc())
{
	$uid = $row["id"];
	if ($uid == $id) {
		$user_data = $row;

		// We'll leave this as a placeholder for now; we'll fill this in
		// later.
		$user_data["collections"] = [];
	} else {
		$app_friend_data[$row["id"]] = $row;
	}
} 


//----------------------
// UPDATE USER
//----------------------
// DB CALL: update the last_login timestamp for this user.
$sql = "UPDATE users SET last_login=now() WHERE id=$id";
$retval = $conn->query( $sql );
if(! $retval )
{	
	$m = "Could not update user last_login: " . $conn->error;
 	error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}


// ---------------------------
//    GLOBAL GAME CONFIG
// ---------------------------
// DB CALL: get all key/value pairs from global_config, save them as proper
// associative array key/value pairs. 

$key = "game.config";
$config = [];
if ($redis->exists($key)) {
	$config = $redis->get($key);
} else {
	$sql = "SELECT * FROM global_config";
	$retval = $conn->query( $sql );
	if(! $retval )
	{	
		$m = "Could not get global config: " . $conn->error;
		error_log($m);
		die('{"status":"error", "message":"' . $m . '"}');
	}

	while ($row = $retval->fetch_assoc())
	{
		$config[$row["k"]] = $row["v"];
	}
	$redis->set($key, $config);
}

// ---------------------------
//     COLLECTION DATA
// ---------------------------
// DB CALL: get basic collection data.

$collection_items = [];
$key = "collection_items";
if ($redis->exists($key)) {
	$collection_items = $redis->get($key);
} else {
	$sql = "SELECT * FROM collection_items";
	$retval = $conn->query( $sql );
	if(! $retval) {
		$m = "Could not get collection data: " . $conn->error;
		error_log($m);
		die('{"status":"error", "message":"' . $m . '"}');
	}

	while ($row = $retval->fetch_assoc()) {
		if (!array_key_exists($row['id'], $collection_items)) {
			$collection_items[$row['id']] = array("image_url" => $row['image_url'], "cost" => $row['cost']);
		}
	}
	$redis->set($key, $collection_items);
}

$sql = "SELECT * FROM user_collection_items WHERE user_id=$uid";
$retval = $conn->query( $sql );
if(! $retval) {
	$m = "Could not get user collection items: " . $conn->error;
 	error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}
while ($row = $retval->fetch_assoc()) {
	if ($row['user_id'] != null) {
		$user_data["collections"][$row['item_id']] = array("count" => $row['count']);
	}
}

// ---------------------------
//    prepare final output
// ---------------------------

$result["config"] = $config;
$result["user"] = $user_data;
$result["collections"] = $collection_items;
$result["status"] = "success";


echo json_encode($result);
$conn->close();
?>

