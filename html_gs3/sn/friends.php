<?php

$result = [];
$result["app_friends"] = [];
$result["non_app_friends"] = [];
$n = rand(5, 40);
for ($i = 0; $i < $n; $i += 1) {
	$id = rand(100000,199999);
	$is_app_friend = rand() % 3 == 0;
	if ($is_app_friend) $result["app_friends"][] = $id;
	else $result["non_app_friends"][] = $id;
}
$delay_ms = rand(250,5000);
usleep($delay_ms * 1000);
echo json_encode($result);
?>

