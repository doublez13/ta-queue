<?php
// File: queue.php
// SPDX-License-Identifier: GPL-3.0-or-later

switch( $_SERVER['REQUEST_METHOD'] ){
  case "GET":
    //For now, these return the same information.
    //Later, we may want the TAs to see more,
    //or the students to see less.
    if (in_array($course, $ta_courses)){ //TA
      $return = get_queue($course);
    }
    elseif (in_array($course, get_stud_courses($username))){ //Student
      $return = get_queue($course);
    }else{ //Not in course
      http_response_code(403);
      $return = array(
        "authenticated" => True,
        "error" => "Not enrolled in course"
        );
    }
  break;

  default:
    http_response_code(405);
    echo json_encode( invalid_method("GET") );
    die();
}

if($return < 0)
{
  $return = return_JSON_error($return);
  http_response_code(500);
}else
{
  $return["authenticated"] = True;
  http_response_code(200);  
}
echo json_encode($return);
?>
