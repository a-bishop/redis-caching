<?php 

$redis = new Redis();
$redis->connect('172.17.0.7', 6379);
$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_IGBINARY);

?>