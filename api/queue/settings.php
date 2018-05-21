<?php
// File: set_cooldown.php
// SPDX-License-Identifier: GPL-3.0-or-later


switch( $_SERVER['REQUEST_METHOD'] ){
  case "POST":
    if (!isset($_POST['course'])){
      http_response_code(422);
      echo json_encode( missing_course() );
      die();
    }
    if (!isset($_POST['setting'])){
      http_response_code(422);
      echo json_encode( missing_course() );
      die();
    }

    $course  = $_POST['course'];
    $setting = $_POST['setting'];
    
    if (!in_array($course, $ta_courses)){
      http_response_code(403);
      echo json_encode( not_authorized() );
      die();
    }
    switch( $setting ){
      case "time_lim":
        if (!isset($_POST['time_lim']) || !is_numeric($_POST['time_lim']) || $_POST['time_lim'] < 0 ){
          http_response_code(422);
          echo json_encode( missing_time('time_lim') );
          die();
        }
        $res = set_time_lim( $_POST['time_lim'], $course);
        break;
      case "cooldown":
        if (!isset($_POST['time_lim']) || !is_numeric($_POST['time_lim']) || $_POST['time_lim'] < 0 ){
          http_response_code(422);
          echo json_encode( missing_time('time_lim') );
          die();
        }
        $res = set_cooldown( $_POST['time_lim'], $course);     
        break;
    }
    break;

  default:
    http_response_code(405);
    echo json_encode( invalid_method("POST") );
    die();
}

if ($res){
  $return = return_JSON_error($res);
  http_response_code(500);
}else{
  $return = array(
    "authenticated" => True,
    "success" => "Setting changed"
  );
  http_response_code(200);
}
echo json_encode($return);
?>
