#!/usr/bin/php
<?php
require_once '../model/config.php';
require_once '../model/queue.php';

#Warm up the cache
get_queue(4, "student");

$trials = 50000;
$time_start = microtime(true);
for ($x = 0; $x <= $trials; $x++) {
  get_queue(4, "student");
} 
$time_end   = microtime(true);
echo ($time_end - $time_start)/$trials;
?>
