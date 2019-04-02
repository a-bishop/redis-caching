<?php 

require_once('redis.php');

if ($redis->exists('test_key')) {
    $val = $redis->get('test_key');
    $redis->set('test_key', $val + 1);
    echo $val;
} else {
    echo 'Key did not exist. Setting test_key to 100.';
    $redis->set('test_key', 100);
}

?>