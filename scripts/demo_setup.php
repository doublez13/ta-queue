<?php

require_once '../model/auth.php';
require_once '../model/courses.php';
require_once '../model/queue.php';


if (new_course("CS 1030: Foundations of CS", "CS", "1030", "description", "rohith", 'KMFDM')){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 1410: Introduction to Object-Oriented Programming", "CS", "1410", "description", "rohith", 'KMFDM')){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 2100: Descrete Scructures", "CS", "2100", "description", "rohith", 'KMFDM')){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 2420: Introduction to Algorithms and Data Structures", "CS", "2420", "description", "dkopta", NULL)){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 3100: Models of Computation", "CS", "3100", "description", "rohith", 'KMFDM')){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 3500: Software Practice 1", "CS", "3500", "description", "dkopta", NULL)){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 3505: Software Practice 2", "CS", "3505", "description", "rohith", 'KMFDM')){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 3810: Computer Organization", "CS", "3810", "description", "rohith", 'KMFDM')){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 4150: Algorithms", "CS", "4150", "description", "rohith", 'KMFDM')){
  echo "Failed to create new course";
  die();
}

if (new_course("CS 4400: Computer Systems", "CS", "4400", "description", "rohith", 'KMFDM')){
  echo "Failed to create new course";
  die();
}



echo "Setup Completed Successfully";
?>
