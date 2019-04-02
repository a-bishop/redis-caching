<?php

require_once('database.php');

$id = $_REQUEST["id"];
$iid = $_REQUEST["iid"];

$itemData = [];

$sql = 'SELECT * FROM collection_items where id = ' . $iid;
$retval = $conn->query( $sql );
if(! $retval )
{	
	$m = "Could not retrieve item data: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

if ($row = $retval->fetch_assoc())
{
	$itemData["id"] = $row["id"];
	$itemData["url"] = $row["image_url"];
	$itemData["cost"] = $row["cost"];
}
else
{
	$m = "Item $iid does not exist: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

$sql = "SELECT * FROM user_collection_items where user_id = $id";
$retval = $conn->query($sql);
if(! $retval )
{	
	$m = "Could not retrieve user item data: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

$itemCount = 0;
if ($row = $retval->fetch_assoc())
{
	$itemCount = intval($row['count']);
}
$itemCount++;
if ($itemCount == 1) 
{
	$sql = "INSERT INTO user_collection_items (user_id, item_id, count) VALUES ($id, $iid, 1)";
}
else
{
	$sql = "UPDATE user_collection_items SET count=$itemCount WHERE user_id = $id AND item_id = $iid";
}
$retval = $conn->query($sql);
if (! $retval )
{
	$m = "Could not set user collection item count: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}

$sql = "UPDATE users SET coins=coins-" . strval($itemData['cost']) . " WHERE id=$id";
$retval = $conn->query($sql);
if (! $retval )
{
	$m = "Could not set user coin balance: " . $conn->error;
        error_log($m);
	die('{"status":"error", "message":"' . $m . '"}');
}



echo '{"item_data":' . json_encode($itemData) . ', "item_count":' . $itemCount . '}';

$conn->close();
?>

