<?php
// File: move_down.php
// SPDX-License-Identifier: GPL-3.0-or-later

switch( $_SERVER['REQUEST_METHOD'] ){
  case "POST":
    if (!isset($_POST['course'])){
      http_response_code(422);
      echo json_encode( missing_course() );
      die();
    }

    $course = $_POST['course'];

    // If TA, set username to the posted student
    if (in_array($course, $ta_courses)){
      if (!isset($_POST['student'])){
        http_response_code(422);
        echo json_encode( missing_student() );
        die();
      }
      $username = $_POST['student'];
    }
    $res = decrease_stud_priority($username, $course);
    break;
  default:
    http_response_code(405);
    echo json_encode( invalid_method("POST") );
    die();
}

if($res){
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "success" => "Student moved down one spot"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
