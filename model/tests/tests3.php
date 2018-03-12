<?php

require_once '../auth.php';
require_once '../courses.php';
require_once '../queue.php';

if (del_course("CS 4400: Computer Systems")){
  echo "Test 10.11 failed";
  die();
}

if (new_course("CS 4400: Computer Systems", "CS", "4400", "The hardest CS course", "cs4400-queue-test", "erin")){
  echo "Test 10.10 failed";
  die();
}

echo "All Tests Completed Successfully";
?>
