<?php
// File: move_up.php
// SPDX-License-Identifier: GPL-3.0-or-later

switch( $_SERVER['REQUEST_METHOD'] ){
  case "POST":
    if (!isset($_POST['course'])){
        http_response_code(422);
          echo json_encode( missing_course() );
          die();
    }

    if (!isset($_POST['student'])){
        http_response_code(422);
          echo json_encode( missing_student() );
          die();
    }

    $course     = $_POST['course'];
    $student    = $_POST['student'];
    if (!in_array($course, $ta_courses)){
      http_response_code(403);
      echo json_encode( not_authorized() );
      die();
    }
    $res = increase_stud_priority($student, $course);
    break;
  default:    
    http_response_code(405);
    echo json_encode( invalid_method("POST") );
    die();
}

if($res < 0){
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "success" => "Student moved up one spot"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
